<?php

use common\models\Contract;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use common\models\Task;
use common\components\ListHelper;
use common\models\UserSettings;

# регистрируем стили и скрипты 
$this->registerJsFile('/js/jquery/jquery.form.js', ['depends' => 'yii\web\JqueryAsset']);
# иконки статусов
$statusIcons = [Contract::STATUS_PROCESSING => 'fa fa-refresh', Contract::STATUS_PAYMENT => 'fa fa-check', Contract::STATUS_DELETED => 'fa fa-trash'];

$cssClassTask = [
];

$this->title = 'Договора';
?>


<div class="contracts-wrapper">
	<div class="nav-header<?= (UserSettings::findOne(['user_id' => user()->id])->show_menu === 'Y') ? ' nav-header-minified"' : '';?>">

		<?php /** -------- <Навигация раздела>  -------- */ ?>
		<?= Nav::widget(['items' => $this->context->module->getMenuItems(), 'options' => ['class' => 'nav nav-tabs']]); ?>
	</div>	

	<div class="row index-main-row">
		<?php /** -------- <Фильтр договоров> -------- */ ?>
		<div class="col-xs-12 col-sm-4 col-sm-push-8 col-md-4 col-md-push-8 col-lg-4 col-lg-push-8 contracts-filter js">
			<div class="piece-of-sheet filter-block" style="margin-top:20px;">
				<?php if (!request()->isAjax) {
					if (isset($freeFilter)) {
						echo $this->render('free-filter', ['filter' => $filter]);								
					} else {
						echo $this->render('filter', ['filter' => $filter]);								
					};
				} ?>
			</div>
		</div>

		<?php /** -------- <Список договоров> -------- */ ?>
		<div class="col-xs-12 col-sm-8 col-sm-pull-4 col-md-8 col-md-pull-4 col-lg-8 col-lg-pull-4 contracts-list js" id="items-container-js">
			<div>
				<div style="float: left; margin-top: 15px;">Показано <?= count($filter->getItems());?> из <?= $filter->totalCount.' '.  rus_plural($filter->totalCount, ['договора', 'договоров', 'договоров']); ?></div>
				<div class="text-right" style="padding: 10px 0 20px 0;"><?= Html::button('Добавить договор', ['class' => 'btn btn-xs btn-success aside-ajax', 'aside-title' => 'Добавление договора', 'aside-url' => '/contracts/create', 'aside-width' => 700]); ?></div>
			</div>
			<table class="table table-hover table-contracts js">
				<thead>
					<tr>
						<th style="width: 50px;"></th>
						<th style="width: 200px;">Проверен</th>
						<th style="width: 300px;">ФИО</th>
						<th style="width: 200px;">Телефон</th>
						<th class="hidden-xs">Ближайшие события</th>
					</tr>
				</thead>
				<tbody class="aside-list" aside-url="/contracts/card/{contract_id}" aside-width="700">
					<?php foreach ($filter->getItems() as $contract): ?>
						<tr class="aside-item" aside-title="<?= $contract->abonent->name.' №'.$contract->id; ?>" contract_id="<?= $contract->primaryKey; ?>">
							<td><i title="<?= Contract::getStatusList($contract->status); ?>" class="<?= ag($statusIcons, $contract->status); ?>"></i></td>
							<td><?=  FormatText::rusDate($contract->updated_at);?></td>
							<td>
							<?=$contract->abonent->name;?><br/>
								<?php if(isset($contract->user->name)) {
									echo '<span class="small" style="color:grey">'.$contract->user->name." ";
									if (!is_null($contract->signed_at)) {
										if (substr($contract->signed_at, 0, 4) >= date("Y")) {
											echo FormatText::rusDate($contract->signed_at, 'j F в H:i').'</span>';
										} else {
											echo FormatText::rusDate($contract->signed_at, 'j F Y').'</span>';
										}
									}									
								} ?>
							</td>
							<td><?= Html::a(FormatText::phone($contract->abonent->phone), 'tel:+7'.$contract->abonent->phone, ['class' => 'btn btn-xs phone-number']); ?></td>
							<td class="hidden-xs">
								<div class="contract-tasks-list">
									<?php foreach ($contract->task as $task): ?>
										<?php if ($task->status == 'process') : ?>
											<a href="#" title="<?= $task->author->name; ?>: <?= $task->comment ?>" class="aside-ajax task-close btn btn-xs btn-<?= ListHelper::getTaskLimitation($task->start_at) ?>" aside-url="/contracts/task-<?= $task->action ?>/<?= $task->contract->primaryKey; ?>/<?= $task->primaryKey ?>" aside-title="<?= $task->contract->abonent->name.' '.FormatText::phone($task->contract->abonent->phone); ?>" aside-width="700"><?= Task::getTaskList($task->task) ?> <?= FormatText::rusDate($task->start_at, 'j F') ?></a>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="pull-right contract-pager js"><?= LinkPager::widget(['pagination' => $filter->getPagination()]); ?></div>
			</div>	
	</div>
</div>
