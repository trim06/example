<?php

namespace crm\modules\contracts\models\taskforms;

use Yii;
use crm\modules\contracts\models\TaskForm;
use common\models\Task;
use common\models\Contract;
use common\models\ContractLog;

class MeetTaskForm extends TaskForm {
	
	const LOG_MEET = 1;
	const LOG_TRANSFER_MEET = 2;
	const LOG_MEET_END = 3;
	const LOG_CANCEL_MEET = 4;

	public $action_id;
	public $smsText;
	public $sendSms;

	public function __construct($config = [], Task $task, Contract $contract) {
		$task->task = Task::TYPE_MEET;
		parent::__construct($config, $task, $contract);
		if ($this->action_id === null) {
			$this->action_id = self::LOG_MEET;
		}
	}


	public function buttons($form = null) {
		return [
			['label' => 'Сохранить', 'attributes' => ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "$('.action_id-js').val(" . self::LOG_MEET . "); return contracts.save(this);"]],
			['label' => 'Перенести встречу', 'attributes' => ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "$('.action_id-js').val(" . self::LOG_TRANSFER_MEET . "); return contracts.save(this);"]],
			['label' => 'Пришел на встречу', 'attributes' => ['class' => 'btn btn-block btn-sm btn-success open-contract-card-js', 'data-url' => ("/contracts/card/".$form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_MEET_END . "); $('.status-js').val('".Task::STATUS_DONE."'); return contracts.save(this);"]],
			['label' => 'Отменить встречу', 'attributes' => ['class' => 'btn btn-block btn-sm btn-danger open-contract-card-js', 'data-url' => ("/contracts/card/".$form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_CANCEL_MEET . "); $('.status-js').val('" . Task::STATUS_CANCEL . "'); return contracts.save(this);"]],
		];
	}	

	public function rules() {
		return array_merge([
			['smsText', 'string', 'max' => 200],
			['sendSms',  'boolean']
		], parent::rules());
	}

	public function attributeLabels() {
		return ['start_at' => 'Дата встречи', 'executor_id' => 'Встреча с'] + parent::attributeLabels();
	}

	public function saveLog() {
		return $this->getContract()->saveLog($this->action_id, $this->task_id, ['meet_date' => $this->getTask()->start_at], $this->getTask()->comment);
	}

}
