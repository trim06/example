<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>


<?php $form = ActiveForm::begin(['enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $contractCreditForm->getContract()->primaryKey]]); ?>
<div style="margin: 20px 0;">
	<div class="abonent-credit-list col-xs-7 col-sm-9">
		<div class="abonent-credit js_form_credit piece-of-sheet">
			<div class="abonent-credit-data">
				<?= $form->field($contractCreditForm, 'creditor_name')->textInput()->iconPrepend('fa-building-o'); ?>
				<?= $form->field($contractCreditForm, 'loan_agreement')->textInput()->iconPrepend('fa-file-text-o'); ?>
				<?= $form->field($contractCreditForm, 'debt_amount')->textInput()->iconPrepend('fa-money'); ?>
				<?= $form->field($contractCreditForm, 'month_payment')->textInput()->iconPrepend('fa-money'); ?>
			</div>
		</div>
	</div>
	<div class="col-xs-5 col-sm-3 list-actions">
		<div class="top-actions">
			<?= Html::submitButton('Сохранить', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.saveCredit(this);']); ?>
		</div>
	</div>
</div>
<?php $form->end(); ?>