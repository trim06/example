<?php

/** Форма редактирования договора */
/* @var $this \yii\web\View */
/* @var $contractForm crm\modules\contracts\models\ContractForm */

use yii\helpers\Html;
use common\models\City;
use common\models\SysDefCode;

$status = $contractForm->contract->status;
?>

<?php if ($contractForm->getContract()->hasStatement() && $contractForm->getContract()->hasUnresolvedStatement()) : ?>
	<div class="row">
		<div class="col-md-12">
			<span class="aside-ajax btn btn-xs btn-danger" aside-title="Заявление по договору №<?= $contractForm->getContract()->id ?>" aside-url="/contracts/statement/resolve/<?= $contractForm->getContract()->statement->id ?>">У данного клиента есть необработанное заявление</span>
		</div>
	</div>
<?php endif; ?>

<?php if (isset($contractForm->city_id)) : ?>
<h4><?= City::find()->andWhere(['city_id' => $contractForm->city_id])->one()->name; ?></h4>
<?php endif; ?>
<div class="row">
	<?php /* ----------Оставшееся время до окончания действия договора---------- */ ?>
	<?php /* -------------показываем только если договор заключен--------------- */ ?>
	<?php if ($status == common\models\Contract::STATUS_PAYMENT) : ?>
		<div class="estimated-time text-right col-xs-12 col-sm-8 col-sm-push-4">
			<?php if ($estimatedTime->s == 0 && $estimatedTime->i == 0 && $estimatedTime->h == 0) : ?>
				Дата истечения периода договора не указана
			<?php elseif ($estimatedTime->invert == 0) : ?>
				Договор истекает через <?= $estimatedTime->days . ' ' . rus_plural($estimatedTime->days, ['день', 'дня', 'дней']) ?>
			<?php else : ?>
				Период действия договора истек
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="col-xs-12 col-sm-4 <?= ($status == common\models\Contract::STATUS_PAYMENT) ? 'col-sm-pull-8' : ' '; ?>">
		<?php 
			if (isset($isCreateContract)) {
				echo $form->field($contractForm, 'phone')->textInput(['onkeyup' => 'contracts.phoneUniqueness.verifyPhone(this)'])->iconPrepend('+7');
			} else {
				echo $form->field($contractForm, 'phone')->textInput([])->iconPrepend('+7');
			}
		?>
		<?php if (isset($contractForm->phone)) : ?>
			<div style="margin-top: -13px"><?= SysDefCode::getRegionName($contractForm->phone); ?></div>
		<?php endif; ?>
	</div>
</div>
<div id="verifyPhoneMessage" class="row" style="display:none;">
	<div class="col-xs-12">
		<div class="row" style="margin-bottom:10px;">
			<div class="col-xs-4 open-contract-button-container">
				<?//= Html::button('Открыть договор', ['class' => 'btn btn-sm btn-success btn-block', 'id' => 'open-contract']);?>
			</div>
			<div class="col-xs-8"><?= Html::tag('span', 'По данному телефону уже существует договор. Вы можете воспользоваться им.', ['class' => 'bg bg-success']); ?></div>
		</div>
		<div class="row">
			<div class="col-xs-4"><?= Html::button('Новый договор', ['class' => 'btn btn-sm btn-warning btn-block inline', 'onclick' => 'contracts.phoneUniqueness.allowCreateNewContract()', 'id' => 'new-contract']);?></div>
			<div class="col-xs-8"><?= Html::tag('span', 'Внимание! Наличие большого количества дублей затрудняет работу', ['class' => 'bg bg-warning']);?></div>
		</div>		
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-8"><?=$form->field($contractForm, 'email')->textInput(['disabled' => isset($isCreateContract)])->iconPrepend('fa-at');?></div>
</div>
<div class="row">
	<?php # если это создание договора из заявки, то указываем в форме значение имени "Клиент" ?>
	<div class="col-xs-12 col-sm-8"><?=$form->field($contractForm, 'name')->textInput(['disabled' => isset($isCreateContract), 'value' => ((isset($isCreateContract))?"Клиент":$contractForm->name)])->iconPrepend('fa-user');?></div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-8">
		<?= $form->field($contractForm, 'comment')->textarea(['disabled' => isset($isCreateContract)])->label('Комментарий к договору'); ?>
	</div>
</div>
<div class="row">
	<div class="col-md-9">
		<?= $form->field($contractForm, 'status', ['options' => ['class' => 'hidden']])->hiddenInput(['class' => 'status-js'])->label(false); ?>
	</div>
</div>

