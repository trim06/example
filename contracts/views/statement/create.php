<?php
/**
 * This is the statement view
 * 
 * @var $listStatement Список заявлений
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="col-md-12" style="margin-top: 20px;">
	<div class="piece-of-sheet">
		<?php $form = ActiveForm::begin(['id' => 'statementForm', 'action' => '/contracts/statement/create/' . $statementForm->getContract()->primaryKey, /*'enableClientValidation' => FALSE,*/ 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $statementForm->getContract()->primaryKey, 'enctype' => 'multipart/form-data']]); ?>
		<div>
			<div class="row">
				<div class="col-sm-12">
					<?= $form->field($statementForm, 'text', ['enableLabel' => false])->textarea(['placeholder' => $statementForm->getAttributeLabel('text'), 'rows' => 10]); ?>
				</div>
				<div class="col-sm-12">
					<?= $form->field($statementForm, 'file[]')->fileInput(['multiple' => true])->label(false); ?>
				</div>
			</div>
		</div>
		<div class="inner-btn">
			<?= Html::submitButton('Сохранить заявление', ['class' => 'btn btn-block btn-sm btn-primary']); ?>
		</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>

