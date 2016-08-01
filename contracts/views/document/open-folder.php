<?php

/**
 * Представление для отображения содержимого папки документов
 * @var common/models/File[] $documentFiles - массив табличных данных о файлах, 
 * принадлежащих данному договору
 * @var crm\modules\contracts\models\FolderDocumentForm $folderDocumentForm - форма для работы с папкой
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>
<div class="row" style="margin: 0;">
	<div class="col-sm-12">
		<?php $form = ActiveForm::begin(['id' => 'folderDocumentForm', 'action' => '/contracts/document/create-folder/'.$folderDocumentForm->getContract()->primaryKey.'/'.$folderDocumentForm->getDocument()->id, 'enableClientValidation' => true, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-document_id' => $folderDocumentForm->getDocument()->id, 'data-id' => $folderDocumentForm->getContract()->primaryKey, 'enctype' => 'multipart/form-data']]); ?>
		<div>
			<div class="">
				<?= $form->field($folderDocumentForm, 'name', ['enableLabel' => false])->hiddenInput(); ?>
			</div>
			<div class="col-sm-6">
				<?= $form->field($folderDocumentForm, 'files[]')->fileInput(['multiple' => true])->label(false); ?>
			</div>
			<div class="col-sm-6">
				<?= Html::submitButton('Добавить файлы', ['class' => 'btn btn-block btn-sm btn-primary']); ?>
			</div>
		</div>		
		<?php ActiveForm::end(); ?>
	</div>
</div>
<div class="col-sm-12">
	<div class="document-folder-content-js">
		<?php foreach ($documentFiles as $file) : ?>		
			<div class="col-md-3 col-xs-4 file-shortcut">
				<?php if ($file->ext === 'jpg' || $file->ext === 'jpeg' || $file->ext === 'png' || $file->ext === 'gif') : ?>
				<a class="fancybox fancybox.ajax" data-fancybox-type="image" rel="group" href="/contracts/documents/get-image/<?=$file->id;?>" title='<a class="btn btn-sm btn-info" href="/contracts/documents/download-file/<?=$file->id;?>">Скачать</a><a class="btn btn-sm btn-success" style="float:right;" target="_blank" href="/contracts/documents/get-image/<?=$file->id;?>">Открыть в новой вкладке</a>'>
						<div class="piece-of-sheet" title="<?= $file->desc.'.'.$file->ext; ?>">
							<table>
								<tr class="thumb">
									<td>
										<img src="/contracts/documents/get-image-thumb/<?=$file->id;?>" />
									</td>
								</tr>
								<tr class="title"><td><h4><?= $file->desc.'.'.$file->ext; ?></h4></td></tr>
							</table>
						</div>
					</a>
				<?php else : ?>
				<?php $action = ($file->ext === 'pdf') ? '/contracts/documents/open-portable-file/'.$file->id : '/contracts/documents/download-file/'.$file->id ?>
				<a href="<?= $action; ?>" target='_blank'>
					<div class="piece-of-sheet" title="Скачать <?= $file->desc.'.'.$file->ext; ?>">
						<table>
							<tr class="thumb">
								<td>
									<div class="document-icon document-icon-mini document-icon-<?=$file->ext;?>"></div>
								</td>
							</tr>
							<tr class="title"><td><h4><?= $file->desc.'.'.$file->ext; ?></h4></td></tr>
						</table>
					</div>
				</a>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>	
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".fancybox").fancybox({
			helpers : {
				title : {
					type : 'outside'
				}
			}
		});
	});
</script>