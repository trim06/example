<?php

namespace crm\modules\contracts\models;

use common\models\BaseFilterForm;
use common\models\ContractStatement;

/**
 * Модель фильтра списка пользователей
 */
class StatementFilterForm extends BaseFilterForm {

	public $name;
	public $phone;
	public $contract_id;
	public $status;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = null) {
		$this->setQuery(ContractStatement::find()->with('contract')->joinWith(['user', 'abonent']));
		
		# если передан маршрут, заполняем модель
		if ($route) $this->fillByRoutes($route);
		
		$this->setQuery($this->_query->orderBy('contract_statement.status DESC, contract_statement.created_at ASC'));
	}

	/**
	 * Возвращает список правил, описывающих логику работы фильтра
	 * @return array
	 */
	public function routeRules() {
		return [
			'n' => ['attr' => 'name', 'function' => 'byName'],
			'ph' => ['attr' => 'phone', 'function' => 'byPhone'],
			'c' => ['attr' => 'contract_id', 'table_field' => 'contract_statement.contract_id', 'condition' => 'like'],
			's' => ['attr' => 'status', 'table_field' => 'contract_statement.status', 'condition' => 'in'],
			] + parent::routeRules();
	}

	public function attributeLabels() {
		return [
			'name' => 'Имя клиента',
			'phone' => 'Телефон клиента',
			'contract_id' => 'Номер договора',
			'status' => 'Статус заявления'
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
	
}
