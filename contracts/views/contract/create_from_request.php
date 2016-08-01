<?php

/** Форма создания контракта */
/* @var $this \yii\web\View */
/* @var $userForm crm\modules\users\models\UserForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="row" style="margin: 20px 0;">
<?php $form = ActiveForm::begin(['id' => 'contractForm', 'action' => '/contracts/create-from-request/'.$request->id, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form']]); ?>
	<div class="col-xs-7 col-sm-9">
		<div class="piece-of-sheet">
			<?= $this->render('_form', ['form' => $form, 'contractForm' => $contractForm]); ?>
		</div>
	</div>
	<div class="col-xs-5 col-sm-3 list-actions">
		<div class="top-actions">
		<?php //= Html::submitButton('Сохранить', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
		<?= Html::submitButton('Встреча', ['data-action' => 'task-meet', 'title' => 'Назначить встречу', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
		<?= Html::submitButton('Перезвонить', ['data-action' => 'task-call', 'title' => 'Перезвонить', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
		<?= Html::submitButton('Оплата', ['data-action' => 'paid', 'title' => 'Оплата', 'class' => 'btn btn-block btn-sm btn-success', 'onclick' => 'return contracts.action(this);']); ?>
		</div>
	</div>
</div>