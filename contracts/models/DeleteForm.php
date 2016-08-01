<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Contract;
use common\models\ContractLog;

class DeleteForm extends \yii\base\Model {
	
	public $comment;
	
	private $_contract;
	
	public function __construct($config = [], Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
	}
	
	public function rules() {
		return  [
			['comment', 'required'],
			['comment', 'string']
		];
	}

	public function attributeLabels() {
		return [
			'comment' => 'Причина удаления',
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
	
	public function saveContract() {
		$this->getContract()->status = Contract::STATUS_DELETED;
		if($this->getContract()->save()) {
			$this->getContract()->saveLog(ContractLog::LOG_DELETE, null, NULL, $this->comment);
			return TRUE;
		}
		return FALSE;
	}

}
