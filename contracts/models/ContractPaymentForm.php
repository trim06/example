<?php

namespace crm\modules\contracts\models;

use common\models\Abonent;
use common\models\Contract;
use common\models\ContractLog;
use crm\models\ContractPayment;
use yii\base\Model;
use common\models\ContractPaymentItem;

class ContractPaymentForm extends Model {

	public $cost;
	public $comment;
	public $contract_service_id;
	public $paymentType;
	public $manager;
	private $_contract;
	private $_contractPayment;

	public function __construct($config = [], ContractPayment $contractPayment, Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
		$this->setContractPayment($contractPayment);
	}

	public function rules() {
		return [
			[['cost', 'comment', 'manager'], 'required'],
			[['cost'], 'integer'],
			[['comment'], 'string'],
			[['contract_service_id'], 'integer'],
			[['paymentType'], 'in', 'range' => array_keys(ContractPayment::getPaymentTypes())],
		];
	}

	public function attributeLabels() {
		return [
			'paymentType' => 'Способ оплаты',
			'cost' => 'Сумма платежа',
			'comment' => 'Комментарий',
			'contract_service_id' => 'Выберите услугу',
			'manager' => 'Менеджер',
		];
	}

	public function setContract($contract) {
		$this->_contract = $contract;
	}

	/**
	 * 
	 * @return Contract
	 */
	public function getContract() {
		return $this->_contract;
	}

	public function setContractPayment($contractPayment) {
		$this->_contractPayment = $contractPayment;
	}

	/**
	 * 
	 * @return ContractPaymentForm
	 */
	public function getContractPayment() {
		return $this->_contractPayment;
	}

	public function save() {
		# сохраняем платеж
		$this->getContractPayment()->cost = $this->cost;
		$this->getContractPayment()->comment = $this->comment;
		$this->getContractPayment()->user_id = $this->manager;
		$this->getContractPayment()->unit_id = user()->identity->current_unit_id;
		$this->getContractPayment()->payment_type = $this->paymentType;
		$this->getContractPayment()->contract_id = $this->getContract()->id;
		$this->getContractPayment()->abonent_id = $this->getContract()->abonent_id;
		$this->getContractPayment()->save();
		
		# сохраняем позицию платежа
		$contractPaymentItem = new ContractPaymentItem;
		$contractPaymentItem->cost = $this->cost;
		$contractPaymentItem->contract_service_id = $this->contract_service_id;
		$contractPaymentItem->contract_payment_id = $this->getContractPayment()->id;
		$contractPaymentItem->save();
		
		# если оплата не через платежную систему
		if (!$this->getContractPayment()->isPaySystem()) {
			# то сразу помечаем платеж как оплаченный
			if (!$this->getContractPayment()->paid()) {
				return false;
			}

			# отправляем смс клиенту
			app()->sms->send(Abonent::find()->andWhere(['id' => $this->getContract()->abonent_id])->one()->phone, 'Вы оплатили услуги на сумму ' . $this->cost . ' руб.');
		}

		$this->getContract()->saveLog(ContractLog::LOG_PAID, null, ['cost' => $this->cost, 'byManager' => $this->manager], $this->comment);
		return true;
	}

}
