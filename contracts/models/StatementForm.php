<?php

namespace crm\modules\contracts\models;

use common\components\ValidateException;
use common\models\Contract;
use common\models\ContractLog;
use common\models\ContractStatement;
use yii\base\Model;
/**
 * Description of StatementForm
 *
 * @author anton2
 */
class StatementForm extends Model {
	
	public $file;
	public $name;
	public $text;
	public $desc;
	public $decision;
	public $status;
	public $sendType;
	public $doc_count;
	
	private $_contract;
	private $_statement;
	
	const TYPE_SMS = 'sms';
	const TYPE_EMAIL = 'email';
	const TYPE_NOT_SEND = 'not_send';

	public function __construct($config = [], ContractStatement $statement, Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
		$this->setStatement($statement);
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			['file', 'file', 'skipOnEmpty' => false, 'maxFiles' => 10],
			[['text', 'desision', 'sendType'], 'required'],
			[['text'], 'string'],
			['status', 'default', 'value' => 'N'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function scenarios() {
		return [
			'resolve' => ['decision', 'status', 'sendType'],
			parent::SCENARIO_DEFAULT => ['text', 'file'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'file' => 'Документ заявления',
			'text' => 'Текст заявления',
			'decision' => 'Решение',
			'sendType' => 'Уведомить о вынесении решения',
		];
	}
	
	public function save() {
		$this->_statement->text = $this->text;
		$this->_statement->contract_id = $this->getContract()->id;
		$this->_statement->user_id = user()->id;
		$this->_statement->doc_count = count($this->file);
		if (!$this->_statement->save()) {
			throw new ValidateException('Не удалось сохранить заявление');
		}
		
		if ($this->file && $this->validate()) {
			if (!mkdir($this->_statement->getPath(), 0700, true)) {
				throw new \Exception("Ошибка создания каталога ".$this->_statement->getPath() . "...<br/>\n");
			}
			foreach ($this->file as $file) {
				$file->saveAs($this->_statement->getPath().$file->baseName . '.' . $file->extension);
				$this->getContract()->saveLog(ContractLog::LOG_FILE_UPLOAD, null, ['name' => $file->name], 'Добавление файла к заявлению №'.$this->_statement->id);
			}
			return true;
		}
	}
	
	public function resolve() {
		$this->_statement->setScenario('resolve');
		$this->_statement->decision = $this->decision;
		$this->_statement->status = ContractStatement::STATUS_RESOLVED;
		if (!$this->_statement->save()) {
			throw new ValidateException('Не удалось сохранить заявление');
		}
		switch ($this->sendType) {
			case self::TYPE_NOT_SEND : break;
			case self::TYPE_SMS : app()->sms->send($this->_statement->abonent->phone, $this->_statement->decision); break;
			case self::TYPE_EMAIL : $message = \Yii::$app->mailer->compose()
									->setFrom('lawyer@sps23.ru')
									->setTo($this->_statement->abonent->email)
									->setSubject('Решение по Вашему заявлению')
									->setHtmlBody('<p style="font-family:colibri,sans-serif; font-size:14px;">'.nl2br($this->decision).'</p>');
									# закоментирована часть кода, предназначенная для отладки отправки писем
									//$logger = new \Swift_Plugins_Loggers_ArrayLogger();
									//\Yii::$app->mailer->getSwiftMailer()->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
									if (!$message->send()) {
										//echo $logger->dump();
										throw new \yii\web\HttpException("Не удалось отправить письмо");
									} 
									break;
			default: break;
		}
		return true;
	}

	protected function setContract(Contract $contract) {
		$this->_contract = $contract;
	}

	public function getContract() {
		return $this->_contract;
	}
	
	protected function setStatement(ContractStatement $statement) {
		$this->_statement = $statement;
		$this->text = $statement->text;
		$this->decision = $statement->decision;
		$this->status = $statement->status;
		$this->doc_count = $statement->doc_count;
	}
	
	public function getStatement() {
		return $this->_statement;
	}
	
	public static function getTypes() {
		return [
			self::TYPE_NOT_SEND => 'Не уведомлять',
			self::TYPE_SMS => 'Отправить смс',
			self::TYPE_EMAIL => 'Отправить email',
		];
	}
	
}

?>
