<?php

/** Форма назначения встречи */
/* @var $this \yii\web\View */
/* @var $taskForm \crm\modules\contracts\models\TaskForm */
/* @var $history common\models\ContractLog[] */

use yii\helpers\Html;
use common\components\ListHelper;
use yii\bootstrap\ActiveForm;
use common\models\Unit;
?>

<div class="row" style="margin: 20px 0;">
	<?php $form = ActiveForm::begin(['id' => 'meetForm', 'action' => rtrim('/contracts/task-meet/'.$taskForm->getContract()->primaryKey.'/'.$taskForm->getTask()->primaryKey, '/'), 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $taskForm->getContract()->primaryKey]]); ?>
	<div class="col-xs-9 col-sm-9">
		<div class="piece-of-sheet">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<?= $this->render('_task_datetimepicker', ['form' => $form, 'taskForm' => $taskForm, 'htmlOptions' => [
						'options' => ['data-date' => $taskForm->start_at ? date('Y-m-d\TH:i+0000', strtotime($taskForm->start_at)) : null],
						'pluginEvents' => [
							'changeDate' => "function(e) {
								var utcDate = main.helper.date.getObjUTCDate(e.date);
								$('input[name*=\"start_at\"]').data(\"date\", utcDate.format('yyyy-mm-dd') + 'T' + utcDate.format('HH:MM') + '+0000');
								contracts.setSmsText();
							}",
						],
					], 'setInterval' => 4,]) ?>
				</div>
				<div class="col-xs-12 col-sm-6">
					<?php /* Объект офиса для аттрибутов данных */ ?>
					<?php $unit = Unit::find()->currentByUser()->one(); $workPhone = user()->identity->userSettings->work_phone; ?>
					<?= $form->field($taskForm, 'executor_id')->dropDownList(ListHelper::getUsersGroupedByUnits(), ['onchange' => 'contracts.setSmsText();', 'data-address' => $unit->address, 'data-phone' => ((is_null($workPhone) || $workPhone === '') ? $unit->phone : $workPhone)]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<?= $form->field($taskForm, 'sendSms')->checkbox(['label' => 'Отправить СМС', 'labelOptions' => ['class' => 'checkbox-inline'], 'onchange' => 'contracts.setSmsText(); contracts.enableSmsTextInput(this);'], true); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<?= $form->field($taskForm, 'smsText')->textarea([
						'class' => 'form-control smsText-js',
						'style' => 'display:none;',
						'data-pattern' => user()->identity->userSettings->sms_pattern,
					])->label(false); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<?= $form->field($taskForm, 'comment')->textarea(['value' => '']) ?>
				</div>
			</div>
			<?= $form->field($taskForm, 'action_id')->hiddenInput(['class' => 'form-control action_id-js'])->label(false); ?>
			<?= $form->field($taskForm, 'status')->hiddenInput(['class' => 'form-control status-js'])->label(false); ?>
		</div>
	</div>
	<div class="col-xs-3 list-actions">
		<div class="top-actions">
			<?php if($taskForm->getTask()->isNewRecord): ?>
				<?= Html::submitButton('Назначить встречу', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
			<?php elseif ($taskForm->getTask()->status === common\models\Task::STATUS_DONE || $taskForm->getTask()->status === common\models\Task::STATUS_CANCEL) : ?>
			<?php else: ?>
				<?php foreach ($taskForm->buttons($taskForm) as $button) : ?>
					<?= Html::submitButton($button['label'], $button['attributes']); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php if (!$taskForm->getTask()->isNewRecord): ?>
			<div class="top-actions">
				<?= Html::button('Перезвонить', ['class' => 'btn btn-block btn-sm btn-primary aside-ajax', 'aside-url' => ("/contracts/task-call/" . $taskForm->contract->id), 'aside-title' => $taskForm->contract->abonent->name . ' ' . \FormatText::phone($taskForm->contract->abonent->phone), 'aside-width' => 700]); ?>
			</div>
			<?= Html::button('Карточка договора', ['class' => 'btn btn-block btn-sm btn-info aside-ajax', 'aside-url' => ("/contracts/card/".$taskForm->contract->id), 'aside-title' => $taskForm->contract->abonent->name . ' №' . $taskForm->contract->id, 'aside-width' => 700]); ?>	
		<?php endif; ?>			
	</div>
	<?php ActiveForm::end(); ?>
</div>

<div>
	<?= $this->render('contract/history', ['listHistory' => $history]); ?>
</div>

