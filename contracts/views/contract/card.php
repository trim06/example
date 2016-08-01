<?php
/* @var $this \yii\web\View */
use yii\helpers\Html;
echo Html::tag('div', "Карточка заблокирована пользователем <span class=blocked-user></span>", ['class' => 'blocker bg-danger', 'style' => 'margin:10px;padding:16px;display:none;']);
echo yii\bootstrap\Tabs::widget([
	'items' => [
		[
			'label' => 'Клиент',
			'content' => isset($cardVars['client']) ? $this->render('edit', $cardVars['client']) : NULL,
			'active' => true
		], [
			'label' => 'Документы',
			'content' => isset($cardVars['files']) ? $this->render('documents', $cardVars['files']) : NULL,
			'headerOptions' => ['onclick' => 'setTimeout(main.initScrollbar, 10);'],
		], [
			'label' => 'Кредиты',
			'content' => isset($cardVars['credits']) ? $this->render('credits', $cardVars['credits']) : NULL,
		], [
			'label' => 'Услуги',
			'content' => isset($cardVars['services']) ? $this->render('services', $cardVars['services']) : NULL,
			'visible' => isset($cardVars['services']) && (($cardVars['services']['contractStatus'] === 'payment') || isset($cardVars['services']) && count($cardVars['services']['listContractService'])),
			'headerOptions' => ['onclick' => 'setTimeout(contracts.initProgressBars, 100);'],
		], [
			'label' => 'Оплаты',
			'content' => isset($cardVars['payments']) ? $this->render('payments', $cardVars['payments']) : NULL,
			'visible' => (isset($cardVars['payments']) && count($cardVars['payments']['listPayments'])),
		], [
			'label' => 'История',
			'content' => isset($cardVars['history']) ? $this->render('history', $cardVars['history']) : NULL,
		],// [
//			'label' => 'Заявления',
//			'content' => isset($cardVars['statement']) ? $this->render('statement', $cardVars['statement']) : null,
//		]
	],
	'options' => ['class' => 'aside-tabs'],
]);
