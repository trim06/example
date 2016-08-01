<?php

namespace crm\modules\contracts\models;

use yii\helpers\ArrayHelper;
use common\models\Unit;
use common\models\Task;
use common\models\BaseFilterForm;

/**
 * Модель фильтра задач для календаря
 */
class CalendarFilterForm extends BaseFilterForm {

	/* поля фильтра */
	public $date_from;
	public $date_to;
	public $author_id;
	public $executor_id;
	public $task;
	public $status;
	public $contract;
	public $date;


	public $activeDay;

	/**
	 * Инциализация фильтра
	 * @param str $route
	 */
	public function initFilter($route = NULL) {		
		$this->setQuery(Task::find()->with(['contract' => function($query) {
			$query->with('abonent');
		}, 'author', 'executor'])->orderBy('start_at')->joinWith('contract')->andWhere(['in', 'task.unit_id', ArrayHelper::getColumn(Unit::find()->byUser()->all(), 'id')]));
		
		# ищем параметр 'date' в маршруте
		$position = strpos($route, 'date');
		if ($position !== false) {
			$this->activeDay = substr($route, $position+4, 10);
		} else {
			# если указана граница "с" и НЕ указана "по", то активным делаем день начала диапазона
			$from = strpos($route, 'df20');
			$to = strpos($route, 'dt20');
			if ($from !== false && $to === false) {
				$activeDay = substr($route, $position+2, 10);
				$this->activeDay = str_replace('.', '-', $activeDay);
			}
		}
		
		
		# если передан маршрут, заполняем модель
		if ($route) $this->fillByRoutes($route);
	}

	
	/**
	 * Возвращает массив моделей, удовлетворяющих условиям фильтра
	 * @return array
	 */
	public function getItems($refresh = FALSE) {
		if ($this->_items === NULL || $refresh === TRUE) {
			$items = $this->_query->all();
			
			# компонуем задачи по дням
			$tasksByDays = [];
			foreach ($items as $task) {
				$index = substr($task->start_at, 0, 10);
				$tasksByDays[$index][] = $task;
			}
			
			# если задач на день много - группируем по типу задач
			# в представлении нужно устраивать проверку на наличие "['icons']"
			foreach ($tasksByDays as $key => $dayTasks) {
				if (count($dayTasks) > 4) {
					$taskByIcons = [];
					foreach ($dayTasks as $task) {
						$taskByIcons[$task->task][] = $task;
					}
					$dayTasks = [];
					$dayTasks['icons'] = $taskByIcons;
					$tasksByDays[$key] = $dayTasks;
				}
			}			
			
			$this->_items = $tasksByDays;
		}
		return $this->_items;
	}
	
	
	/**
	 * Возвращает список правил, описывающих логику работы фильтра
	 * @return array
	 */
	public function routeRules() {
		return [
			'id' => ['attr' => 'id', 'table_field' => 'task.id', 'condition' => 'in'],
			'a' => ['attr' => 'author_id', 'table_field' => 'task.author_id' , 'condition' => 'in'],
			'e' => ['attr' => 'executor_id', 'function' => 'byExecutor'],
			't' => ['attr' => 'task', 'table_field' => 'task.task', 'condition' => 'in'],
			's' => ['attr' => 'status', 'table_field' => 'task.status', 'condition' => 'in'],
			'c' => ['attr' => 'contract', 'table_field' => 'task.contract_id', 'condition' => 'in'],
			'df' => ['attr' => 'date_from', 'function' => 'byDateFrom'],
			'dt' => ['attr' => 'date_to', 'function' => 'byDateTo'],
			'date' => ['attr' => 'date', 'function' => 'activeDay'],
		] + parent::routeRules();
	}

	public function attributeLabels() {
		return [
			'author_id' => 'Назначил',
			'executor_id' => 'Исполнитель',
			'task' => 'Тип задачи',
			'status' => 'Статус задачи',
			'contract' => 'Договор',
		];
	}
	
	/* Добавляет критерий начальной даты диапазона задач */
	protected function byDateFrom() {
		$date = str_replace('.', '-', $this->date_from);
		$dateFrom = (new \DateTime($date))->format('Y-m-d 00:00:00');
		$this->getQuery()->andWhere(['>=', 'task.start_at', $dateFrom]);
	}

	protected function byDateTo() {		
		$date = str_replace('.', '-', $this->date_to);
		$dateTo = (new \DateTime($date))->format('Y-m-d 23:59:59');
		$this->getQuery()->andWhere(['<=', 'task.start_at', $dateTo]);
	}
	
	# достает activeDay из date и заменяет исходное значение date
	protected function activeDay() {
		$this->activeDay = implode('-', $this->date);
		$this->date = $this->activeDay;
	}
	
	# поиск договора по исполнителю
	protected function byExecutor() {
		$conditions = explode('.', $this->executor_id);
		foreach ($conditions as $condition) {
			$prepend = substr($condition, 0, 1);
			$value = substr($condition, 2);
			switch ($prepend) {
				case 0:
					$this->getQuery()->joinWith('executor')->andWhere(['task.unit_id' => $value]);
					break;
				case 1: $this->getQuery()->andWhere(['task.executor_id' => $value]);
					break;
				default: break;
			}
		}
	}

}
