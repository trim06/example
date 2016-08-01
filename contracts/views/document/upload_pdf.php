<?php

/**
 * Форма создания новых PDF-документов
 * 
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="col-md-12" style="margin-top: 20px;">
		<?php $form = ActiveForm::begin(['id' => 'portableDocumentForm', 'action' => '/contracts/document/upload-portable-document/'.$portableDocumentForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $portableDocumentForm->getContract()->primaryKey, 'enctype' => 'multipart/form-data']]); ?>
		<div>
			<div class="row">
				<div class="col-sm-12">
					<?= $form->field($portableDocumentForm, 'name', ['enableLabel' => false])->textInput(['placeholder' => 'Название документа', 'rows' => 10]); ?>
				</div>
				<div class="col-sm-12">
					<?= $form->field($portableDocumentForm, 'file')->fileInput(['accept' => 'application/pdf'])->label(false); ?>
				</div>
			</div>
		</div>
		<div class="inner-btn">
			<?= Html::submitButton('Загрузить файл', ['class' => 'btn btn-block btn-sm btn-primary']); ?>
		</div>
		<?php ActiveForm::end(); ?>
</div>