<?php 

use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use common\models\UserSettings;

# регистрируем стили и скрипты 
$this->registerJsFile('/js/jquery/jquery.form.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerJsFile('/js/request.js', ['depends' => 'yii\web\JqueryAsset']);

$this->title = 'Заявки';
?>
<div class="contracts-wrapper">
	<div class="nav-header<?= (UserSettings::findOne(['user_id' => user()->id])->show_menu === 'Y') ? ' nav-header-minified"' : ''; ?>">

		<?php /** -------- <Навигация раздела>  -------- */ ?>
		<?= Nav::widget(['items' => $this->context->module->getMenuItems(), 'options' => ['class' => 'nav nav-tabs']]); ?>
	</div>
		
	<div class="row index-main-row">
		<?php /** -------- <Фильтр договоров> -------- */ ?>
		<div class="col-xs-12 col-sm-4 col-sm-push-8 col-md-4 col-md-push-8 col-lg-4 col-lg-push-8 contracts-filter js">
			<div class="piece-of-sheet filter-block" style="margin-top:20px;">
				<?= request()->isAjax ? '' : $this->render('filter', ['filter' => $filter]); ?>
			</div>
		</div>

		<?php /** -------- <Список договоров> -------- */ ?>
		
		<div class="col-xs-12 col-sm-8 col-sm-pull-4 col-md-8 col-md-pull-4 col-lg-8 col-lg-pull-4 request-list js" id="items-container-js">
			<div clsss="row">
				<div style="margin: 15px 0;">Показано <?= count($filter->getItems()); ?> из <?= $filter->totalCount.' '.rus_plural($filter->totalCount, ['заявки', 'заявок', 'заявок']); ?></div>
			</div>				
			<table class="table table-hover table-contracts js table-request">
				<thead>
					<tr>
						<th></th>
						<!--<th>Дата заявки</th>-->
						<th>Имя клиента</th>
						<th>Телефон</th>
						<th>Источник заявки</th>
					</tr>
				</thead>
				<tbody class="aside-list" aside-url="/contracts/request/create-contract/{request_id}" aside-width="700">
					<?php foreach ($filter->getItems() as $request): ?>
						<tr class="aside-item" aside-title="<?= 'Заявка от клиента'; ?>" request_id="<?= $request->id; ?>">
							<td>
								<?php if ($request->send_type == 'auto') : ?>
									<i class="fa fa-font" title="Заявка была создана автоматически, без ведома пользователя"></i>
								<?php endif; ?>
							</td>							
							<!--<td><?= FormatText::dateInterval($request->created_at); ?></td>-->
							<td title="<?= $request->name; ?>">Клиент</td>
							<td><?= Html::a(FormatText::phone($request->phone), 'tel:+7'.$request->phone, ['class' => 'btn btn-xs phone-number']); ?></td>
							<td>
								<?php if ($request->landing) : ?>
									<a href="http://<?= $request->landing->domain ?>" target="_blank"><?= $request->landing->name ?></a>
								<?php else : ?>
									Просрочки по кредитам
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="pull-right request-pager js"><?= LinkPager::widget(['pagination' => $filter->getPagination()]); ?></div>
		</div>
	</div>
</div>
