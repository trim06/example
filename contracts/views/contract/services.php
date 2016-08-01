<?php
/* Список услуг */

/* @var $this \yii\web\View */
/* @var $listContractService \crm\models\ContractService[] */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
?>

<?php /** -------- <Список услуг> -------- */ ?>
<div class="services-list piece-of-sheet js">
	<?php foreach ($listContractService as $contractService) : ?>
		<div class="service-item item-in-list" >
			<div class="pull-left">
				<h4 title="<?= $contractService->service->description; ?>"><?= $contractService->title; ?></h4>
				<div class="service-item-info">
					<?php $dayCount = (new \DateTime($contractService->date))->diff(new \DateTime())->days; ?>
					оформил <?= $contractService->user->name ?>
					<?= ($dayCount !== 0) ? $dayCount.' '.rus_plural($dayCount, ['день', 'дня', 'дней']).' назад' : (((new \DateTime($contractService->date))->format('d') == date('d')) ? 'сегодня' : 'вчера'); ?>
				</div>
			</div>

			<div class="service-item-payment pull-right">
				<?= ($contractService->paymentsSum ? $contractService->paymentsSum.' из ' : '').$contractService->cost.' руб.' ?>
			</div>			

			<div class="clearfix"></div>

			<div class="progress-bar-container progress-bar-container-js">
				<div class="payment-progress-bar payment-progress-bar-js" data-sum="<?= $contractService->paymentsSum; ?>" data-cost="<?= $contractService->cost; ?>"></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<?php /** -------- <Активация дополнительных услуг> -------- */ ?>
<div class="col-md-12 col-sm-12">
	<div class="row" style="margin: 20px 0;">
		<?php $form = ActiveForm::begin(['id' => 'meetForm', 'action' => '/contracts/agreement/'.$agreementForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $agreementForm->getContract()->primaryKey]]); ?>
		<div class="piece-of-sheet" style="padding-left: 0">
			<h4 style="padding-left: 16px; margin-bottom: 8px;"><strong>Активация дополнительных услуг</strong></h4>
			<div style="padding: 16px;">
				<div class="row">
					<div class="col-xs-12">
						<?=
						$form->field($agreementForm, 'serviceId')->checkboxList(
								ArrayHelper::map($listService, 'id', 'name'), ['item' => function ($index, $label, $name, $checked, $value) use ($listService, $agreementForm, $listContractCredit, $listContractService) {
								# если текущая услуга работает с кредитами и по договору не указан ни один кредит, то отключаем услугу
								$disableService = ($listService[$value]->work_with_credit === 'Y' && !count($agreementForm->getContract()->credit) > 0);
								$htmlCheckboxOptions = [
									'value' => $listService[$value]->work_with_credit === 'Y' ? null : $value,
									'label' => $label,
									$disableService ? 'disabled' : '' => 'disabled',
									'data-cost' => $listService[$value]->work_with_credit === 'Y' ? 0 : $listService[$value]->cost,
									'title' => $listService[$value]->description,
									'onclick' => $listService[$value]->work_with_credit === 'Y' ? 'contracts.showCreditServices(this);' : 'contracts.showCreditServices(this); contracts.agreementService(this)',
									'class' => $listService[$value]->work_with_credit === 'Y' ? 'work-with-credit agreement-service-js' : 'agreement-service-js'
								];

								# формируем checkbox с названием услуги
								$content = Html::checkbox('AgreementForm[serviceId]['.$value.']', $checked, $htmlCheckboxOptions);

								# если услуга откючена, то у чекбокса услуги, связанной с кредитом выводим предупреждение
								if ($disableService) {
									$content .= Html::tag('br').Html::tag('a', 'Укажите кредиты клиента', ['style' => 'color:red;', 'onclick' => '', 'class' => 'aside-ajax', 'href' => '#', 'aside-url' => '/contracts/card/'.$agreementForm->getContract()->id.'#Кредиты']);
								}

								# если услуга работает с кредитами, то выводим под услугой список кредитов клиента
								if ($listService[$value]->work_with_credit === 'Y') {
									$existCount = 0;
									$creditList = ArrayHelper::map($listContractService, 'id', function($a) {
												return [$a->service_id => $a->credit_id];
											});
									foreach ($listContractCredit as $contractCredit) {
										# проверяем, не активирована ли уже эта услуга для этого кредита
										$alreadyExist = (in_array([$value => $contractCredit->id], $creditList));
										$htmlInnerCheckboxOptions = [
											'value' => $value,
											'label' => ($alreadyExist) ? $contractCredit->creditor_name.' '.Html::tag('a', 'Услуга по данному кредиту уже активирована', ['onclick' => '', 'class' => 'aside-ajax bg-info', 'href' => '#', 'aside-url' => '/contracts/card/'.$agreementForm->getContract()->id]) : $contractCredit->creditor_name,
											$alreadyExist ? 'disabled' : '' => 'disabled',
											'data-cost' => $listService[$value]->cost,
											'title' => $contractCredit->creditor_name.' '.$contractCredit->loan_agreement,
											'onclick' => 'contracts.showCreditServices(this); contracts.agreementService(this)',
											'class' => 'agreement-service-js',
										];
										if ($alreadyExist) {
											$existCount++;
										};
										$costInput = Html::tag('div', Html::tag('div', Html::tag('div', Html::textInput('AgreementForm[serviceId]['.$value.']['.$contractCredit->id.'][cost]', null, ['onkeyup' => 'contracts.agreementDebt()', 'class' => 'form-control service-cost-js']).Html::tag('div', 'р', ['class' => 'input-group-addon']), ['class' => 'input-group']), ['class' => 'input-wrapper']), ['class' => 'col-xs-12 col-sm-6', 'style' => 'margin-left:20px;']);
										$firstInstalmentInput = Html::tag('div', Html::tag('div', Html::tag('div', Html::textInput('AgreementForm[serviceId]['.$value.']['.$contractCredit->id.'][first_installment]', null, ['onkeyup' => 'contracts.agreementDebt()', 'class' => 'form-control service-first-instalment-js', 'placeholder' => 'Первый взнос']).Html::tag('div', 'р', ['class' => 'input-group-addon']), ['class' => 'input-group']), ['class' => 'input-wrapper']), ['class' => 'col-xs-11 col-sm-5']);
										$row = Html::tag('div', $costInput.$firstInstalmentInput, ['class' => 'row', 'style' => 'display:none;']);
										if ($existCount == count($listContractCredit)) {
											$row = Html::tag('br').Html::tag('a', 'Добавьте кредиты', ['style' => 'color:red;', 'onclick' => '', 'class' => 'aside-ajax', 'href' => '#', 'aside-url' => '/contracts/card/'.$agreementForm->getContract()->id.'#Кредиты']).$row;
										}
										$content .= Html::tag('div', Html::checkbox('AgreementForm[serviceId]['.$value.']['.$contractCredit->id.']', $checked, $htmlInnerCheckboxOptions).$row, ['class' => 'credit-services-list-js', 'style' => 'display:none;margin-left:20px;']).' ';
									}
								} else {
									# в противном случае выводим поля для указания стоимости услуги и первого взноса
									$costInput = Html::tag('div', Html::tag('div', Html::tag('div', Html::textInput('AgreementForm[serviceId]['.$value.'][0][cost]', null, ['onkeyup' => 'contracts.agreementDebt()', 'class' => 'form-control service-cost-js']).Html::tag('div', 'р', ['class' => 'input-group-addon']), ['class' => 'input-group']), ['class' => 'input-wrapper']), ['class' => 'col-xs-12 col-sm-6']);
									$firstInstalmentInput = Html::tag('div', Html::tag('div', Html::tag('div', Html::textInput('AgreementForm[serviceId]['.$value.'][0][first_installment]', null, ['onkeyup' => 'contracts.agreementDebt()', 'class' => 'form-control service-first-instalment-js', 'placeholder' => 'Первый взнос']).Html::tag('div', 'р', ['class' => 'input-group-addon']), ['class' => 'input-group']), ['class' => 'input-wrapper']), ['class' => 'col-xs-12 col-sm-6']);
									$content .= Html::tag('div', $costInput.$firstInstalmentInput, ['class' => 'row', 'style' => 'display:none;']);
								}
								return Html::tag('div', $content, ['class' => 'checkbox']);
							}]
								);
								?>
							</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12">
						<?= $form->field($agreementForm, 'cost', [])->hiddenInput(['onkeyup' => 'contracts.agreementDebt()'])->label(false) ?>
						<h4>Стоимость договора: <span class="contract-cost-js">__</span> руб.</h4>
					</div>
					<div class="col-xs-12 col-sm-12" style="margin-bottom:20px;">
						<?= $form->field($agreementForm, 'first_installment', [])->hiddenInput(['onkeyup' => 'contracts.agreementDebt()'])->label(false) ?>
						<h4>Первый взнос: <span class="contract-first-instalment-js">__</span> руб.</h4>
					</div>
					<div class="col-xs-12 js-payment-type-block">
						<?=
						$this->renderAjax('@app/modules/payment/views/_payment_types_field', [
							'form' => $form,
							'model' => $agreementForm,
							'field' => 'paymentType',
							'options' => []
						])
						?>
					</div>
					<div class="col-xs-12">
						<?= $form->field($agreementForm, 'comment')->textarea() ?>
					</div>
				</div>
				<h4>График выплат:</h4>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">Долг клиента: <strong id="debt">___</strong></div>
					</div>
				</div>
				<div class="row js-grafic">
					<div class="col-xs-4"><?=
						$form->field($agreementForm, 'day_payment')->widget(DatePicker::classname(), [
							'options' => ['placeholder' => 'Дата', 'name' => 'AgreementForm[day_payment][]', 'class' => 'js-datepicker', 'onchange' => 'contracts.agreementDebt()'],
							'removeButton' => false,
							'pluginOptions' => [
								'autoclose' => false,
								'todayHighlight' => true,
								'weekStart' => 1,
							]
						])->label(false);
						?>
					</div>
					<div class="col-xs-3">
						<?= $form->field($agreementForm, 'payment', ['inputTemplate' => '<div class="input-group">{input}<div class="input-group-addon">р</div></div>'])->textInput(['name' => 'AgreementForm[payment][]', 'class' => 'form-control js-grafic-payment', 'onkeyup' => 'contracts.agreementDebt();'])->label(false) ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($agreementForm, 'payment_comment')->textInput(['name' => 'AgreementForm[payment_comment][]', 'class' => 'form-control', 'placeholder' => 'Комментарий'])->label(false); ?>
					</div>
					<div class="col-xs-1 close-grafic" style="display: none">
						<a href="#" class="btn" onclick="contracts.agreementDatePickerRemove(this);
										contracts.agreementDebt();
										return false;"><i class="fa fa-close"></i></a>
					</div>
				</div>
				<a href="#" class="btn btn-success btn-xs pull-right" onclick="contracts.agreementDatePicker(this);
								return false;"><i class="fa fa-plus"></i> Добавить дату выплаты</a>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">Остаток: <strong id="left">___</strong></div>
					</div>
				</div>
				<div class="inner-btn">
					<?= Html::submitButton('Добавить услуги', ['data-user' => user()->id, 'class' => 'btn btn-block btn-sm btn-primary disabled', 'id' => 'js-enter-contract', 'disabled' => 'disabled', 'onclick' => 'return contracts.save(this);']); ?>
				</div>
			</div>
			
		</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>
