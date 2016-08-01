<?php
use crm\models\ContractPayment;
?>


<div style="margin: 20px;">
	<table class="table table-hover services-list js">
		<thead>
			<tr>
				<th>Дата оплаты</th>
				<th>Способ оплаты</th>
				<th>Комментарий</th>
				<th>Сумма</th>
			</tr>
		</thead>
		<?php $sum = 0; ?>
		<?php foreach ($listPayments as $payment): ?>
			<tr <?php if (count($payment->contractPaymentItems) > 0) { echo "onclick=\"contracts.toggleContractPaymentItems(this);\" style=\"cursor:pointer;\""; } ?> >
				<td class="col-md-4"><?= FormatText::rusDate($payment->date, 'D, j F Y в H:i') ?></td>
				<td class="col-md-3">
					<?php switch ($payment->payment_type):
						case ContractPayment::PAYMENT_TYPE_CASH: ?> Наличные
						<?php break; ?>
						<?php case ContractPayment::PAYMENT_TYPE_TRANSFER: ?> Перевод на расчетный счет
						<?php break; ?>
						<?php case ContractPayment::PAYMENT_TYPE_UNION_PLAT: ?> Банковская карта
						<?php break; ?>
					<?php endswitch ?>
				</td>
				<td class="col-md-3"><?= $payment->comment; ?></td>
				<td class="col-md-2"><?= $payment->cost; ?> руб.</td>
				<?php $sum = $sum + $payment->cost; ?>
			</tr>
			<?php if (count($payment->contractPaymentItems) > 0) : ?>
				<?php foreach ($payment->contractPaymentItems as $item) : ?>
					<?php if (($item->cost > 0) && (!is_null($item->contract_service_id))) : ?>
						<tr class="contract-payment-item-js" style="background-color:lightgrey; display:none;">
							<td class="col-md-10" colspan="3"><i class="fa fa-level-up fa-rotate-90"></i>&nbsp; <?= $item->contractService ? $item->contractService->title : ''; ?></td>
							<td class=""><?= $item->cost; ?> руб.</td>
						</tr>					
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<tr>
			<th colspan="3">Итого:</th>
			<td><?= $sum; ?> руб.</td>
		</tr>
	</table>
</div>