<?php
/**
 * Представление календаря
 * @var crm\models\Calendar $calendar
 * @var array $tasks Массив задач, сгруппированных по дате начала
 * @var crm\modules\contracts\models\CalendarFilterForm $filter - фильтр задач для календаря
 * @var strint $route - Маршрут, передаваемый из контроллера
 */
use common\models\Task;
use yii\bootstrap\Nav;
use kartik\widgets\DatePicker;
use common\components\ListHelper;
use common\models\UserSettings;


$this->registerJsFile('/js/calendar.js', ['depends' => 'yii\web\JqueryAsset']);
$this->title = 'Календарь задач';
?>
<div class="nav-header<?= (UserSettings::findOne(['user_id' => user()->id])->show_menu === 'Y') ? ' nav-header-minified"' : '';?>">

	<?php /** -------- <Навигация раздела>  -------- */ ?>
	<?= Nav::widget(['items' => $this->context->module->getMenuItems(), 'options' => ['class' => 'nav nav-tabs']]); ?>
</div>

<div class="row main-calendar-row index-main-row">
	<div class="col-md-9 calendar-column">
		<table class="table calendar">
			<thead>
				<?php foreach (FormatText::$daysOfWeek as $dayOfWeek) : ?>
					<th class="text-center ucfirst"><?= ucfirst($dayOfWeek) ?></th>
				<?php endforeach; ?>
			</thead>
			<tbody>
				<?php $day = $calendar->firstDayOnSheet; ?>
				<?php for ($w=0; $w<$calendar->weeksInMonth; $w++) : ?>
					<tr class="week">
						<?php for ($d=0; $d<7; $d++, $day->add(new \DateInterval('P1D'))) : ?>
							<td class="day <?= ($day->format('m') !== $calendar->currentDay->format('m')) ? 'not-in-this-month' : '' ?>
								<?= ($day->format('Y-m-d') == $calendar->today->format('Y-m-d')) ? 'today' : '' ?>
								<?= ($day->format('Y-m-d') == $calendar->currentDay->format('Y-m-d')) ? 'current-day' : '' ?>
								<?= ($day->format('w') == 0 || $day->format('w') == 6) ? 'weekend' : '' ?>"
								data-date="<?= $day->format('Y-m-d'); ?>"
								style='height: <?= $height = ceil(744 / $calendar->weeksInMonth); ?>px; line-height: 12px;'>
								<div class="day-number" style="margin-bottom: 3px;"><?= $day->format('d'); ?></div>
								<!--Задачи-->
								<?php $tasks = $filter->getItems(); ?>
								<?php if (isset($tasks[$day->format('Y-m-d')])) : ?>
									<?php if (isset($tasks[$day->format('Y-m-d')]['icons'])) : ?>
										<?php /* Если есть задачи по иконкам, то выводим их */ ?>
										<?php foreach ($tasks[$day->format('Y-m-d')]['icons'] as $taskType) : ?>
											<?php if (count($taskType) > 1) : ?>
												<?php /* Если в оконке больше одной задачи, то выводим список задач */ ?>
													<a href="#" class="aside-ajax task-icon-item" aside-url="/contracts/calendar/task-list/t<?= $taskType[0]->task.'_date'.$day->format('Y-m-d').'_'.preg_replace('/_date(.){10}/', '', $route); ?>" aside-title="<?= Task::getTaskList()[$taskType[0]->task];?>  <?= FormatText::rusDate($day, 'd F Y, l') ?>" aside-width="700" onclick="return false;">
													<div class="task-icon">
														<i class="fa <?= ListHelper::taskIconType($taskType[0]->task); ?>"></i>
														<div class="task-count"><?= count($taskType); ?></div>
													</div>
												</a>
											<?php else : ?>
												<?php /* Если в оконке всего одна задача, то выводим задачу */ ?>
												<a href="#" class="aside-ajax task-icon-item" aside-url="/contracts/task-<?= $taskType[0]->action ?>/<?= $taskType[0]->contract->primaryKey; ?>/<?= $taskType[0]->primaryKey ?>" aside-title="<?= $taskType[0]->contract->abonent->name.' '.FormatText::phone($taskType[0]->contract->abonent->phone); ?>" aside-width="700" onclick="return false;">
													<div class="task-icon">
														<i class="fa <?= ListHelper::taskIconType($taskType[0]->task); ?>"></i>
														<div class="task-count"><?= count($taskType); ?></div>
													</div>
												</a>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
										<?php /* Если задачи НЕ сгруппированы по иконкам, то выводим список задач */ ?>
										<?php foreach ($tasks[$day->format('Y-m-d')] as $task) : ?>
											<a href="#" class="aside-ajax task-item" aside-url="/contracts/task-<?= $task->action ?>/<?= $task->contract->primaryKey; ?>/<?= $task->primaryKey ?>" aside-title="<?= $task->contract->abonent->name.' '.  FormatText::phone($task->contract->abonent->phone); ?>" aside-width="700" onclick="return false;">
												<div class="task-item">
													<div class="task-image" style="float: left;"><i class="fa <?= ListHelper::taskIconType($task->task);?>"></i></div>
													<div class="task-title" title="<?= $task->comment;?>"><?= $task->contract->abonent->name; ?></div>
												</div>
											</a>										
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endif; ?>
								
							</td>
						<?php endfor; ?>
					</tr>
				<?php endfor; ?>
			</tbody>
		</table>
	</div>
	<div class="col-md-3 filter-column">
		
		<?php /*---------- DatePicker -----------*/ ?>
		<div class="month-name ucfirst hidden-sm hidden-xs">
			<?= FormatText::$months[$calendar->currentDay->format('n')].' '.$calendar->currentDay->format('Y'); ?> 
		</div>
		<?php
		echo '<div class="well well-sm" style="background-color: #fff; width:245px; margin: 10px auto;">';
		echo DatePicker::widget([
			'model' => $calendar,
			'attribute' => 'currentDayString',
			'type' => DatePicker::TYPE_INLINE,
			'pluginOptions' => [
				'format' => 'yyyy-mm-dd',
				'todayHighlight' => true,
			],
			'pluginEvents' => [
				'changeDate' => "function(e) {calendar.calendarRefresh(e.format('yyyy-mm-dd'));}",
				'changeMonth' => "function(e) {calendar.calendarRefresh(e.date.format('yyyy-mm-dd'));}",
			],
			'options' => [
				'class' => 'hide',
			]
		]);
		echo '</div>';
		?>
		
		<?php /* ---------- Фильтр задач ----------- */ ?>
		<div class="col-md-12 calendar-filter js">
			<?= request()->isAjax ? '' : $this->render('filter', ['filter' => $filter]); ?>
		</div>
	</div>
</div>