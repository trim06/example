<?php

/** Фильтр пользователей */

use common\models\ContractStatement;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>

<h5 style="margin-bottom:15px;">Поиск</h5>
<?php $statementFilterForm = ActiveForm::begin(['id' => 'statementFilterForm', 'action' => '/contracts/statement', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
<?= Html::activeHiddenInput($filter, 'page', ['route_key' => 'p']); ?>
<?= $statementFilterForm->field($filter, 'contract_id')->textInput(['route_key' => 'c']); ?>
<?= $statementFilterForm->field($filter, 'name')->textInput(['route_key' => 'n'])->iconPrepend('fa-user'); ?>
<?= $statementFilterForm->field($filter, 'phone')->textInput(['route_key' => 'ph'])->iconPrepend('+7'); ?>
<?= $statementFilterForm->field($filter, 'status')->dropDownList([NULL => 'Все заявления'] + ContractStatement::getStatusList(), ['route_key' => 's']); ?>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return statement.filter.apply();']); ?></div>
<?php ActiveForm::end(); ?>