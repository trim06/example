<?php

/** Фильтр пользователей */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Task;
use common\models\UserRole;
use crm\widgets\userSearch2\UserSearch2Widget;
use common\models\Unit;


?>

<h5>Поиск</h5>
<?php $calendarFilterForm = ActiveForm::begin(['id' => 'calendarFilterForm', 'action' => '/contracts/calendar/month', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
<?= Html::activeHiddenInput($filter, 'page', ['route_key' => 'p']); ?>

		<?//=
		$calendarFilterForm->field($filter, 'date_from', ['enableLabel' => FALSE])->widget(\kartik\date\DatePicker::classname(), [
			'options' => ['placeholder' => 'c', 'route_key' => 'df'],
			'pluginOptions' => [
				'format' => 'yyyy.mm.dd',
				'orientation' => 'top',
				'autoclose' => TRUE
			]
		])
		?>
		<?//=
		$calendarFilterForm->field($filter, 'date_to', ['enableLabel' => FALSE])->widget(\kartik\date\DatePicker::classname(), [
			'options' => ['placeholder' => 'по', 'route_key' => 'dt'],
			'pluginOptions' => [
				'format' => 'yyyy.mm.dd',
				'orientation' => 'top',
				'autoclose' => TRUE
			]
		])
		?>
<?//= \crm\widgets\userSearch\UserSearchWidget::widget(['model' => $filter, 'attribute' => 'manager', 'prompt' => 'Назначил', 'options' => ['route_key' => 'a']]); ?>
				<?=
		UserSearch2Widget::widget([
			'model' => $filter,
			'name' => 'executor_id',
			'id' => 'calendarfilterform-executor_id',
			'className' => Unit::className(),
			'relations' => 'accessedUsers',
			'modelCriteria' => ['byUser'],
			'prompts' => ['Выберите офис', 'Выберите сотрудника'],
			'route_key' => 'e',
			'mode' => UserSearch2Widget::MODE_ALL,
		]);
		?>
<?//= \crm\widgets\userSearch\UserSearchWidget::widget(['model' => $filter, 'attribute' => 'executor_id', 'prompt' => 'Исполнитель', 'options' => ['route_key' => 'e']]); ?>
<?//= $calendarFilterForm->field($filter, 'author_id', ['enableLabel' => FALSE])->dropDownList([NULL => $filter->getAttributeLabel('author_id')] + ArrayHelper::map(User::find()->all(), 'id', 'name'), ['route_key' => 'a']); ?>
<?//= $calendarFilterForm->field($filter, 'executor_id', ['enableLabel' => FALSE])->dropDownList([NULL => $filter->getAttributeLabel('executor_id')] + ArrayHelper::map(User::find()->all(), 'id', 'name'), ['route_key' => 'e']); ?>
<?= $calendarFilterForm->field($filter, 'task', ['enableLabel' => FALSE])->dropDownList([NULL => $filter->getAttributeLabel('task')] + Task::getTaskList(), ['route_key' => 't']); ?>
<?= $calendarFilterForm->field($filter, 'status', ['enableLabel' => FALSE])->dropDownList(Task::getStatusList(), ['route_key' => 's', 'prompt' => $filter->getAttributeLabel('status')]); ?>
<?= $calendarFilterForm->field($filter, 'date')->hiddenInput(['route_key' =>'date'])->label(FALSE); ?>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return calendar.filter.apply();']); ?></div>
<?php ActiveForm::end(); ?>