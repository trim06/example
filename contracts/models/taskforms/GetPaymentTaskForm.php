<?php

namespace crm\modules\contracts\models\taskforms;

use Yii;
use crm\modules\contracts\models\TaskForm;
use common\models\Task;
use common\models\Contract;
use common\models\ContractLog;

class GetPaymentTaskForm extends TaskForm {

	const LOG_GET_PAYMENT = 21;
	const LOG_TRANSFER_GET_PAYMENT = 22;
	const LOG_GET_PAYMENT_END = 23;
	const LOG_CANCEL_GET_PAYMENT = 24;

	public $action_id;

	public function __construct($config = [], Task $task, Contract $contract) {
		$task->task = Task::TYPE_GET_PAYMENT;
		parent::__construct($config, $task, $contract);
		if ($this->action_id === null) {
			$this->action_id = self::LOG_GET_PAYMENT;
		}
	}
	
	public function buttons($form = null) {
		return [
			['label' => 'Перенести', 'attributes' => ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "$('.action_id-js').val(" . self::LOG_TRANSFER_GET_PAYMENT . "); return contracts.save(this);"]],
			['label' => 'Принял', 'attributes' => ['class' => 'btn btn-block btn-sm btn-success open-contract-card-js', 'data-url' => ("/contracts/card/" . $form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_GET_PAYMENT_END . "); $('.status-js').val('" . Task::STATUS_DONE . "'); return contracts.save(this);"]],
			['label' => 'Отменить', 'attributes' => ['class' => 'btn btn-block btn-sm btn-danger open-contract-card-js', 'data-url' => ("/contracts/card/" . $form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_CANCEL_GET_PAYMENT . "); $('.status-js').val('" . Task::STATUS_CANCEL . "'); return contracts.save(this);"]],
		];
	}

	public function attributeLabels() {
		return [
			'start_at' => 'Дата приема платежа',
		] + parent::attributeLabels();
	}
	
	public function saveLog() {
		return $this->getContract()->saveLog($this->action_id, $this->task_id, ['get_payment_date' => $this->getTask()->start_at], $this->getTask()->comment);
	}
}
