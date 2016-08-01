<?php

namespace crm\modules\contracts\models;

use yii\helpers\ArrayHelper;
use common\models\Request;
use common\models\BaseFilterForm;

/**
 * Модель фильтра списка пользователей
 */
class RequestFilterForm extends BaseFilterForm {

	public $name;
	public $phone;
	public $role;
	public $status;
	public $city_id;
	public $source;
	public $sendType;
	//public $date_from;
	//public $date_to;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = NULL) {
		
		$this->status = Request::NEW_REQUEST;
		$query = Request::find()->with(['landing'])->andWhere(['in', 'unit_id', ArrayHelper::getColumn(user()->identity->userUnits, 'unit_id')])->orWhere(['unit_id' => null])->orderBy('created_at DESC');
		# добавляем критерий поиска по городам для НЕ админов
		if (user()->identity->role_id != 1) {
			$query->andWhere(['in', 'city_id', ArrayHelper::getColumn(user()->identity->units, 'city_id')]);
		}
		$this->setQuery($query);

		# если передан маршрут, заполняем модель
		
		if ($route) $this->fillByRoutes($route);
	}

	/**
	 * Возвращает список правил, описывающих логику работы фильтра
	 * @return array
	 */
	public function routeRules() {
		return [
			'i' => ['attr' => 'id', 'table_field' => 'contract.id', 'condition' => 'in'],
			'n' => ['attr' => 'name', 'table_field' => 'name' , 'condition' => 'like'],
			'ph' => ['attr' => 'phone', 'table_field' => 'phone', 'condition' => 'like'],
			's' => ['attr' => 'status', 'condition' => 'in'],
			'c' => ['attr' => 'city_id', 'table_field' => 'city_id', 'condition' => 'in'],
			'u' => ['attr' => 'source', 'table_field' => 'source', 'condition' => 'in'],
			't' => ['attr' => 'sendType', 'table_field' => 'send_type', 'condition' => 'in'],
			//'df' => ['attr' => 'date_from', 'function' => 'byDateFrom'],
			//'dt' => ['attr' => 'date_to', 'function' => 'byDateTo'],
			] + parent::routeRules();
	}

	public function attributeLabels() {
		return [
			'name' => 'Имя клиента',
			'phone' => 'Телефон клиента',
			'status' => 'Статус заявки',
			'city_id' => 'Город',
			'source' => 'Источник заявки',
			'sendType' => false,
		];
	}
	
	# Добавляет критерий начальной даты диапазона заявок
//	protected function byDateFrom() {
//		$date = str_replace('.', '-', $this->date_from);
//		$dateFrom = (new \DateTime($date))->format('Y-m-d 00:00:00');
//		$this->getQuery()->andWhere(['>=', 'request.created_at', $dateFrom]);
//	}
	# Добавляет критерий конечной даты диапазона заявок
//	protected function byDateTo() {
//		$date = str_replace('.', '-', $this->date_to);
//		$dateTo = (new \DateTime($date))->format('Y-m-d 23:59:59');
//		$this->getQuery()->andWhere(['<=', 'request.created_at', $dateTo]);
//	}

}
