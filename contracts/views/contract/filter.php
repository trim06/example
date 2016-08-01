<?php

/** Фильтр договоров */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Contract;
use common\models\Unit;
use common\models\Task;
use common\models\ServiceCategory;
use crm\widgets\userSearch2\UserSearch2Widget;

?>

<h5>Поиск</h5>
<?php $contractFilterForm = ActiveForm::begin(['id' => 'сontractFilterForm', 'action' => '/contracts', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
<?= Html::activeHiddenInput($filter, 'page', ['route_key' => 'p']); ?>
<?= $contractFilterForm->field($filter, 'id', ['enableLabel' => FALSE])->textInput(['route_key' => 'i', 'placeholder' => 'Номер договора']); ?>
<?= $contractFilterForm->field($filter, 'name', ['enableLabel' => FALSE])->textInput(['route_key' => 'n', 'placeholder' => $filter->getAttributeLabel('name')])->iconPrepend('fa-user'); ?>
<?= $contractFilterForm->field($filter, 'phone', ['enableLabel' => FALSE])->textInput(['route_key' => 'ph', 'placeholder' => $filter->getAttributeLabel('phone')])->iconPrepend('+7'); ?>
<?= $contractFilterForm->field($filter, 'status', ['enableLabel' => FALSE])->dropDownList([NULL => 'Статус договора'] + Contract::getStatusList(), ['route_key' => 's', 'onchange' => 'contracts.toggleServiceDDList(this);']); ?>
<?= $contractFilterForm->field($filter, 'service', ['enableLabel' => FALSE])->dropDownList([NULL => 'Подключенные услуги'] + ServiceCategory::getList(), ['route_key' => 'e', 'style' => "display:none"]) ?>
<?= $contractFilterForm->field($filter, 'taskType', ['enableLabel' => FALSE])->dropDownList([NULL => 'Тип задач'] + Task::getTaskList(), ['route_key' => 't']); ?>
<?=
UserSearch2Widget::widget([
	'model' => $filter,
	'name' => 'manager',
	'id' => 'contractfilterform-manager',	
	'className' => Unit::className(),
	'relations' => 'accessedUsers',
	'modelCriteria' => ['byUser'],
	'prompts' => ['Выберите офис', 'Выберите сотрудника'],
	'route_key' => 'm',
	'mode' => UserSearch2Widget::MODE_ALL,
]);
?>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return contracts.filter.apply();']); ?></div>
<?php ActiveForm::end(); ?>