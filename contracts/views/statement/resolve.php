<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\ContractStatement;
use crm\modules\contracts\models\StatementForm;
/**
 * Представление для принятия решения по заявлению
 * @var ContractStatement $statement  - заявление
 */
?>
<div class="col-md-12" style="margin-top: 20px;">
	<div class="piece-of-sheet">
		<div style="float:right;"><span>Заявление принято <?= FormatText::rusDate($statementForm->getStatement()->created_at); ?></span></div>
		<?php $form = ActiveForm::begin(); ?>
		<div>
			<div class="statement-text" style="margin-bottom:20px;">
				<label class="control-label">Текст заявления</label>
				<br/>
				<?= nl2br($statementForm->text); ?>
			</div>
			<div class="statement-files">
				<label class="control-label">Файлы заявления</label>
				<br/>
			</div>
			<?php if ($statementForm->doc_count > 0) : ?>
				<?php $files = scandir($statementForm->getStatement()->getPath());?>
				<?php foreach ($files as $file) : ?>
					<?php if ($file !== '.' && $file !== '..') : ?>
						<a href="/contracts/statement/download-file/<?=$statementForm->getStatement()->id;?>/<?=$file?>" download><?= $file.' - Скачать документ'; ?></a>
						<br/>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?= $form->field($statementForm, 'status')->hiddenInput(['value' => 'Y'])->label(false); ?>
			<?php if ($statementForm->status === 'N') : ?>
				<?= $form->field($statementForm, 'sendType', ['options' => ['style' => 'margin-top:20px;']])->inline()->radioList(StatementForm::getTypes(), [
					'item' => function($index, $label, $name, $checked, $value) use ($statementForm) {
						if ($value === StatementForm::TYPE_EMAIL && (is_null($statementForm->getStatement()->abonent->email) || $statementForm->getStatement()->abonent->email === '')) {
							return '<label class="radio-inline">
										<input type="radio" disabled="disabled" name="'.$name.'" value="'.$value.'">'.$label.
										'<span style="color:red;margin-left:10px;cursor:help;" title="Вы можете указать email клиента в карточке договора"> У данного пользователя не указан email</span>'.
									'</label>';
						} else {
							return '<label class="radio-inline" '.(($value === 'email') ? 'title="Отправить по адресу: '.$statementForm->getStatement()->abonent->email.'"  style="cursor:help;"' : '').'>
										<input type="radio" name="'.$name.'" value="'.$value.'">'.$label.
									'</label>';
						}
					}
				]);?>
				<?= $form->field($statementForm, 'decision')->textarea(['rows' => 10, 'style' => 'resize:none;']); ?>
				<!--<h4 style="margin-top:-10px;"><small>Данный текст будет отправлен клиенту по смс</small></h4>-->
			<?php else : ?>
				<div class="statement-decision" style="margin-bottom:15px;">
					<label class="control-label">Решение</label>
					<br/>
					<?= nl2br($statementForm->decision); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="row">
			<div class="col-xs-6 col-sm-3 col-sm-offset-6">
				<div class="">
					<?= Html::button('Договор', ['class' => 'btn btn-block btn-sm btn-primary aside-ajax', 'aside-url' => '/contracts/card/'.$statementForm->getContract()->id]) ?>
				</div>
			</div>
			<div class="col-xs-6 col-sm-3">
				<div class="">
					<?php if ($statementForm->status === 'N') : ?>
						<?= Html::submitButton('Вынести решение', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'contracts.resolveStatement(this); return false;']); ?>
					<?php else : ?>
						<?= Html::button('Закрыть', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'aside.hide();']); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>