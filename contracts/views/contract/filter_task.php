<?php

/** Фильтр пользователей */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use yii\helpers\ArrayHelper;

$listActiveUsers = \yii\helpers\ArrayHelper::map(User::findAll(['status' => User::STATUS_ACTIVE]), 'id', 'name');
?>

<h5>Поиск</h5>
<div class="row">
<?php $taskFilterForm = ActiveForm::begin(['id' => 'taskFilterForm', 'action' => '/contracts/task', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
	<div class="col-xs-12 col-sm-6">
	<?= $taskFilterForm->field($filter, 'date_from')->widget(\kartik\date\DatePicker::classname(), [
		'options' => ['placeholder' => 'c', 'route_key' => 'df'],
		'pluginOptions' => [
			'orientation' => 'top',
			'autoclose' => TRUE
		]
	]) ?>
	</div>
	<div class="col-xs-12 col-sm-6">
	<?= $taskFilterForm->field($filter, 'date_to')->widget(\kartik\date\DatePicker::classname(), [
		'options' => ['placeholder' => 'по', 'route_key' => 'dt'],
		'pluginOptions' => [
			'orientation' => 'top',
			'autoclose' => TRUE
		]
	]) ?>
	</div>
	<div class="col-xs-12">
		<?= $taskFilterForm->field($filter, 'manager', ['enableLabel' => FALSE])->dropDownList([NULL => 'Менеджер'] + ArrayHelper::map($this->context->getListUsers(), 'id', 'name'), ['route_key' => 'm']); ?>
	</div>
</div>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return contracts.taskFilter.apply();']); ?></div>
<?php ActiveForm::end(); ?>