<?php

/** Форма назначения встречи */
/* @var $this \yii\web\View */
/* @var $taskForm \crm\modules\contracts\models\TaskForm */

use yii\helpers\Html;
use common\components\ListHelper;
use yii\bootstrap\ActiveForm;
?>

<div class="row" style="margin: 20px 0;">
	<?php $form = ActiveForm::begin(['id' => 'meetForm', 'action' => rtrim('/contracts/task-create-doc/'.$taskForm->getContract()->primaryKey.'/'.$taskForm->getTask()->primaryKey, '/'), 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $taskForm->getContract()->primaryKey]]); ?>
	<div class="col-xs-9 col-sm-9">
		<div class="piece-of-sheet">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<?= $this->render('_task_datetimepicker', ['form' => $form, 'taskForm' => $taskForm]) ?>
				</div>
				<div class="col-xs-12 col-sm-6">
				<?//= $form->field($taskForm, 'executor_id')->dropDownList(ArrayHelper::map($this->context->getListUnitUsers(), 'id', 'name')); ?>
				<?= $form->field($taskForm, 'executor_id')->dropDownList(ListHelper::getUsersGroupedByUnits()); ?>
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
			<?php if ($taskForm->getTask()->isNewRecord): ?>
				<?= Html::submitButton('Создать задачу', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
			<?php elseif ($taskForm->getTask()->status === common\models\Task::STATUS_DONE || $taskForm->getTask()->status === common\models\Task::STATUS_CANCEL) : ?>
			<?php else: ?>
				<?php foreach ($taskForm->buttons($taskForm) as $button) : ?>
					<?= Html::submitButton($button['label'], $button['attributes']); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>		
		<?php if (!$taskForm->getTask()->isNewRecord): ?>
			<?= Html::button('Карточка договора', ['class' => 'btn btn-block btn-sm btn-info aside-ajax', 'aside-url' => ("/contracts/card/" . $taskForm->contract->id), 'aside-title' => $taskForm->contract->abonent->name . ' №' . $taskForm->contract->id, 'aside-width' => 700]); ?>	
		<?php endif; ?>
	</div>
	<?php ActiveForm::end(); ?>
</div>

<div>
	<?= $this->render('contract/history', ['listHistory' => $history]); ?>
</div>