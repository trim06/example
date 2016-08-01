<?php
/**
 * @var boolean $setInterval - признак задания интервала доступных дат
 */

use kartik\widgets\DateTimePicker;

$defaultOptions = [
	'options' => ['placeholder' => 'Дата'],
	'removeButton' => false,
	'pluginOptions' => [
		'autoclose' => TRUE,
		'weekStart' => 1,
		'format' => 'dd.mm.yyyy hh:ii',
	]
];

# если задан флаг setInterval = true, то добавляем доступный для выбора интервал дат
if (@$setInterval) {
	$date = new \DateTime();
	// если в setInterval передано число, то указываем интервал с заданным числом недель
	$intervalString = (is_int($setInterval))?"P{$setInterval}W":"P2W";
	# добавляем доступный интервал дат (2 недели)
	$defaultOptions['pluginOptions']['startDate'] = $date->format('d.m.Y H:i');
	$defaultOptions['pluginOptions']['endDate'] = $date->add(new \DateInterval($intervalString))->setTime(23, 59, 59)->format('d.m.Y H:i');
}

$options = isset($htmlOptions) ? $htmlOptions + $defaultOptions : $defaultOptions;

?>

<?= $form->field($taskForm, 'start_at')->widget(DateTimePicker::classname(), $options); ?>
