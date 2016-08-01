<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Abonent;
use common\models\Contract;
use common\models\Request;
use common\models\ContractLog;
use common\models\AbonentContractCredit;
use common\components\ValidateException;
use yii\db\Expression;

class ContractForm extends \yii\base\Model {

	public $name;
	public $phone;
	public $email;
	public $status;
	public $unit_id;
	public $city_id;
	public $credits = [];
	public $comment;
	public $statement_name;
	public $statement_text;
	
	# доп. поля для завки на списание долга

	private $_abonent;
	private $_contract;
	private $_listAbonentContractCredit;
	private $_request;

	public function __construct($config = [], Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
	}
	
	public function scenarios() {
		return [
			parent::SCENARIO_DEFAULT => ['status', 'name', 'phone', 'credits', 'comment', 'email'],
			'agreement' => ['status', 'name', 'phone', 'credits', 'comment', 'email'],
		];
	}

	public function rules() {
		return  [
			# статус
			['status', 'default', 'value' => Contract::STATUS_PROCESSING],
			# имя пользователя
			['name', 'filter', 'filter' => 'trim'],
			['name', 'required'],
			['name', 'match', 'pattern' => '/(\S*\s){2,}\s?/', 'on' => ['agreement'], 'message' => 'ФИО должно состоять минимум из трех слов'],
			['name', 'filter', 'filter' => [$this, 'transformCase']],
			# телефон
			['phone', 'filter', 'filter' => 'trim'],
			['phone', 'filter', 'filter' => [$this, 'filterDigitsOnly']],
			['phone', 'required'],
			['phone', 'match', 'pattern' => '/^[0-9].{9,9}$/', 'message' => 'Номер телефона указан неверно', 'enableClientValidation' => FALSE],
			['phone', 'unique', 'targetClass' => '\common\models\User', 'targetAttribute' => 'id', 'message' => 'Указанный номер телефона уже зарегистрирован в базе.'],
			['credits', 'safe'],
			# комментарий
			['comment', 'string'],
			# электронная почта
			['email', 'filter', 'filter' => 'trim'],
			['email', 'email'],
			['email', 'default', 'value' => ''],
			
		];
	}

	/** Фильтр валидации отсекающий любые символы кроме цифр */
	public function filterDigitsOnly($value) {
		return preg_replace('#[\D]*#', '', $value);
	}
	
	/** Фильтрация регистра ФИО */
	public function transformCase($name) {
		return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
	}

	public function attributeLabels() {
		return parent::attributeLabels() + [
			'phone' => 'Телефон',
			'name' => 'ФИО',
			'email' => 'Электронная почта',
			'status' => 'Статус договора',
			'month_payment'=> 'Ежемесячный платеж',
			'creditor_name'=> 'Название кредитной организации',
			'loan_agreement'=> 'Номер и дата кредитного договора',
		];
	}
	
	
	public function createContract() {
		# ищем абонента по номеру телефона
		$this->changeAbonent();
		
	}
	

	/**
	 * Создает новый договор
	 * @param boolean $withoutLogging Если true, то отключаем логирование при создании нового договора
	 * @return boolean
	 */
	public function saveContract($withoutLoging = false) {
		if (is_null($this->contract->id)) {
			$isNewContract = true;
		} else {
			$isNewContract = false;
		}
		# если номер телефона изменился, то меняем абонента
		if (!$this->_abonent || $this->_abonent->phone != $this->phone) $this->changeAbonent();

		# сохраняем абонента
		$this->_abonent->name = $this->name;
		$this->_abonent->email = $this->email;

		# сохраняем договор
		$this->_contract->status = $this->status;
		$this->_contract->type_id = 12;
		$this->_contract->comment = $this->comment;
		# добавление полей unit_id и city_id только при создании договора
		if ($this->_contract->isNewRecord) {
			$this->status = 'processing';
			$this->_contract->unit_id = user()->identity->current_unit_id;
			$this->_contract->city_id = user()->identity->currentUnit->city_id;
		}
		
		# сохраняем абонента
		if(!$this->_abonent->save()) throw new ValidateException('При сохранении абонента произошла ошибка');
		
		$this->_contract->expire_at = new Expression('NOW() + INTERVAL 30 DAY');
		
		# сохраняем договор
		$this->_contract->validate();
		if($this->_contract->link('abonent', $this->_abonent) === FALSE) throw new ValidateException('При сохранеии договора произошла ошибка');
		# если $withoutLoging = true, то пропускаем логирование
		if ($isNewContract && !$withoutLoging) {
			$this->_contract->saveLog(ContractLog::LOG_CREATE_CONTRACT);
		}
		foreach($this->credits as $key => $credit) {
			$modelCredit = is_numeric($key) ? AbonentContractCredit::findOne($key) : new AbonentContractCredit();
			$modelCredit->load($credit, '');
			$modelCredit->abonent_id = $this->_abonent->id;
			$modelCredit->save();
		}
		
		return $this;
	}
	
	public function saveContractOnRequest() {
		# создаем новый договор, не записывая в лог
		$this->saveContract(true);
		# записываем в лог здесь (создание договора из заявки)
		$r = $this->getContract()->saveLog(ContractLog::LOG_CREATE_ON_REQUEST, null, ['request_id' => $this->getRequest()->id]);
		$this->getRequest()->status = \common\models\Request::CREATED_CONTRACT;
		$this->getRequest()->contract_id = $this->getContract()->id;
		$this->getRequest()->save();
		if ($this->getRequest()->uid === '' || $this->getRequest()->uid === null) {
			db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::CREATED_CONTRACT."' WHERE r.phone = '".$this->getRequest()->phone."'")->execute();
		} else {
			db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::CREATED_CONTRACT."' WHERE r.uid = '" . $this->getRequest()->uid . "' AND r.phone = '".$this->getRequest()->phone."'")->execute(); // todo anton2 заменить OR на AND
		}
		return $r;
	}

	/**
	 * Заменят модель абонента, при изменении номера телефона
	 * @return boolean
	 */
	private function changeAbonent() {
		# ищем абонента по номеру телефона
		$this->_abonent = Abonent::find()->where(['phone' => $this->phone])->one();

		# если в базе нет абонента с таким номером телефона, то создаем нового
		if ($this->_abonent === NULL) {
			$this->_abonent = new Abonent();
			$this->_abonent->phone = $this->phone;
		}

		return $this;
	}

	/** -------- <Геттеры и сеттеры> -------- */

	/**
	 * Возвращает модель абонента
	 * @return Abonent
	 */
	public function getAbonent() {
		return $this->_abonent;
	}

	/**
	 * Задает модель абонента
	 * @param \common\models\Abonent $abonent
	 * @return \crm\modules\contracts\models\ContractForm
	 */
	public function setAbonent(Abonent $abonent) {
		$this->_abonent = $abonent;
		$this->name = $abonent->name;
		$this->phone = $abonent->phone;
		$this->email = $abonent->email;
		return $this;
	}

	/**
	 * Возвращает модель договора
	 * @return Contract
	 */
	public function getContract() {
		return $this->_contract;
	}

	/**
	 * Задает модель договора
	 * @param \common\models\Contract $contract
	 * @return \crm\modules\contracts\models\ContractForm
	 */
	public function setContract(Contract $contract) {
		$this->_contract = $contract;
		if ($contract->abonent) $this->setAbonent($contract->abonent);
		$this->status = $contract->status;
		$this->comment = $contract->comment;
		$this->city_id = $contract->city_id;

		return $this;
	}
	
	public function setRequest(\common\models\Request $request) {
		$this->_request = $request;
		$this->name = $request->name;
		$this->phone = $request->phone;
	}
	
	/**
	 * @return \common\models\Request
	 */
	public function getRequest() {
		return $this->_request;
	}
	
	/**
	 * Возвращает список кредитных договоров абонента
	 * @return array
	 */
	public function getListAbonentContractCredit() {
		if(!$this->_listAbonentContractCredit) {
			$list = AbonentContractCredit::find()->where(['abonent_id' => $this->_contract->abonent_id])->all();
			$this->_listAbonentContractCredit = $list ? $list : [new AbonentContractCredit];
		}
		return $this->_listAbonentContractCredit;
	}

}
