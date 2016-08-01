<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Task;
use common\models\BaseFilterForm;

/**
 * Модель фильтра списка пользователей
 */
class TaskFilterForm extends BaseFilterForm {

	public $date_from;
	public $date_to;
	public $manager;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = NULL) {
		$this->setQuery(Task::find()->with(['contract.abonent'])->where(['executor_id' => user()->id, 'status' => Task::STATUS_NEW])->orderBy('start_at ASC'));

		# если передан маршрут, заполняем модель
		if ($route) $this->fillByRoutes($route);
	}

	/**
	 * Возвращает список правил, описывающих логику работы фильтра
	 * @return array
	 */
	public function routeRules() {
		return [
			'df' => ['attr' => 'date_from', 'function' => 'byDateFrom'],
			'dt' => ['attr' => 'date_to', 'function' => 'byDateTo'],
			'm' => ['attr' => 'manager', 'function' => 'byManager'],
			];// + parent::routeRules();
	}

	public function attributeLabels() {
		return [
			'date_from' => 'Период',
			'date_to' => '-',
			'manager' => 'Менеджер',
		];
	}
	
	protected function byDateFrom() {
		$this->getQuery()->andWhere(['>=', 'start_at', $this->date_from]);
	}
	
	protected function byDateTo() {
		$this->getQuery()->andWhere(['<=', 'start_at', $this->date_to]);
	}

	protected function byManager() {
		$this->getQuery()->andWhere('author_id = :author_id OR executor_id = :executor_id', [':author_id' => $this->manager, ':executor_id' => $this->manager]);
	}
	
}
