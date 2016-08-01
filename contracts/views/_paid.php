<?php

/** Форма оплаты 
 * @var $this \yii\web\View
 * @var $paidForm crm\modules\contracts\models\ContractPaymentForm */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use common\components\ListHelper;
?>

<div class="row" style="margin: 20px 0;">
	<div class="piece-of-sheet" style="margin: 0 20px;">
		<?php $form = ActiveForm::begin(['id' => 'meetForm', 'action' => '/contracts/paid/'.$paidForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $paidForm->getContract()->primaryKey]]); ?>
		<div>
			<div class="row">
				<div class="col-sm-12">
					<?=$form->field($paidForm, 'manager')->dropDownList(ListHelper::getUsersGroupedByUnits(), ['options' => [user()->id => ['selected ' => true]]]);?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<?php if (count($contractServices) === 1 && ($contractServices[0]->cost > $contractServices[0]->getPaymentsSum())) {
						$contractServiceId = $contractServices[0]->id;
						$dropDownOptions = ['options' =>[$contractServiceId => ['selected ' => true]]];
					} else {
						$dropDownOptions = [];
					} ?>
					<?= $form->field($paidForm, 'contract_service_id')->dropDownList([null => 'Прочее']+ArrayHelper::map($contractServices, 'id', function ($a) use ($dropDownOptions) {
						$result = $a->service->name;
						if (!is_null($a->credit)) {
							$result .= ' - '.$a->credit->creditor_name;
						}
						return $result.' (Остаток: '.($a->cost - $a->paymentsSum).' руб.)';					
					}), $dropDownOptions); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<?= $this->render('@app/modules/payment/views/_payment_types_field', ['form' => $form, 'model' => $paidForm, 'field' => 'paymentType']) ?>
				</div>
				<div class="col-xs-6">
					<?= $form->field($paidForm, 'cost')->textInput() ?>
				</div>
				<div class="col-xs-12">
					<?= $form->field($paidForm, 'comment')->textarea() ?>
				</div>
			</div>
		</div>
		<div class="inner-btn">
			<?= Html::submitButton('Внести оплату', ['data-user' => user()->id, 'class' => 'btn btn-block btn-sm btn-primary', 'title' => $paidForm->getContract()->abonent->name.' №'.$paidForm->getContract()->id, 'onclick' => 'return contracts.savePayment(this);']); ?>
		</div>
		<?php ActiveForm::end(); ?>
	</div>		
</div>