<?php

/** Фильтр заявок */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use common\models\Request;
use common\models\City;
use common\models\Unit;

# формирование списка городов
if (user()->identity->role_id == 1) {
	$unitsIds = ArrayHelper::getColumn(Unit::find()->all(), 'city_id');
	$cities = [NULL => 'Все города'];
} else {
	$unitsIds = ArrayHelper::getColumn(user()->identity->units, 'city_id');
	$cities = [];
}
$cities += ArrayHelper::map(City::find()->andWhere(['in', 'city_id', $unitsIds])->all(), 'city_id', 'name');
?>


<h5 style="margin-bottom: 15px;">Поиск</h5>
<?php $requestFilterForm = ActiveForm::begin(['id' => 'requestFilterForm', 'action' => '/contracts/request', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
<?= Html::activeHiddenInput($filter, 'page', ['route_key' => 'p']); ?>
<?= $requestFilterForm->field($filter, 'name')->textInput(['route_key' => 'n'])->iconPrepend('fa-user'); ?>
<?= $requestFilterForm->field($filter, 'phone')->textInput(['route_key' => 'ph'])->iconPrepend('+7'); ?>
<?= $requestFilterForm->field($filter, 'status')->dropDownList(Request::getStatusList(), ['route_key' => 's']); ?>
<?php if (count($cities) > 1) : ?>
<?= $requestFilterForm->field($filter, 'city_id')->dropDownList($cities, ['route_key' => 'c']); ?>
<?php endif; ?>
<?= $requestFilterForm->field($filter, 'source')->dropDownList([null => 'Из всех источников'] + Request::getSourceList(), ['route_key' => 'u', 'onchange' => 'request.toggleSendTypeField(this);']); ?>
<?= $requestFilterForm->field($filter, 'sendType')->dropDownList([null => 'Все заявки'] + Request::getSendTypeList(), ['route_key' => 't']); ?>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return request.filter.apply();']); ?></div>
<?php ActiveForm::end(); ?>