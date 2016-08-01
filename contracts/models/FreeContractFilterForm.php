<?php

namespace crm\modules\contracts\models;

use yii\db\Query;
use common\models\Contract;
use common\models\BaseFilterForm;

/**
 * Модель фильтра списка пользователей
 */
class FreeContractFilterForm extends BaseFilterForm {

	public $name;
	public $phone;
	public $role;
	public $status;
	public $taskType;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = NULL) {
		$this->setQuery(Contract::find()
			->joinWith(['abonent', 'task', 'user'])
			->with(['task' => function($query) {
				$query->orderBy('task.start_at ASC')->with(['author', 'contract' => function($query) {
					$query->with('abonent');
				}]);
			}])
			->andWhere(['task.id' => null])
			->orWhere(['not exists', (new Query())->select('*')->from('task as t2')->where(['and', 't2.status = "process"', 't2.contract_id = contract.id'])])
			->andWhere(['contract.status' => Contract::STATUS_PROCESSING])
			//->andWhere(['in', 'unit_id', ArrayHelper::getColumn(Unit::find()->byUser()->all(), 'id')])
			->andWhere(['contract.city_id' => user()->identity->currentUnit->city_id])
			->groupBy('contract.id')
		);

		# если передан маршрут, заполняем модель
		if ($route) $this->fillByRoutes($route);

		if (empty($this->status)) {
			$this->setQuery($this->_query->andWhere(['not in', 'contract.status', Contract::STATUS_DELETED]));
		}

		$this->setQuery($this->_query->orderBy('contract.updated_at ASC'));
	}

	/**
	 * Возвращает список правил, описывающих логику работы фильтра
	 * @return array
	 */
	public function routeRules() {
		return [
			'i' => ['attr' => 'id', 'table_field' => 'contract.id', 'condition' => 'in'],
			'n' => ['attr' => 'name', 'function' => 'byName'],
			'ph' => ['attr' => 'phone', 'function' => 'byPhone'],
			't' => ['attr' => 'taskType', 'function' => 'byTask'],
			] + parent::routeRules();
	}

	public function attributeLabels() {
		return [
			'name' => 'Имя клиента',
			'phone' => 'Телефон',
		];
	}
	
	# поиск договора по имени абонента
	public function byName() {
		$this->getQuery()->andWhere(['like', 'abonent.name', $this->name]);
	}
	
	# поиск договора по телефону клиента
	public function byPhone() {
		$this->getQuery()->andWhere(['like', 'abonent.phone', $this->phone]);
	}
	
	# поиск договоров с имеющимися незаконченными задачами определенного типа
	public function byTask() {
		$this->getQuery()->andWhere(['task.task' => $this->taskType, 'task.status' => 'process']);
	}
}
