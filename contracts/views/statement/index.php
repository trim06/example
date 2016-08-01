<?php
/**
 * Представление списка заявлений от клиентов
 */

use common\models\ContractStatement;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use common\models\UserSettings;




# регистрируем стили и скрипты 
$this->registerJsFile('/js/jquery/jquery.form.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerJsFile('/js/statement.js', ['depends' => 'yii\web\JqueryAsset']);

$statusIcons = [ContractStatement::STATUS_UNRESOLVED => 'fa fa-refresh', ContractStatement::STATUS_RESOLVED => 'fa fa-check'];

$this->title = 'Заявления от клиентов';
?>

<div class="contracts-wrapper">
	<div class="nav-header<?= (UserSettings::findOne(['user_id' => user()->id])->show_menu === 'Y') ? ' nav-header-minified"' : ''; ?>">

		<?php /** -------- <Навигация раздела>  -------- */ ?>
		<?= Nav::widget(['items' => $this->context->module->getMenuItems(), 'options' => ['class' => 'nav nav-tabs']]); ?>
	</div>
	
	<div class="row index-main-row">
		<?php /** -------- <Фильтр заявлений> -------- */ ?>
		<div class="col-xs-12 col-sm-4 col-sm-push-8 col-md-4 col-md-push-8 col-lg-4 col-lg-push-8 statement-filter js">
			<div class="piece-of-sheet filter-block" style="margin-top:20px;">
				<?= request()->isAjax ? '' : $this->render('filter', ['filter' =>$filter]); ?>			
			</div>
		</div>

		<?php /** -------- <Список заявлений> -------- */ ?>
		<div class="col-xs-12 col-sm-8 col-sm-pull-4 col-md-8 col-md-pull-4 col-lg-8 col-lg-pull-4 statement-list js" id="items-container-js">
			<div>
				<div style="float: left; margin-top: 15px;">Показано <?= count($filter->getItems()); ?> из <?= $filter->totalCount . ' ' . rus_plural($filter->totalCount, ['заявления', 'заявлений', 'заявлений']); ?></div>
			</div>
			<table class="table table-hover table-statement js">
				<thead>
					<tr>
						<th style="width: 50px;"></th>
						<th style="width: 300px;">ФИО</th>
						<th style="width: 200px;">Телефон</th>
						<th class="hidden-xs">В обработке</th>
					</tr>
				</thead>
				<tbody class="aside-list" aside-url="/contracts/statement/resolve/{statement_id}" aside-width="700">
					<?php foreach ($filter->getItems() as $statement): ?>
					<tr class="aside-item" aside-title="<?= $statement->abonent->name . ' №' . $statement->contract->id; ?>" statement_id="<?= $statement->primaryKey; ?>">
						<td><i title="<?= ContractStatement::getStatusList($statement->status); ?>" class="<?= ag($statusIcons, $statement->status); ?>"></i></td>
						<td><?= $statement->abonent->name; ?></td>
						<td><?= Html::a(FormatText::phone($statement->abonent->phone), 'tel:+7' . $statement->abonent->phone, ['class' => 'btn btn-xs phone-number']); ?></td>
						<td class="hidden-xs">
							<a href="#" class="<?= $statement->getProcessingLimitClass(); ?>">
								<?php if ($statement->status === 'N') : ?>
									В обработке <?= FormatText::dateInterval($statement->created_at, false); ?>
								<?php else : ?>
									Решение по заявлению вынесено
								<?php endif; ?>
							</a>
						</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="pull-right statement-pager js"><?= LinkPager::widget(['pagination' => $filter->getPagination()]); ?></div>
		</div>
	</div>
</div>