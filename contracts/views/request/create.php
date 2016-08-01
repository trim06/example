<?php

/** Форма создания пользователя */
/* @var $this \yii\web\View */
/* @var $userForm crm\modules\users\models\UserForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Request;
use yii\bootstrap\Alert;

echo Html::tag('div', "Заявка заблокирована пользователем <span class=blocked-user></span>", ['class' => 'blocker bg-danger', 'style' => 'margin:10px;padding:16px;display:none;']);
?>
	<?php if ($contractForm->request->status === 'discard' && strlen($contractForm->request->reason) > 0) {
		$alertText = '<h4>Брак</h4>'.$contractForm->request->reason;
	} else if ($contractForm->request->status === 'checked') {
		$alertText = 'Данная заявка уже обработана';
	} ?>
	<?php if (isset($alertText)) : ?>
		<div class="col-sm-12">
			<?= Alert::widget([
				'options' => [
				'class' => ($contractForm->request->status === 'discard') ? 'alert-warning' : 'alert-success',
				],
				'body' => $alertText,
				'closeButton' => false,
			]);; ?>
		</div>
	<?php endif; ?>
<div class="row" style="margin: 20px 0;">	
<?php $form = ActiveForm::begin(['id' => 'requestForm', 'action' => '/contracts/request/create-contract/' . $contractForm->getRequest()->id, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' =>  $contractForm->getContract()->primaryKey ]]); ?>
	<div class="col-xs-7 col-sm-9">
		<div class="piece-of-sheet">
			<?= $this->render('/contract/_form', ['form' => $form, 'contractForm' => $contractForm, 'isCreateContract' => true]); ?>
		</div>
		<?php if (user()->can('view_request_advert_info')) : ?>
			<?php if (isset($contractForm->request->utm_source)) : ?>
				<div class="piece-of-sheet">
					<table class="table table-hover" style="margin: 0;">
						<tbody>
							<tr>
								<td>utm_source:</td>
								<td><?=$contractForm->request->utm_source;?></td>
							</tr>
							<tr>
								<td>utm_medium:</td>
								<td><?=$contractForm->request->utm_medium_type;?></td>
							</tr>
							<tr>
								<td>utm_campaign:</td>
								<td><?=$contractForm->request->utm_campaign;?></td>
							</tr>
							<tr>
								<td>utm_keyword:</td>
								<td><?=$contractForm->request->utm_keyword;?></td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="bs-callout bs-callout-warning">
					<p>Рекламная информация по данной заявке отсутствует.</p>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<div class="col-xs-5 col-sm-3 list-actions">
		<div class="top-actions">
		<?php //= Html::submitButton('Сохранить', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
		<?php if (/*$contractForm->request->isFree() &&*/ in_array($contractForm->request->status, [Request::NEW_REQUEST, Request::DISCARD])) : ?>
			<?= Html::submitButton('Встреча', ['data-action' => 'task-meet', 'title' => 'Назначить встречу', 'class' => 'btn btn-block btn-sm btn-primary disablable', 'onclick' => 'return request.action_request(this);', 'disabled' => 'disabled']); ?>
			<?= Html::submitButton('Перезвонить', ['data-action' => 'task-call', 'title' => 'Перезвонить', 'class' => 'btn btn-block btn-sm btn-primary disablable', 'onclick' => 'return request.action_request(this);', 'disabled' => 'disabled']); ?>
			<?= Html::submitButton('Договор', ['data-action' => 'agreement', 'title' => 'Договор', 'class' => 'btn btn-block btn-sm btn-success disablable', 'onclick' => 'return request.action_request(this);', 'disabled' => 'disabled']); ?>
			<?php if ($request->status !== Request::DISCARD) : ?>
				<?= Html::submitButton('Удалить', ['data-id' => $request->id, 'title' => 'Удалить', 'class' => 'btn btn-block btn-sm btn-danger', 'onclick' => 'return request.delete(this);']); ?>
				<?= Html::submitButton('Брак', ['aside-url' => '/contracts/request/discard/'.$request->id, 'data-id' => $request->id, 'title' => 'Пометить заявку как брак', 'class' => 'btn btn-block btn-sm btn-warning', 'onclick' => 'request.discard(this); return false;']); ?>
			<?php endif; ?>
		<?php endif; ?>
		</div>
	</div>	
<?php ActiveForm::end(); ?>
</div>
<script>
	$(document).ready(function() {
		$('#contractform-phone').trigger('keyup');
	});
</script>
