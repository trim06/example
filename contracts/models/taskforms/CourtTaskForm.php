<?php

namespace crm\modules\contracts\models\taskforms;

use Yii;
use crm\modules\contracts\models\TaskForm;
use common\models\Task;
use common\models\Contract;
use common\models\ContractLog;

class CourtTaskForm extends TaskForm {

	const LOG_COURT = 41;
	const LOG_TRANSFER_COURT = 42;
	const LOG_COURT_END = 43;
	const LOG_CANCEL_COURT = 44;

	public $action_id;

	public function __construct($config = [], Task $task, Contract $contract) {
		parent::__construct($config, $task, $contract);
		
		$task->task = Task::TYPE_COURT;
		if ($this->action_id === null) {
			$this->action_id = self::LOG_COURT;
		}
	}

	public function buttons($form = null) {
		return [
			['label' => 'Перенести', 'attributes' => ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "$('.action_id-js').val(" . self::LOG_TRANSFER_COURT . "); return contracts.save(this);"]],
			['label' => 'Завершено', 'attributes' => ['class' => 'btn btn-block btn-sm btn-success open-contract-card-js', 'data-url' => ("/contracts/card/".$form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_COURT_END . "); $('.status-js').val('" . Task::STATUS_DONE . "'); return contracts.save(this);"]],
			['label' => 'Отменить', 'attributes' => ['class' => 'btn btn-block btn-sm btn-danger open-contract-card-js', 'data-url' => ("/contracts/card/" . $form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_CANCEL_COURT . "); $('.status-js').val('" . Task::STATUS_CANCEL . "'); return contracts.save(this);"]],
		];
	}

	public function attributeLabels() {
		return ['start_at' => 'Дата судебного заседания'] + parent::attributeLabels();
	}
	
	public function saveLog() {
		return $this->getContract()->saveLog($this->action_id, $this->task_id, ['date' => $this->getTask()->start_at], $this->getTask()->comment);
	}
}
