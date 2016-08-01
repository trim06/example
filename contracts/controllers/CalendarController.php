<?php

namespace app\modules\contracts\controllers;

/**
 * Контроллер для работы с задачами
 *
 * @author anton2
 */

use yii\filters\AccessControl;
use common\models\Task;
use crm\models\Calendar;
use crm\modules\contracts\models\CalendarFilterForm;
use crm\modules\contracts\models\TaskFilterForm;

class CalendarController extends BaseContractController {
	
	public $defaultAction = 'month';
	
	public function behaviors() {
		$behaviours = parent::behaviors();

		$rules = [
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return user()->can('view_contract_calendar');
				}
			]
		];
		$behaviours['access']['rules'] = array_merge($behaviours['access']['rules'], $rules);

		return $behaviours;
	}
	
	public function actionMonth($route = null) {
		# по умолчанию показываем только задачи текущего пользователя
		if (!request()->isAjax && $route == null) {
			$route = "e0$".user()->identity->current_unit_id.".1$".user()->id.'_sprocess';
		}
		
		# формируем массив задач с использованием фильтра
		$filter = new CalendarFilterForm($route);
		
		# создаем модель календаря в соответствии с полученной датой
		$calendar = new Calendar([], $filter->activeDay);
		
		# ищем только те задачи, которые находятся во временном промежутке, изображенном на листе
		$filter->getQuery()->andWhere(['between', 'task.start_at', $calendar->firstDayOnSheet->format("Y-m-d H:i:s"), $calendar->lastDayOnSheet->format("Y-m-d 23:59:59")]);
		
		return request()->isAjax ? $this->renderAjax('calendar', ['calendar' => $calendar, 'filter' => $filter, 'route' => $route]) : $this->render('calendar', ['calendar' => $calendar, 'filter' => $filter, 'route' => $route]);
		//return $this->render('calendar', ['calendar' => $calendar,'tasks' => $tasks]);
	}
	
	public function actionTaskList($route = null) {
		$filter = new CalendarFilterForm($route);
		$filter->getQuery()->andWhere(['between', 'task.start_at', (new \DateTime($filter->activeDay))->format("Y-m-d 00:00:00"), (new \DateTime($filter->activeDay))->format("Y-m-d 23:59:59")]);
		return request()->isAjax ? $this->renderAjax('task_list', ['filter' => $filter]) : $this->render('task_list', ['filter' => $filter]);
	}
	
}
