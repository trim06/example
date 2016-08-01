<?php

namespace crm\modules\contracts\models;

use common\models\Contract;
use common\models\Task;
use common\models\BaseFilterForm;

/**
 * Модель фильтра списка пользователей
 */
class ContractFilterForm extends BaseFilterForm {

	public $name;
	public $phone;
	public $role;
	public $status;
	public $manager;
	public $taskType;
	public $service;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = NULL) {
		$this->setQuery(Contract::find()
			->joinWith(['abonent', 'task', 'services'])
			->with(['user', 'task' => function($query) {
				$query->orderBy('task.start_at ASC')->with(['author', 'contract' => function($query) {
					$query->with('abonent');
				}]);
			}])
			# ->andWhere(['in', 'unit_id', ArrayHelper::getColumn(Unit::find()->byUser()->all(), 'id')])
			->groupBy('contract.id')
		);

		$this->setQuery($this->_query->orderBy('task.start_at ASC'));
		# если передан маршрут, заполняем модель
		if ($route) $this->fillByRoutes($route);

		if (empty($this->status)) {
			$this->setQuery($this->_query->andWhere(['not in', 'contract.status', Contract::STATUS_DELETED]));
		}

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
			'm' => ['attr' => 'manager', 'function' => 'byManager'],
			's' => ['attr' => 'status', 'function' => 'byStatus'],
			't' => ['attr' => 'taskType', 'function' => 'byTask'],
			'e' => ['attr' => 'service', 'function' => 'byService'],
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
	
	public function byStatus() {
		switch ($this->status) {
			case (Contract::STATUS_PROCESSING) : $this->getQuery()->andWhere(['task.status' => Task::STATUS_NEW, 'contract.status' => Contract::STATUS_PROCESSING]); break;
			case (Contract::STATUS_PAYMENT) : $this->getQuery()->orderBy('signed_at DESC');
			default : $this->getQuery()->andWhere(['IN', 'contract.status', $this->status]); break;
		}
	}
	
	# поиск договора по менеджеру
	public function byManager() {
		$conditions = explode('.', $this->manager);
		foreach ($conditions as $condition) {
			$prepend = substr($condition, 0, 1);
			$value = substr($condition, 2);
			switch ($prepend) {
				case 3: 
					$this->getQuery()->joinWith('unit')->andWhere(['unit.city_id' => $value]);
					break;
				case 0: 
					$this->getQuery()->andWhere(['task.unit_id' => $value]);
					break;
				case 1: $this->getQuery()->andWhere(['task.executor_id' => $value, 'task.status' => Task::STATUS_NEW])->with(['task' => function($query) use ($value) {
					$query->orderBy('task.start_at ASC')->andWhere(['task.executor_id' => $value])->with(['author', 'contract' => function($query) {
						$query->with('abonent');
					}]);
				}]);
					break;
				default: break;
			}
		}
	}
	
	# поиск договоров с имеющимися незаконченными задачами определенного типа
	public function byTask() {
		$this->getQuery()->andWhere(['task.task' => $this->taskType, 'task.status' => 'process']);
	}
	
	# поиск договор по подключенным услугам
	public function byService() {
		$this->getQuery()->andWhere(['in', 'service.category_id', $this->service]);
	}
}
