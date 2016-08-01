<?php
use common\models\Task;
use common\components\ListHelper;

# распаковываем массив задач из фильтра
$tasks = (isset(current($filter->getItems())['icons'])) ? current(current(current($filter->getItems()))) : current($filter->getItems());
?>

<div class="tasks-list calendar-tasks-list js text-center" style="border-right: 1px solid rgb(221, 221, 221);">
	<ul>
		<?php foreach ($tasks as $task): ?>
			<li>
				<div class="task-info aside-list" aside-width="700">
					<div class="task-time"><?= Task::getTaskList($task->task) . ' ' . FormatText::rusDate($task->start_at, 'd F в G:i'); ?></div>
					<a href="#" class="aside-ajax task-close" aside-url="/contracts/task-<?= $task->action ?>/<?= $task->contract->primaryKey; ?>/<?= $task->primaryKey ?>" aside-title="<?= $task->contract->abonent->name . ' ' . FormatText::phone($task->contract->abonent->phone); ?>" aside-width="700" onclick="return false;">
						<div class="task-icon label-<?= ListHelper::getTaskLimitation($task->start_at) ?>"><i class="fa <?= ListHelper::taskIconType($task->task) ?> fa-2x"></i></div>
					</a>
				</div>
				<div class="task-content">
					
					<div class="task-title">
						<a href="#" class="aside-ajax" aside-url="/contracts/card/<?= $task->contract->primaryKey; ?>" aside-title="<?= $task->contract->abonent->name . ' ' . FormatText::phone($task->contract->abonent->phone); ?>" aside-width="700" onclick="return false;"><?= $task->contract->abonent->name; ?></a>
					</div>
					<div class="task-comment"><?= $task->comment; ?></div>
					<div class="task-author">Назначил <?= ($task->author_id != $task->executor_id ? $task->author->name : NULL); ?> <?= FormatText::dateInterval($task->created_at); ?></div>
					<div class="task-files hide">Files</div>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
