<?php

/* Список документов */

/**
 * @var $this \yii\web\View
 * @var $contractFile \crm\models\File
 * @var int $contractId
 */

use common\models\Document;
?>
<div class="drive-loader">
	<h3>Подождите, идет загрузка...</h3>
</div>
<div class="col-md-12 document-action-list piece-of-sheet" data-contract_id="<?=$contractId;?>">
	<div class="document-action col-md-4 col-xs-4 aside-ajax" title="Создать папку с изображениями" aside-url="/contracts/document/create-folder/<?=$contractId;?>" aside-title="Новая папка с документами по договору №<?=$contractId;?>" data-type="folder">
		<div class="document-icon document-icon-folder"></div>
		<h4>Новая папка</h4>
	</div>
	<div class="document-action col-md-4 col-xs-4 aside-ajax" aside-url="/contracts/document/pattern-select?contractId=<?=$contractId;?>" aside-title="Выберите шаблон">
		<div class="document-icon document-icon-document"></div>
		<h4>Новый документ</h4>
	</div>
	<div class="document-action col-md-4 col-xs-4 aside-ajax" title="Загрузить PDF файл" aside-url="/contracts/document/upload-portable-document/<?=$contractId;?>" aside-title="Новый PFD-документ по договору №<?=$contractId;?>" data-type="pdf">
		<div class="document-icon document-icon-pdf"></div>
		<h4>Загрузить PDF</h4>
	</div>
</div>
<div class="col-md-12 documents-list item-list document-tab-list" id="aside-items-container-js">
	<?php foreach ($listDocuments as $document) : ?>
		<?php if ($document->type === Document::TYPE_DOCUMENT) : ?>
			<div class="item-in-list document-item" data-document_id="<?=$document->id; ?>">
				<div class="row">
					<div class="col-xs-2">
						<a href="" onclick="main.documents.showDriveLoader(); main.documents.openDocument(this); return false;"><div class="document-icon document-icon-mini document-icon-document"></div></a>
					</div>
					<div class="col-xs-10">
						<a href="" onclick="main.documents.showDriveLoader(); main.documents.openDocument(this); return false;"><h4><?= $document->name; ?></h4></a>
						<p>Создал<?= ($document->user->gender === 'female') ? 'a' : '' ;?>: <?=$document->user->name; ?> <?= FormatText::rusDate($document->date) ?></p>
						<?php /* Выводим размер файла */ ?>
						<?php if ($fileSize = $document->getSize('docx') != 0):?>
							<p>Размер: <?= $document->getSize('docx'); ?>  Кб</p>
						<?php else: ?>
							<p>Файл не найден</p>
						<?php endif; ?>
						<div class="col-xs-4 document-btn">
							<a href="/contracts/documents/download-document/<?= $document->id; ?>" download class="btn btn-block btn-sm btn-success">Скачать</a>
						</div>
					</div>
					<span></span>
				</div>				
			</div>
		<?php elseif ($document->type === Document::TYPE_FOLDER) : ?>
			<div class="item-in-list document-item" data-document_id="<?= $document->id; ?>">
				<div class="row">
					<div class="col-xs-2">
						<a href="" onclick="main.documents.openFolder(this); return false;"><div class="document-icon document-icon-mini document-icon-folder"></div></a>
					</div>
					<div class="col-xs-10">
						<a href="" onclick="main.documents.openFolder(this); return false;"><h4 class="document-item-name"><?= $document->name; ?></h4></a>
						<p>Создал<?= ($document->user->gender === 'female') ? 'a' : '' ;?>: <?= $document->user->name; ?> <?= FormatText::rusDate($document->date) ?></p>
						<?php /* Выводим размер папки */ ?>
						<p>Размер: <?= $document->getFolderSize(); ?>  Кб </br>Содержит <?= $document->file_count.' '.rus_plural($document->file_count, ['файл', 'файла', 'файлов']); ?></p>
						<div class="col-xs-4 document-btn">
							<a href="/contracts/document/zip-and-download?documentId=<?=$document->id;?>" download class="btn btn-block btn-sm btn-success">Скачать архив</a>
						</div>
					</div>
					<span></span>
				</div>				
			</div>
		<?php elseif ($document->type === Document::TYPE_PDF) : ?>
		<div class="item-in-list document-item" data-document_id="<?=$document->id; ?>">
			<div class="row">
				<div class="col-xs-2">
					<a href="/contracts/documents/open-portable-document/<?=$document->id;?>" target='_blank'><div class="document-icon document-icon-mini document-icon-pdf"></div></a>
				</div>
				<div class="col-xs-10">
					<a href="/contracts/documents/open-portable-document/<?=$document->id;?>" target='_blank'><h4 class="document-item-name"><?= $document->name; ?></h4></a>
					<p>Создал<?= ($document->user->gender === 'female') ? 'a' : '' ?>: <?= $document->user->name; ?> <?= FormatText::rusDate($document->date) ?></p>
					<?php /* Выводим размер файла */ ?>
					<?php if ($fileSize = $document->getSize('pdf') != 0): ?>
						<p>Размер: <?= $document->getSize('pdf'); ?>  Кб</p>
					<?php else: ?>
						<p>Файл не найден</p>
					<?php endif; ?>
					<div class="col-xs-4 document-btn">
						<a href="/contracts/documents/download-document/<?=$document->id;?>" download class="btn btn-block btn-sm btn-success">Скачать</a>
					</div>
				</div>
				<span></span>
			</div>				
		</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
