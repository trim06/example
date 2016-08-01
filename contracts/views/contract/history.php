<?php

/* История действий */

/* @var $this \yii\web\View */
/* @var $listHistory \common\models\ContractLog[] */

use common\components\ListHelper;
?>
<?php if (count($listHistory) > 0) : ?>
<div class="col-md-12 history-list js">
	<div style="padding-left: 0;">
		<?php foreach ($listHistory as $contractLog): ?>
			<div class="item-in-list">
				<div class="history-info">
					<div class="history-icon"><i class="fa <?= ListHelper::logActionIcon($contractLog->action_id) ?> fa-2x"></i></div>
				</div>
				<div class="history-content">
					<div class="history-title">
						<span><?= $contractLog->logAction->name; ?></span>
					</div>
					<div class="history-comment"><?= $contractLog->comment; ?></div>
					<div class="history-author"><?= $contractLog->user->name; ?> <?= FormatText::rusDate($contractLog->date, 'd F Y H:i:s'); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>