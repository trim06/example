<?php
namespace crm\modules\contracts\models;
/**
 * Description of FolderDocumentForm
 *
 * @author anton2
 */
use yii\base\Model;
use common\models\Document;
use common\models\ContractLog;

class PortableDocumentForm extends Model {
	
	public $file;
	public $name;
	
	private $_contract;
	private $_document;
	
	public function __construct($config = [], Document $document, $contract) {
		parent::__construct($config);
		$this->setContract($contract);
		$this->setDocument($document);
	}
	
	public function rules() {
		return [
			['file', 'file', 'skipOnEmpty' => false, 'maxFiles' => 1, 'extensions' => ['pdf']],
			[['name'], 'required'],
			[['name'], 'string'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'file' => 'Документ',
			'name' => 'Название документа',
		];
	}
	
	public function save() {
		$this->_document->name = $this->name;
		$this->_document->contract_id = $this->getContract()->id;
		$this->_document->user_id = user()->id;
		$this->_document->file_count = count($this->file);
		$this->_document->type = Document::TYPE_PDF;
		if (!$this->_document->save()) {
			throw new ValidateException('Не удалось сохранить папку с файлами');
		}
		
		if ($this->file && $this->validate()) {
			$documentPath = $this->_document->createContractFolder();
			$this->file->saveAs($documentPath.DS.$this->_document->id.'.'.$this->file->extension);
		}
		$this->getContract()->saveLog(ContractLog::LOG_FILE_UPLOAD, null, ['name' => $this->file->name], 'Загружен PDF-файл, ID: '.$this->_document->id.' к договору №'.$this->getContract()->id);
		return true;
	}
	
	protected function setContract($contract) {
		$this->_contract = $contract;
	}

	public function getContract() {
		return $this->_contract;
	}

	protected function setDocument($statement) {
		$this->_document = $statement;
	}

	public function getDocument() {
		return $this->_document;
	}	
}
