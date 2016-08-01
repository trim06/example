<?php

/** Карточка редактирования договора */
/* @var $this \yii\web\View */
/* @var $contractForm ContractForm */
/* @var $form ActiveForm */

use common\components\ListHelper;
use common\models\Contract;
use common\models\ContractLog;
use common\models\Task;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$today = new \DateTime();
$expiteAt = new \DateTime($contractForm->getContract()->expire_at);
$estimatedTime = $today->diff($expiteAt);
?>

<div class="row" style="margin: 20px 0;">
	<?php $form = ActiveForm::begin(['id' => 'contractForm', 'action' => '/contracts/edit/'.$contractForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $contractForm->getContract()->primaryKey]]); ?>
	<div class="col-xs-7 col-sm-9">
		<div class="piece-of-sheet">
			<?= $this->render('_form', ['form' => $form, 'contractForm' => $contractForm, 'estimatedTime' => $estimatedTime]); ?>
		</div>

		<?php /** -------- <Основные услуги по договору и их описание> -------- */ ?>
		<?php if (count($contractServices) > 0) : ?>
		<div class="piece-of-sheet">
			<h4>Услуги по договору</h4>
			<div class="contract-service-list items-list">
				<?php foreach ($contractServices as $contractService) : ?>
					<div class="contract-service item-in-list">
						<h4><?= $contractService->service->name; ?></h4>
						<div><?= nl2br($contractService->service->description); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<?php /** -------- <Задачи по договору и их описание> -------- */ ?>
		<?php if (count($tasks) > 0) : ?>
		<div class="piece-of-sheet">
			<h4>Ближайшие события</h4>
			<div class="card-tasks-list tasks-list js">
				<div class="left213" style="padding-left: 0;">
					<?php foreach ($tasks as $task): ?>
						<div class="item-in-list">
							<div class="task-info aside-list" aside-width="700" aside-url="/contracts/task-<?= $task->action ?>/<?= $task->contract->primaryKey; ?>/<?= $task->primaryKey ?>" aside-title="<?= $task->contract->abonent->name; ?>">
								<div class="task-time aside-item"><?= Task::getTaskList($task->task) . ' ' . FormatText::dateInterval($task->start_at); ?></div>
								<div class="task-icon aside-item"><i class="fa <?= ListHelper::taskIconType($task->task) ?> fa-2x"></i></div>
							</div>
							<div class="task-content">
								<div class="task-title">
									<a href="#" class="aside-ajax" aside-url="/contracts/card/<?= $task->contract->primaryKey; ?>" aside-title="<?= $task->contract->abonent->name; ?>" aside-width="700" onclick="return false;"><?= $task->contract->abonent->name; ?></a>
								</div>
								<div class="task-comment"><?= $task->comment; ?></div>
								<div class="task-author">Назначил <?= ($task->author_id != $task->executor_id ? $task->author->name : NULL); ?> <?= FormatText::dateInterval($task->created_at); ?></div>
								<div class="task-files hide">Files</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>		
		<?php endif; ?>
	</div>
	
	<div class="col-xs-5 col-sm-3 list-actions">
		<div class="top-actions">
			<?= Html::submitButton('Сохранить', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
			<?php if (in_array($contractForm->getContract()->status, [Contract::STATUS_PROCESSING, Contract::STATUS_PAYMENT])): ?>
				<?= Html::submitButton('Встреча', ['data-action' => 'task-meet', 'title' => 'Назначить встречу', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
				<?= Html::submitButton('Перезвонить', ['data-action' => 'task-call', 'title' => 'Перезвонить', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
				<?php if ($contractForm->getContract()->status == Contract::STATUS_PAYMENT) : ?>
					<?= Html::submitButton('Оплата', ['data-action' => 'paid', 'title' => 'Оплата по договору №'.$contractForm->getContract()->id, 'class' => 'btn btn-block btn-sm btn-success', 'onclick' => 'return contracts.action(this);']); ?>
				<?php endif; ?>
				<?php if ($contractForm->getContract()->status == Contract::STATUS_PROCESSING) : ?>
					<?= Html::button('Договор', ['data-action' => 'agreement', 'title' => 'Заключение договора №'.$contractForm->getContract()->id, 'class' => 'btn btn-block btn-sm btn-success', 'formaction' => "/contracts/edit/".$contractForm->getContract()->primaryKey."/true", 'onclick' => 'return contracts.action(this);']); ?>
					<?= Html::button('Удалить', ['title' => 'Удаление договора', 'class' => 'btn btn-block btn-sm btn-danger', 'onclick' => "return contracts.delete(this)", 'data-id' => $contractForm->getContract()->id]); ?>
					<?php if ($contractForm->getContract()->abonent->got_sms === 'N') : ?>
						<?= Html::button('Отправить смс', ['title' => 'Отправляет клиенту смс-сообщение с рекламой наших услуг', 'class' => 'btn btn-block btn-sm btn-success', 'onclick' => "return contracts.sendAdvertSms(this);"]); ?>
					<?php endif; ?>
				<?php endif; ?>				
			<?php endif; ?>
			<?php if ($contractForm->getContract()->status == Contract::STATUS_DELETED) : ?>
				<?= Html::button('Сохранить', ['title' => 'Восстановление договора', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => "contracts.save(this, function(){contracts.filter.apply()});", 'formaction' => '/contracts/edit/'.$contractForm->getContract()->primaryKey, 'data-id' => $contractForm->getContract()->id]); ?>
				<?= Html::button('Восстановить', ['title' => 'Восстановление договора', 'class' => 'btn btn-block btn-sm btn-success', 'onclick' => "return contracts.restore(this)", 'formaction' => '/contracts/edit/'.$contractForm->getContract()->primaryKey, 'data-id' => $contractForm->getContract()->id]); ?>
			<?php endif; ?>
		</div>
		<?php if ($contractForm->getContract()->status == Contract::STATUS_PAYMENT): ?>
			<?= Html::submitButton('Анализ документов', ['data-action' => 'task-analysis-doc', 'title' => 'Анализ документов', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
			<?= Html::submitButton('Запросить документы', ['data-action' => 'task-request-doc', 'title' => 'Запросить документы', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
			<?= Html::submitButton('Судебное заседание', ['data-action' => 'task-court', 'title' => 'Судебное заседание', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
			<?= Html::submitButton('Составление докум.', ['data-action' => 'task-create-doc', 'title' => 'Составление документов', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
			<?= Html::submitButton('Отправить документы', ['data-action' => 'task-send-doc', 'title' => 'Отправить документы', 'class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.action(this);']); ?>
			<?//php if (!$contractForm->getContract()->hasStatement()) : ?>
				<?= Html::submitButton('Принять заявление', ['aside-url' => '/contracts/statement/create/'.$contractForm->getContract()->id, 'title' => 'Создание заявления по договору №'.$contractForm->getContract()->id, 'aside-title' => 'Создание заявления по договору №' . $contractForm->getContract()->id, 'class' => 'btn btn-block btn-sm btn-warning aside-ajax']); ?>
			<?//php endif; ?>
		<?php endif; ?>
		<?php //= Html::submitButton('Удалить', ['data-action' => 'delete', 'title' => 'Удаление', 'class' => 'btn btn-block btn-sm btn-danger', 'onclick' => 'return contracts.action(this);']);    ?>
	</div>
	<?php ActiveForm::end(); ?>
</div>
