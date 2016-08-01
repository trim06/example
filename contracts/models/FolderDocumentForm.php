<?php
namespace crm\modules\contracts\models;
/**
 * Класс формы для создания/изменения папки документов
 * @author anton2
 */
use yii\base\Model;
use common\models\File;
use common\models\Document;
use common\models\ContractLog;

class FolderDocumentForm extends Model {
	
	public $files;
	public $name;
	
	public $append = false;
	
	private $_contract;
	private $_document;
	
	public function __construct($config = [], Document $document, $contract) {
		parent::__construct($config);
		$this->setContract($contract);
		$this->setDocument($document);
	}
	
	public function rules() {
		return [
			['files', 'file', 'skipOnEmpty' => false, 'maxFiles' => 20, 'checkExtensionByMimeType' => false,
				'extensions' => "png, jpg, gif, jpeg, pdf, odt, doc, docx, txt",
				
			],
			[['name'], 'required'],
			[['name'], 'string'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'file' => 'Файлы папки',
			'name' => 'Название документа',
		];
	}
	
	public function save() {
		$this->_document->name = $this->name;
		$this->_document->contract_id = $this->getContract()->id;
		$this->_document->user_id = user()->id;
		$this->_document->file_count = ($this->_document->file_count == 0 || is_null($this->_document->file_count)) ? count($this->files) : $this->_document->file_count + count($this->files);
		$this->_document->type = Document::TYPE_FOLDER;
		if (!$this->_document->save()) {
			throw new ValidateException('Не удалось сохранить папку с файлами');
		}

		if ($this->files && $this->validate()) {
			$documentPath = $this->_document->createContractFolder().DS.$this->_document->id;
			if (!file_exists($documentPath)) {
				mkdir($documentPath, 0775, true);
			}
			foreach ($this->files as $file) {
				$documentFile = new File();
				$documentFile->user_id = user()->id;
				$documentFile->desc = $file->baseName;
				$documentFile->ext = $file->extension;
				$documentFile->document_id = $this->getDocument()->id;
				if ($documentFile->save()) {
					$file->saveAs($documentPath.'/'.$documentFile->id.'.'.$file->extension);
				} else {
					throw new \Exception('Не удалось сохранить файл');
				}
			}
			$text = ($this->append) ? 'Дополнена' : 'Создана';
			$this->getContract()->saveLog(ContractLog::LOG_FILE_UPLOAD, null, ['name' => $file->name],
					$text.' папка документов '.$this->getDocument()->id.' к договору №'.$this->getContract()->id).
					' ('.$this->getDocument()->file_count.' '.rus_plural($this->getDocument()->file_count, ['файл', 'файла', 'файлов']);
			return true;
		}
	}
	
	protected function setContract($contract) {
		$this->_contract = $contract;
	}

	public function getContract() {
		return $this->_contract;
	}

	protected function setDocument($document) {
		$this->_document = $document;
		$this->name = $document->name;
	}

	public function getDocument() {
		return $this->_document;
	}	
}

?>
