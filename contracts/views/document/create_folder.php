<?php

/**
 * Форма создания новых папок с документами
 * 
 * @var $listStatement Список заявлений
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="col-md-12" style="margin-top: 20px;">
		<?php $form = ActiveForm::begin(['id' => 'folderDocumentForm', 'action' => '/contracts/document/create-folder/'.$folderDocumentForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $folderDocumentForm->getContract()->primaryKey, 'enctype' => 'multipart/form-data']]); ?>
		<div>
			<div class="row">
				<div class="col-sm-12">
					<?= $form->field($folderDocumentForm, 'name', ['enableLabel' => false])->textInput(['placeholder' => 'Название папки с документами']); ?>
				</div>
				<div class="col-sm-12">
					<?= $form->field($folderDocumentForm, 'files[]')->fileInput(['multiple' => true])->label(false); ?>
				</div>
			</div>
		</div>
		<div class="inner-btn">
			<?= Html::submitButton('Загрузить файлы', ['class' => 'btn btn-block btn-sm btn-primary']); ?>
		</div>
		<?php ActiveForm::end(); ?>
</div>