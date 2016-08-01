<?php
/** Список задач менеджеров */
/* @var $task common\models\Task */

use yii\bootstrap\Nav;
use common\models\Task;
use common\components\ListHelper;

$this->title = 'Задачи';
?>
<div class="contracts-wrapper">
	<h3>Управление задачами</h3>

	<?php /** -------- <Навигация раздела>  -------- */ ?>
	<?= Nav::widget(['items' => $this->context->module->getMenuItems(), 'options' => ['class' => 'nav nav-tabs']]); ?>

	<div class="row">
		<?php /** -------- <Фильтр договоров> -------- */ ?>
		<div class="col-xs-12 col-sm-4 col-sm-push-8 col-md-4 col-md-push-8 col-lg-4 col-lg-push-8 tasks-filter js">
			<?= request()->isAjax ? '' : $this->render('filter_task', ['filter' => $filter]); ?>
		</div>
		<?php /** -------- <Список задач текущего пользователя> -------- */ ?>
		<div class="col-xs-12 col-sm-8 col-sm-pull-4 col-md-8 col-md-pull-4 col-lg-8 col-lg-pull-4 tasks-list js" style="border-right: 1px solid rgb(221, 221, 221);">
			<ul>
				<?php foreach ($filter->getItems() as $task): ?>
					<li>
						<div class="task-info aside-list" aside-width="700">
							<div class="task-time aside-item" aside-title="<?= $task->contract->abonent->name; ?>" task_type="<?= $task->action ?>" contract_id="<?= $task->contract->primaryKey; ?>" task_id="<?= $task->primaryKey ?>"><?= Task::getTaskList($task->task).' '.FormatText::dateInterval($task->start_at); ?></div>
							<div class="task-icon aside-item" aside-title="<?= $task->contract->abonent->name; ?>" task_type="<?= $task->action ?>" contract_id="<?= $task->contract->primaryKey; ?>" task_id="<?= $task->primaryKey ?>"><i class="fa <?= ListHelper::taskIconType($task->task) ?> fa-2x"></i></div>
						</div>
						<div class="task-content">
							<div class="task-title">
								<a href="#" class="aside-ajax" aside-url="/contracts/card/<?= $task->contract->primaryKey; ?>" aside-title="<?= $task->contract->abonent->name; ?>" aside-width="700" onclick="return false;"><?= $task->contract->abonent->name; ?></a> - 
								<a href="#" class="aside-ajax task-close" aside-url="/contracts/task-<?= $task->action ?>/<?= $task->contract->primaryKey; ?>/<?= $task->primaryKey ?>" aside-title="<?= $task->contract->abonent->name; ?>" aside-width="700" onclick="return false;">закрыть задачу</a>
							</div>
							<div class="task-comment"><?= $task->comment; ?></div>
							<div class="task-author">Назначил <?= ($task->author_id != $task->executor_id ? $task->author->name : NULL); ?> <?= FormatText::dateInterval($task->created_at); ?></div>
							<div class="task-files hide">Files</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>

