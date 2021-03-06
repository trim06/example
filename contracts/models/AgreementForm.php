<?php

namespace crm\modules\contracts\models;

use common\models\Contract;
use common\models\ContractLog;
use common\models\Service;
use common\models\ContractService;
use crm\models\ContractPayment;
use common\models\ContractPaymentItem;
use common\models\Task;
use common\models\Abonent;
use common\components\ValidateException;

class AgreementForm extends \yii\base\Model {

	public $cost;
	public $payment;
	public $payment_comment;
	public $day_payment;
	public $comment;
	public $first_installment;
	public $serviceId;
	public $paymentType;
	private $_services;
	private $_contract;
	private $_contractPayment;

	public function __construct($config = [], Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
	}

	public function rules() {
		return [
			[['cost', 'comment', 'payment', 'day_payment', 'serviceId'], 'required'],
			[['cost', 'first_installment'], 'integer'],
			[['comment'], 'string'],
			[['payment', 'day_payment', 'payment_comment', 'serviceId'], 'safe'],
			['paymentType', 'required', 'when' => function($model) {
					# проверяем, если заполнено поле first_installment
					return (!$model->getErrors('first_installment') && $model->first_installment > 0);
				}],
			[['paymentType'], 'in', 'range' => array_keys(ContractPayment::getPaymentTypes())]
		];
	}

	public function attributeLabels() {
		return [
			'cost' => 'Стоимость договора',
			'payment' => 'Ежемесячный платёж',
			'day_payment' => 'День выплаты',
			'first_installment' => 'Первый взнос',
			'comment' => 'Комментарий к оплате',
			'serviceId' => 'Выберите услуги',
			'paymentType' => 'Способ оплаты'
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

	/**
	 * Сохраняем данные договора
	 * @return boolean
	 * @throws ValidateException
	 */
	public function save() {
		if (count($this->_contract->services) === 0) {
			$isNewContract = true;
		} else {
			$isNewContract = false;
			$countOfServices = count(ContractService::find()->andWhere(['contract_id' => $this->getContract()->id])->all());
		}
		# создание услуг по контракту и кредиту
		foreach ($this->serviceId as $serviceId => $credit) {
			foreach ($credit as $credit_id => $creditService) {
				if ($creditService['cost'] == 0) {continue;}
				$contractService = new \crm\models\ContractService;
				$contractService->service_id = $serviceId;
				$contractService->user_id = user()->id;
				$contractService->unit_id = user()->identity->current_unit_id;
				$contractService->contract_id = $this->getContract()->id;
				$contractService->cost = $creditService['cost'];
				# если услуга работает с кредитом, сохраняем $credit_id, иначе оставляем пустым пустым
				if ($credit_id != 0) {
					$contractService->credit_id = $credit_id;
				} else {
					$contractService->credit_id = null;
				}
				if (!$contractService->save()) {
					throw new ValidateException('ContractService not save');
				};
				$this->setServices($contractService);
			}
			
		}
		# создание платежа по услуге
		if ($this->first_installment > 0) {			
			# сохраняем платеж
			$contractPayment = new ContractPayment;
			$contractPayment->cost = $this->first_installment;
			$contractPayment->payment_type = $this->paymentType;
			$contractPayment->comment = $this->comment;
			$contractPayment->user_id = user()->id;
			$contractPayment->unit_id = user()->identity->current_unit_id;
			$contractPayment->contract_id = $this->getContract()->id;
			$contractPayment->abonent_id = $this->getContract()->abonent_id;
			$contractPayment->save();
			
			# сохраняем позиции платежа
			foreach ($this->serviceId as $serviceId => $credit) {
				foreach ($credit as $credit_id => $creditService) {
					if ($creditService['cost'] == 0 || $creditService['first_installment'] == 0) {
						continue;
					}
					$contractPaymentItem = new ContractPaymentItem;
					$contractPaymentItem->cost = $creditService['first_installment'];
					$contractPaymentItem->contract_service_id = $this->getServiceId($serviceId, $credit_id);
					$contractPaymentItem->contract_payment_id = $contractPayment->id;
					$contractPaymentItem->save();
				}
			}
			$this->_contractPayment = $contractPayment;
			
			
			if (!$contractPayment->isPaySystem()) {
				# то сразу помечаем платеж как оплаченный
				if (!$contractPayment->paid()) {

			
					throw new ValidateException('ContractPayment not save');
				}

				# отправляем смс клиенту
				app()->sms->send(Abonent::find()->andWhere(['id' => $this->getContract()->abonent_id])->one()->phone, 'Вы оплатили услуги на сумму ' . $this->first_installment . ' руб.');
			}
			
		}
		# создание графика платежей в виде задач
		foreach ($this->day_payment as $i => $date) {
			$cost = ag($this->payment, $i, 0);
			$comment = ag($this->payment_comment, $i, 0);
			if (!$date || !$cost) {
				continue;
			}

			/* Сохраняем пункты графика оплат в задачи */
			$paymentTask = new Task();
			$paymentTask->contract_id = $this->getContract()->id;
			$paymentTask->unit_id = user()->identity->current_unit_id;
			$paymentTask->task = Task::TYPE_GET_PAYMENT;
			$paymentTask->author_id = user()->id;
			$paymentTask->executor_id = user()->id;
			$date = new \DateTime($date);
			$paymentTask->start_at = $date->format('Y-m-d 12:00:00');
			$paymentTask->end_at = $date->format('Y-m-d 12:00:00');
			$paymentTask->comment = (($comment === '') ? '' : $comment.' ').'('.$cost.' руб.)';

			if (!$paymentTask->save()) {
				throw new ValidateException('Task not save');
			}
			$this->getContract()->saveLog(taskforms\GetPaymentTaskForm::LOG_GET_PAYMENT, $paymentTask->id, ['get_payment_date' => $paymentTask->start_at], $paymentTask->comment);
		}

		$this->getContract()->status = Contract::STATUS_PAYMENT;
		$this->getContract()->cost = $this->cost;

		if ($this->getContract()->save()) {
			# при подписании договора
			if ($isNewContract && $this->getContract()->status === Contract::STATUS_PAYMENT) {
				$this->getContract()->user_id = user()->identity->id;
				$this->getContract()->signed_at = date("Y-m-d H:i:s");
				$this->getContract()->saveLog(ContractLog::LOG_CONTRACT_AGREEMENT);
				$this->getContract()->saveLog(ContractLog::LOG_PAID_ON_AGREMENT, null, ['cost' => $this->cost], $this->comment);
			} else {
				# при добавлении услуги
				$this->getContract()->saveLog(ContractLog::LOG_PAID, null, ['cost' => $this->cost], $this->comment);
				if (count(ContractService::find()->andWhere(['contract_id' => $this->getContract()->id])->all()) > $countOfServices) {
						$this->getContract()->saveLog(ContractLog::LOG_NEW_SERVICE);
					}
				}
			return TRUE;
		} else {
			throw new ValidateException('Contract not save');
		}
		return FALSE;
	}

	public function getContractPayment() {
		return $this->_contractPayment;
	}
	
	public function getServices() {
		return $this->_services;
	}
	
	public function setServices($service) {
		if (!$this->_services) {
			$this->_services = [];
		}
		$this->_services[] = $service;
	}
	
	/**
	 * Возвращает ID конкретной услуги по заданым ID услуги и кредита
	 * @param type $serviceId - ID услуги
	 * @param null $credit_id - ID кредита
	 * @return null
	 */
	protected function getServiceId($serviceId, $credit_id) {
		if ($credit_id == 0) {$credit_id = null;}
		foreach ($this->_services as $service) {
			if ($service->service_id == $serviceId && $service->credit_id == $credit_id) {
				return $service->id;
			}
		}
		return null;
	}
	
}
