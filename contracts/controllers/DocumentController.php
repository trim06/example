<?php

namespace app\modules\contracts\controllers;
/**
 * Контроллер для работы с документами
 *
 * @author anton2
 */

use common\models\Contract;
use common\models\Document;
use common\models\File;
use yii\web\UploadedFile;
use common\components\BaseController;
use crm\models\GoogleAPIClient;
use common\models\DocumentPattern;
use crm\modules\contracts\models\TaskForm;
use crm\modules\contracts\models\FolderDocumentForm;
use crm\modules\contracts\models\PortableDocumentForm;

class DocumentController extends BaseController {
	
	/** ID общедоступной папки для документов */
	const SHARED_FOLDER_ID = '0B2OPAIMw393JaVNETmRld2p1WG8';

	/**
	 * Создает новый текстовый документ в Google Drive для дальнейшей работы
	 * @return int ID созданного файла в Google Drive
	 */
	public function actionCreateNewDoc() {
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		# Создание нового документа в Google Drive
		$fileMetadata = new \Google_Service_Drive_DriveFile([
			'title' => 'Новый пустой документ',
			'mimeType' => 'application/vnd.google-apps.document',
		]);
		$file = $service->files->insert($fileMetadata, array(
			'fields' => 'id')
		);
		$fileId = $file->getId();
		# Переносим созданный файл в папку LawDocuments
		$listParents = $service->parents->listParents($fileId);
		$parents = $listParents->getItems();
		$newParent = new \Google_Service_Drive_ParentReference();
		$newParent->setId(self::SHARED_FOLDER_ID);
		try {
			# Удаляем предыдущие папки
			foreach ($parents as $parent) {
				$service->parents->delete($fileId, $parent->id);
			}
			$service->parents->insert($fileId, $newParent);
		} catch (\Exception $e) {
			print "An error occurred: ".$e->getMessage();
		}
		
		return $fileId;
	}
	
	/**
	 * Выводит представление для выбора шаблона нового документа
	 * @param int $contractId ID договора
	 * @return
	 */
	public function actionPatternSelect($contractId) {
		$patterns = DocumentPattern::find()->andWhere(['unit_id' => user()->identity->current_unit_id])->all();
		return $this->renderAjax('document_patterns', ['contractId' => $contractId, 'patterns' => $patterns]);
	}
	
	/**
	 * 
	 * @param int $patternId ID шаблона нового документа
	 * @return int ID файла на Google Drive
	 */
	public function actionCreateByPattern($patternId) {
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		# Создание нового документа в Google Drive
		$fileMetadata = new \Google_Service_Drive_DriveFile([
			'title' => 'Новый договор',
			'mimeType' => 'application/vnd.google-apps.document',
		]);
		$file = $service->files->copy($patternId, $fileMetadata);
		$fileId = $file->getId();
		# Переносим созданный файл в папку LawDocuments
		$listParents = $service->parents->listParents($fileId);
		$parents = $listParents->getItems();
		$newParent = new \Google_Service_Drive_ParentReference();
		$newParent->setId(self::SHARED_FOLDER_ID);
		try {
			# Удаляем предыдущие папки
			foreach ($parents as $parent) {
				$service->parents->delete($fileId, $parent->id);
			}
			$service->parents->insert($fileId, $newParent);
		} catch (\Exception $e) {
			print "An error occurred: ".$e->getMessage();
		}

		return $fileId;
	}
	
	/**
	 * Сохраняет документ из Google Docs на сервер в формате .docx
	 * @param type $id ID файла в Google Drive
	 * @param type $contractId ID договора
	 * @throws \yii\web\HttpException в случае ошибки сохранения в БД
	 */
	public function actionSaveDoc($id, $contractId) {
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		# Получаем ссылку и загружаем файл с диска
		$file = $service->files->get($id);
		$fileUrl = $file->getExportLinks()['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
		$content = file_get_contents($fileUrl);
		# Записываем содеяное в БД
		$document = new Document();
		$document->name = $file->getTitle();
		$document->contract_id = $contractId;
		$document->type = Document::TYPE_DOCUMENT;
		$document->user_id = user()->id;
		$document->edited_at = (new \DateTime())->format('Y-m-d H:i:s');
		if (!$document->save()) {
			throw new \yii\web\HttpException(500, 'Не удалось сохранить документ');
		}
		# Создаем папку если ее нет
		$documentFolder = $document->createContractFolder();
		if (!file_exists($documentFolder)) {
			mkdir($documentFolder, 0775, true);
		}
		# Сохраняем файл на сервер
		file_put_contents($documentFolder.DS.$document->id.'.docx', $content);
		
		# удаляем документ с Google Drive
		$service->files->delete($file->getId());
		# Создаем таску для Марго о создании документа
		TaskForm::createMargoTaskOnDocument($document);
	}
	
	/**
	 * Сохраняет документ на сервер после его редактирования в Google Drive
	 * @param int $id ID файла в Google Drive
	 * @param int $documentId ID документа
	 * @throws \yii\web\HttpException
	 */
	public function actionUpdateDoc($id, $documentId) {
		$document = Document::findOne(['id' => $documentId]);
		
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		$file = $service->files->get($id);
		$fileUrl = $file->getExportLinks()['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
		$content = file_get_contents($fileUrl);
		# Создаем папку если ее нет
		$documentFolder = $document->createContractFolder();
		if (!file_exists($documentFolder)) {
			mkdir($documentFolder, 0775, true);
		}
		# Сохраняем файл на сервер
		file_put_contents($documentFolder.DS.$document->id.'.docx', $content);
		# Фиксируем изменения в БД
		$document->name = $file->getTitle();
		$document->edited_at = (new \DateTime())->format('Y-m-d H:i:s');
		if (!$document->save()) {
			throw new \yii\web\HttpException(500, 'Не удалось сохранить документ');
		}
		# удаляем документ с Google Drive
		$service->files->delete($file->getId());
		# Создаем таску для Марго о изменении документа
		TaskForm::createMargoTaskOnDocument($document, false);
	}
	
	/**
	 * Копирует файл с сервера на Google Drive для дальшейшего редактирования
	 * @param int $documentId ID документа
	 * @param int $contractId ID договора
	 * @return string JSON с данными для окна редактирования документа
	 */
	public function actionOpenWithDocs($documentId, $contractId) {
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		$document = Document::findOne(['id' => $documentId]);
		$content = file_get_contents($document->getPath('docx'));
		
		$file = new \Google_Service_Drive_DriveFile();
		$file->setTitle($document->name);
		$file->setMimeType('application/vnd.google-apps.document');

		$parent = new \Google_Service_Drive_ParentReference();
		$parent->setId(self::SHARED_FOLDER_ID);
		$file->setParents([$parent]);
		
		$createdFile = $service->files->insert($file, [
			'uploadType' => 'media',
			'data' => $content,
			'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		]);
		
		$result = [
			'id' => $createdFile->getId(),
			'title' => $document->name,
			'document_id' => $documentId,
		];		
		return je($result);
	}
	
	/**
	 * Создание папки с документами, а также добавление файлов в папку
	 * @param int $id ID договора
	 * @param int $documentId ID документа
	 */
	public function actionCreateFolder($id, $documentId = null) {
		$contract = Contract::findOne(['id' => $id]);
		$document = (!is_null($documentId)) ? Document::findOne($documentId) : new Document;
		$folderDocumentForm = new FolderDocumentForm([], $document, $contract);
		if (!is_null($documentId)) {$folderDocumentForm->append = true;}
		if (request()->isGet) {
			return $this->renderAjax('create_folder', ['folderDocumentForm' => $folderDocumentForm]);
		}
		if (request()->isPost) {
			$folderDocumentForm->load(request()->post());
			$folderDocumentForm->files = UploadedFile::getInstances($folderDocumentForm, 'files');
		}
		$this->validateAndSaveForm($folderDocumentForm, []);
		
		# Создаем таску для Марго о создании/изменении папки
		TaskForm::createMargoTaskOnDocument($folderDocumentForm->getDocument(), is_null($documentId));
		return [];
	}
	
	/**
	 * Открывает PDF-файл в новой вкладке
	 * @param int $fileId ID файла
	 */
	public function actionOpenPortableFile($fileId) {
		$file = File::findOne($fileId);
		if (file_exists($file->getFullPath())) {
			$content = file_get_contents($file->getFullPath());
			header("Content-type: application/pdf");
		echo $content;
		}
	}
	
	/**
	 * Открывает PDF-документ в новой вкладке
	 * @param int $documentId ID документа
	 */
	public function actionOpenPortableDocument($documentId) {
		$document = Document::findOne($documentId);
		if (file_exists($document->createContractFolder().DS.$document->id.'.pdf')) {
			$content = file_get_contents($document->createContractFolder().DS.$document->id.'.pdf');
			header("Content-type: application/pdf");
		echo $content;
		}
	}
	
	/**
	 * Загружает файлы с сервера
	 * @param int $fileId ID файла
	 */
	public function actionDownloadFile($fileId) {
		$file = File::findOne($fileId);
		if (file_exists($file->getFullPath())) {
			$content = file_get_contents($file->getFullPath());
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($file->getFullPath()).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize($file->getFullPath()));
			echo $content;
		}
	}
	/**
	 * Загружает документ с сервера
	 * @param int $documentId ID документа
	 */
	public function actionDownloadDocument($documentId) {
		$document = Document::findOne($documentId);
		$extension = ($document->type === Document::TYPE_DOCUMENT) ? 'docx' : 'pdf';
		if (file_exists($document->createContractFolder().DS.$document->id.'.'.$extension)) {
			$content = file_get_contents($document->createContractFolder().DS.$document->id.'.'.$extension);
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($document->createContractFolder().DS.$document->id.'.'.$extension).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize($document->createContractFolder().DS.$document->id.'.'.$extension));
			echo $content;
		}
	}
	
	/**
	 * Создает zip-архив папки, передает его для скачивания, затем удаляет
	 * @param int $documentId ID документа-папки
	 * @throws \Exception в случае отказа при создании архива
	 */
	public function actionZipAndDownload($documentId) {		
		$document = Document::findOne($documentId);
		$zip = new \ZipArchive();
		if ($zip->open($document->getPath('').DS.$documentId.".zip", \ZipArchive::CREATE) !== true) {
			throw new \Exception('Не удалось создать zip-архив');
		}
		$folder = $document->getPath('');
		if ($dirstream = @opendir($folder)) {
			while (false !== ($filename = readdir($dirstream))) {
				if ($filename != "." && $filename != ".." && $filename != $documentId.".zip") {
					if (is_file($folder."/".$filename))
						$zip->addFile($folder."/".$filename, $filename);
				}
			}
		}
		@closedir($dirstream);
		$zip->close();
		if (file_exists($document->getPath('').DS.$documentId.".zip")) {
			$content = file_get_contents($document->getPath('').DS.$documentId.".zip");
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($document->getPath('').DS.$documentId.".zip").'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize($document->getPath('').DS.$documentId.".zip"));
			echo $content;
		}
		unlink($document->getPath('').DS.$documentId.".zip");
	}
	
	/**
	 * Загружает PDF-документ на сервер
	 * @param int $id ID договора
	 */
	public function actionUploadPortableDocument($id) {
		$contract = Contract::findOne($id);
		$portableDocumentForm = new PortableDocumentForm([], new Document, $contract);
		if (request()->isGet) {
			return $this->renderAjax('upload_pdf', ['portableDocumentForm' => $portableDocumentForm]);
		}
		if (request()->isPost) {
			$portableDocumentForm->load(request()->post());
			$portableDocumentForm->file = UploadedFile::getInstance($portableDocumentForm, 'file');
		}
		$this->validateAndSaveForm($portableDocumentForm, []);

		# Создаем таску для Марго о создании/изменении папки
		TaskForm::createMargoTaskOnDocument($portableDocumentForm->getDocument());
		return [];
	}
	
	/**
	 * Открытие папки
	 * @param int $documentId ID документа
	 */
	public function actionOpenFolder($documentId) {
		$document = Document::findOne(['id' => $documentId]);
		$documentFiles = $document->files;
		$folderDocumentForm = new FolderDocumentForm([], $document, $document->contract);
		return $this->renderAjax('open-folder', ['documentFiles' => $documentFiles, 'folderDocumentForm' => $folderDocumentForm]);
	}
	
	/**
	 * Удаляет файл из Google Drive в случае отмены создания документа
	 * @param type $id ID файла в Google Drive
	 */
	public function actionCancel($id) {
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		$service->files->delete($id);
	}
	
	/**
	 * Экшн для редактирования только что созданного документа
	 * @param int $id ID созданного файла в Google Drive
	 * @param int $contractId ID договора
	 */
	public function actionNewDocument($id, $contractId) {
		return $this->renderAjax('new_document', ['fileId' => $id, 'contractId' => $contractId]);
	}
	
	/**
	 * Экшн для редактирования уже имеющегося документа
	 * @param int $id ID созданного файла в Google Drive
	 * @param int $contractId ID договора
	 * @param int $documentId ID документа
	 */
	public function actionEditDocument($id, $contractId, $documentId) {
		return $this->renderAjax('edit_document', ['fileId' => $id, 'contractId' => $contractId, 'documentId' => $documentId]);
	}
	
	/**
	 * Возвращает уменьшенный вариант изображения
	 * @param int $imageId ID файла
	 */
	public function actionGetImageThumb($imageId) {
		$file = File::findOne($imageId);
		$image = \Yii::$app->image->load($file->getFullPath());
		$image->resize(120, 120);
		header("Content-Type: image/".$file->ext);
		echo $image->render();
	}	
	
	/**
	 * Возвращает изображение
	 * @param int $imageId ID файла
	 */
	public function actionGetImage($imageId) {
		$file = File::findOne($imageId);
		$image = \Yii::$app->image->load($file->getFullPath());
		header("Content-Type: image/".$file->ext);
		echo $image->render();
	}
	
	/**
	 * Возвращает клиента Google API
	 * @param type $code Код верификации, необходимый для авторизации
	 * @return \GoogleAPIClient
	 */
	public function getClient($code = null) {
		$googleAPIClient = new GoogleAPIClient();
		return $googleAPIClient->getClient($code);
	}
	
	/**
	 * Возвращает путь для сохранения файла с Google Drive
	 * @param \Google_Service_Drive_DriveFile $file Google Drive файл
	 * @return string Путь к файлу в файловой системе
	 */
	public function getDownloadPath($file) {
		return mb_convert_encoding($file->getTitle(), 'CP1251', mb_detect_encoding($file->getTitle()));
	}
	
	/**
	 * Создает папку документов для договора, если ее нет и возвращает путь к ней
	 * @param int $contractId ID договора
	 * @return string Путь к созданной папке для сохранения документа
	 */
	public function createFolder($contractId) {
		$fileDir = Document::UPLOADS_FOLDER.$contractId;
		if (!file_exists($fileDir)) {
			mkdir($fileDir, 0775);
		}
		return $fileDir;
	}

}
