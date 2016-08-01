<?php

use yii\helpers\Html;
?>

<div class="piece-of-sheet">
	<div class="pull-right">
		<?= Html::submitButton('Добавить кредит', ['class' => 'btn btn-block btn-sm btn-primary aside-ajax', 'aside-url' => '/contracts/credit/create/'.$contract->id]); ?>
	</div>
</div>
<div style="margin: 15px;">	
	
	<?php /** -------- <Список кредитов> -------- */ ?>
	<ol class="aside-list credit-list js pull-left" aside-url="/contracts/credit/{credit_id}" aside-width="700">
		<?php foreach ($listContractCredit as $contractCredit): ?>
			<li class="credit-item">
				<a href="#" class="aside-item" aside-title="Кредитный договор <?= $contractCredit->loan_agreement ?>" credit_id="<?= $contractCredit->primaryKey; ?>">
					<?= $contractCredit->creditor_name.' - '.$contractCredit->debt_amount.' рублей' ?>
				</a>
				<div class="credit-item-number"><?= $contractCredit->loan_agreement ?></div>
			</li>
		<?php endforeach; ?>
	</ol>
	
	<div class="clearfix"></div>
</div>
