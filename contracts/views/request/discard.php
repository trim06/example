<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<div class="row piece-of-sheet" style="margin: 20px;">
	<?php $form = ActiveForm::begin(['id' => 'discardForm', 'action' => '/contracts/request/discard/' . $discardForm->getRequest()->id, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form']]) ?>
	<div class="">		
		<?= $form->field($discardForm, 'reason')->textarea(['rows' => 10]); ?>
	</div>
	<div class="inner-btn">
		<?= Html::submitButton('OK', ['class' => 'btn btn-block btn-sm btn-warning', 'title' => 'Пометить заявку как брак', 'onclick' => 'request.discard(this); return false;']); ?>
	</div>
	<?php ActiveForm::end();  ?>
	
</div>

