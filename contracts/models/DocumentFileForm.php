<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Contract;
use common\models\ContractLog;
use crm\models\Document;
use crm\modules\contracts\models\ContractDocument;

class DocumentFileForm extends \yii\base\Model {
	
	public $name;
	
	private $_contract;
	private $_document;
	
	public function __construct($config = [], Document $contractFolderFile, Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
		$this->setDocument($contractFolderFile);
	}
	
	public function rules() {
		return  [
			['name', 'required'],
		];
	}

	public function attributeLabels() {
		return [
			'name' => 'Название документа',
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
	
	public function setDocument($contractFolderFile) {
		$this->_document = $contractFolderFile;
	}
	
	/**
	 * 
	 * @return Document
	 */
	public function getDocument() {
		return $this->_document;
	}
	
	public function save() {
		$this->getDocument()->name = $this->name;
		$this->getDocument()->user_id = user()->id;
		if($this->getDocument()->save()) {
			$contractDocument = new ContractDocument;
			$contractDocument->contract_id = $this->getContract()->id;
			$contractDocument->document_id = $this->getDocument()->primaryKey;
			if(!$contractDocument->save()) {
				throw new \common\components\ValidateException('Не удалось сохранить связь договора и документа');
			}
			
			$this->getContract()->saveLog(ContractLog::LOG_CREATE_FOLDER_FILE, null, ['name' => $this->getDocument()->name]);
			return TRUE;
		}
		return FALSE;
	}

}
