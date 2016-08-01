<?php

namespace crm\modules\contracts\models;

use Yii;
use crm\models\Document;
use common\models\ContractLog;
use crm\models\File;

class FileForm extends \yii\base\Model {
	
	public $file;
	public $desc;
	
	private $_dicument;
	private $_file;
	
	public function __construct($config = [], File $contractFile, Document $contractFolderFile) {
		parent::__construct($config);

		$this->setDocument($contractFolderFile);
		$this->setFile($contractFile);
	}
	
	public function rules() {
		return  [
			['file', 'file', 'skipOnEmpty' => false, 'maxFiles' => 1],
			['desc', 'required'],
		];
	}

	public function attributeLabels() {
		return [
			'file' => 'Файл',
			'desc' => 'Описание',
		];
	}
	
	public function setDocument($contractFolderFile) {
		$this->_dicument = $contractFolderFile;
	}
	
	/**
	 * 
	 * @return Contract
	 */
	public function getDocument() {
		return $this->_dicument;
	}
	
	public function setFile($contractFile) {
		$this->_file = $contractFile;
	}
	
	/**
	 * 
	 * @return File
	 */
	public function getFile() {
		return $this->_file;
	}
	
	public function save() {
		$this->getFile()->ext = $this->file->extension;
		$this->getFile()->desc = $this->desc;
		$this->getFile()->user_id = user()->id;
		$this->getFile()->document_id = $this->getDocument()->id;
		if($this->getFile()->save()) {
			$this->file->saveAs(File::getPathUpload() . $this->getFile()->primaryKey . '.' . $this->file->extension);
			$this->getDocument()->contract->saveLog(ContractLog::LOG_FILE_UPLOAD, null, ['name' => $this->getFile()->name], $this->desc);
			return TRUE;
		}
		return FALSE;
	}

}
