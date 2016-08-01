<?php

namespace crm\modules\contracts\models\taskforms;

use Yii;
use crm\modules\contracts\models\TaskForm;
use common\models\Task;
use common\models\Contract;
use common\models\ContractLog;

class RequestDocTaskForm extends TaskForm {

	const LOG_REQUEST_DOC = 61;
	const LOG_TRANSFER_REQUEST_DOC = 62;
	const LOG_REQUEST_DOC_END = 63;
	const LOG_CANCEL_REQUEST_DOC = 64;

	public $action_id;

	public function __construct($config = [], Task $task, Contract $contract) {
		$task->task = Task::TYPE_REQUEST_DOC;
		parent::__construct($config, $task, $contract);
		if ($this->action_id === null) {
			$this->action_id = self::LOG_REQUEST_DOC;
		}
	}

	public function buttons($form = null) {
		return [
			['label' => 'Отложить', 'attributes' => ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "$('.action_id-js').val(" . self::LOG_TRANSFER_REQUEST_DOC . "); return contracts.save(this);"]],
			['label' => 'Готово', 'attributes' => ['class' => 'btn btn-block btn-sm btn-success open-contract-card-js', 'data-url' => ("/contracts/card/".$form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_REQUEST_DOC_END . "); $('.status-js').val('" . Task::STATUS_DONE . "'); return contracts.save(this);"]],
			['label' => 'Отменить', 'attributes' => ['class' => 'btn btn-block btn-sm btn-danger open-contract-card-js', 'data-url' => ("/contracts/card/" . $form->contract->id), 'data-title' => $form->contract->abonent->name, 'data-width' => 700, 'onclick' => "$('.action_id-js').val(" . self::LOG_CANCEL_REQUEST_DOC . "); $('.status-js').val('" . Task::STATUS_CANCEL . "'); return contracts.save(this);"]],
		];
	}

	public function attributeLabels() {
		return [
			'start_at' => 'Запросить документы к',
		] + parent::attributeLabels();
	}
	
	public function saveLog() {
		return $this->getContract()->saveLog($this->action_id, $this->task_id, ['date' => $this->getTask()->start_at], $this->getTask()->comment);
	}
}
