<?php
/**
 * @var common\models\DocumentPattern $patterns Шаблоны документов
 */
?>
<div class="drive-loader">
	<h3>Подождите, идет загрузка...</h3>
</div>
<div style="margin-top:20px;">
	<div class="col-sm-12">
		<h4 class="group-delimeter">Основные шаблоны</h4>
		<div class="row document-action-list" data-contract_id="<?= $contractId; ?>">
			<div class="col-md-3 col-xs-4 pattern-shortcut">
				<a href="" onclick="main.documents.showDriveLoader(); main.documents.newDocument(this); return false;">
					<div class="document-icon document-icon-mini document-icon-document"></div>
					<div>Пустой документ</div>
				</a>
			</div>
		</div>
		<h4 class="group-delimeter">Шаблоны договоров</h4>
		<div class="row document-action-list" data-contract_id="<?= $contractId; ?>">
			<?php foreach ($patterns as $pattern) : ?>
			<div class="col-md-3 col-xs-4 pattern-shortcut">
				<a href="" data-pattern_id="<?=$pattern->gd_id?>" onclick="main.documents.showDriveLoader(); main.documents.newDocByPattern(this); return false;">
					<div class="document-icon document-icon-mini document-icon-document"></div>
					<div><?=$pattern->name;?></div>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>	
</div>