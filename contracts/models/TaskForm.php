<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Task;
use common\models\Contract;
use common\models\Document;
use common\components\ValidateException;
use crm\modules\contracts\models\taskforms\CallTaskForm;

class TaskForm extends \yii\base\Model {	
	
	public $start_at;
	public $end_at;
	public $comment;
	public $executor_id;
	public $status;
	
	public $task_id;
	
	private $_task;
	private $_contract;
	
	protected $logAction;
	
	public function __construct($config = [], Task $task, Contract $contract) {
		parent::__construct($config);
		
		$this->setContract($contract);
		$this->setTask($task);
		$this->task_id = $task->id;
	}

	public function scenarios() {
		return [
			'createTask' => ['start_at', 'end_at', 'comment', 'status', 'executor_id', 'action_id', 'smsText', 'sendSms'],
			'completeTask' => ['start_at', 'end_at', 'status', 'executor_id', 'action_id', 'smsText', 'sendSms'],
		];
	}
	
	public function rules() {
		return  [
			[['start_at', 'end_at', 'comment', 'status'], 'string'],
			[['executor_id', 'action_id'], 'integer'],
			[['start_at'], 'required'],
		];
	}

	public function attributeLabels() {
		return [
			'phone' => 'Телефон',
			'name' => 'ФИО',
			'start_at' => 'Дата',
			'comment' => 'Комментарий',
			'executor_id' => 'Ответственный',
		];
	}
	
	public function fillTask() {
		$this->getTask()->author_id = user()->id;
		$this->getTask()->executor_id = $this->executor_id ? $this->executor_id : user()->id;
		$this->getTask()->start_at = $this->start_at;
		$this->getTask()->unit_id = user()->identity->current_unit_id;
		
		# Вычисляю дату и время когда встречу необходимо завершить
		/*if(!$this->end_at && $this->start_at) {
			$this->getTask()->end_at = date('Y-m-d H:i:s', strtotime($this->start_at) + 60*60);
		} else {
			$this->getTask()->end_at = $this->end_at;
		}*/
		if (in_array($this->status, [Task::STATUS_DONE, Task::STATUS_CANCEL]) && $this->status !== $this->getTask()->status) {
			$this->getTask()->end_at = date('Y-m-d H:i:s');
		}
		$this->getTask()->status = $this->status ? $this->status : Task::STATUS_NEW;
		$this->getTask()->comment = $this->comment;
		return $this->getTask();
	}
	
	public function saveTask() {
		$this->fillTask();
		$this->getTask()->validate();

		if ($this->getTask()->link('contract', $this->getContract()) === FALSE) {
			throw new ValidateException('При сохранении задачи произошла ошибка');
		}

		$this->task_id = $this->getTask()->id;
		$this->saveLog();
		if ($this->action_id == taskforms\MeetTaskForm::LOG_MEET && $this->sendSms) {
			$phone = $this->contract->abonent->phone;
			$message = $this->smsText;
			Yii::$app->sms->send($phone, $message);
		}		
		
		# Создание задачи по прозвону клиентов для Маргариты, при выполнении юристами(Есилевский Андрей, Мыцик Андрей) какого-либо действия
		if (in_array(user()->id, [53791, 66651]) && $this->getContract()->status === Contract::STATUS_PAYMENT) {
			$this->createMargoTask();
		}
		return TRUE;
	}
	
	/**
	 * Создание задачи по прозвону клиентов для Маргариты
	 * @throws \Exception в случае ошибки сохренения задачи
	 */
	public function createMargoTask() {
		# id Маргариты
		$margoId = 53831;
		
		$taskForMargo = new Task();
		$taskForMargo->task = Task::TYPE_CALL;
		$taskForMargo->contract_id = $this->getContract()->id;
		$taskForMargo->author_id = user()->id;
		$taskForMargo->executor_id = 53831;
		# Если назначаемая задача выходит за рамки рабочего времени, то переносим на завтра
		$start_at = new \DateTime();
		$start_at->add(new \DateInterval('PT2H'));
		if ((int)$start_at->format('H') >= 18 || (int)$start_at->format('H') < 8) {
			$start_at->add(new \DateInterval('P1D'));
			$start_at->setTime(10, 00, 00);	
		}
		$start_at = $start_at->format('Y-m-d H:i:s');
		$taskForMargo->start_at = $start_at;
		$taskForMargo->end_at = date('Y-m-d H:i:s', strtotime($start_at) + 60 * 60);
		$taskForMargo->status = Task::STATUS_NEW;
		$taskForMargo->comment = Task::getTaskList($this->getTask()->task);
		if (!$taskForMargo->save()) {
			throw new \Exception("Ошибка создания задачи для Маргариты");
		}
		$this->getContract()->saveLog(CallTaskForm::LOG_CALL, $taskForMargo->id, [], 'По причине: '.Task::getTaskList($this->getTask()->task));
	}
	
	/**
	 * Создание задачи по прозвону клиентов для Маргариты
	 * @param \common\models\Document $document объект документа
	 * @param boolean $isNew - признак того, что документ создан
	 * @throws \Exception
	 */
	public static function createMargoTaskOnDocument(Document $document, $isNew = true) {
		if (!in_array(user()->id, [53791, 66651])) return;
		# id Маргариты
		$margoId = 53831;

		$taskForMargo = new Task();
		$taskForMargo->task = Task::TYPE_CALL;
		$taskForMargo->contract_id = $document->contract_id;
		$taskForMargo->author_id = user()->id;
		$taskForMargo->executor_id = 53831;
		# Если назначаемая задача выходит за рамки рабочего времени, то переносим на завтра
		$start_at = new \DateTime();
		$start_at->add(new \DateInterval('PT2H'));
		if ((int) $start_at->format('H') >= 18 || (int) $start_at->format('H') < 8) {
			$start_at->add(new \DateInterval('P1D'));
			$start_at->setTime(10, 00, 00);
		}
		$start_at = $start_at->format('Y-m-d H:i:s');
		$taskForMargo->start_at = $start_at;
		$taskForMargo->end_at = date('Y-m-d H:i:s', strtotime($start_at) + 60 * 60);
		$taskForMargo->status = Task::STATUS_NEW;
		$taskForMargo->comment = ($isNew) ? 'Создан документ "'.$document->name.'" по договору №'.$document->contract_id : 'Документ "'.$document->name.'" по договору №'.$document->contract_id.' изменен';
		if (!$taskForMargo->save()) {
			throw new \Exception("Ошибка создания задачи для Маргариты");
		}
		$document->contract->saveLog(CallTaskForm::LOG_CALL, $taskForMargo->id, [], 'По причине: '.$taskForMargo->comment);
	}
	
	public function setTask(Task $task) {
		# теперь каждый раз будет выводиться новая задача
		$this->start_at = $task->start_at ? date('d.m.Y H:i', strtotime($task->start_at)) : null;
		$this->end_at = $task->end_at;
		$this->comment = $task->comment;
		# если это редактирование задачи, то выводим исполнителя,
		# иначе исполнителем по умолчанию назначается текущий пользователь
		if (!$task->isNewRecord) {
			$this->executor_id = $task->executor_id;
		} else {
			$this->executor_id = user()->identity->id;
		}
		
		$this->_task = $task;
	}
	
	/**
	 * 
	 * @return Task
	 */
	public function getTask() {
		return $this->_task;
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
	
	public function saveLog() {
		return FALSE;
	}

}
