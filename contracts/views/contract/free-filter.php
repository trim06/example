<?php

/** Фильтр пользователей */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<h5>Поиск</h5>
<?php $ContractFilterForm = ActiveForm::begin(['id' => 'freeContractFilterForm', 'action' => '/contracts/free', 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'autocomplete' => 'off']]); ?>
<?= Html::activeHiddenInput($filter, 'page', ['route_key' => 'p']); ?>
<?= $ContractFilterForm->field($filter, 'id', ['enableLabel' => FALSE])->textInput(['route_key' => 'i', 'placeholder' => 'Номер договора']); ?>
<?= $ContractFilterForm->field($filter, 'name', ['enableLabel' => FALSE])->textInput(['route_key' => 'n', 'placeholder' => $filter->getAttributeLabel('name')])->iconPrepend('fa-user'); ?>
<?= $ContractFilterForm->field($filter, 'phone', ['enableLabel' => FALSE])->textInput(['route_key' => 'ph', 'placeholder' => $filter->getAttributeLabel('phone')])->iconPrepend('+7'); ?>
<div class="text-right"><?= Html::submitButton('Найти', ['class' => 'btn btn-sm btn-primary', 'onclick' => 'return contracts.freeFilter.apply();']); ?></div>
<?php ActiveForm::end(); ?>