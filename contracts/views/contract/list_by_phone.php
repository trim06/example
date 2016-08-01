<?php
/** Список договоров, удовлетворяющих условиям быстрого поиска
 * @var array $contracts Массив объектов Contract для списка
 */
?>
<div class="contracts-list-by-phone">
	<?php foreach($contracts as $contract) : ?>
		<div class="item-in-list clickable aside-ajax" aside-url="/contracts/card/<?= $contract->id ?>">
			<a class="abonent-phone btn btn-xs phone-number" href="tel:+7<?=$contract->abonent->phone; ?>"><?= FormatText::phone($contract->abonent->phone); ?></a>
			<div class="contract-id">Договор №<?= $contract->id; ?></div>
			<div class="abonent-name"><?=$contract->abonent->name; ?></div>
		</div>
	<?php endforeach; ?>
</div>
