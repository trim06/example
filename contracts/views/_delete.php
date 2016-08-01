<?php

/** Форма назначения встречи */
/* @var $this \yii\web\View */
/* @var $deleteForm crm\modules\contracts\models\DeleteForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<div class="row" style="margin: 20px 0;">
	<?php $form = ActiveForm::begin(['id' => 'meetForm', 'action' => '/contracts/delete/'.$deleteForm->getContract()->primaryKey, 'enableClientValidation' => FALSE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $deleteForm->getContract()->primaryKey]]); ?>
	<div class="col-xs-9 col-sm-10">
		<div class="row">
			<div class="col-xs-12">
				<?= $form->field($deleteForm, 'comment')->textarea() ?>
			</div>
		</div>
	</div>
	<div class="col-xs-2">
		<?= Html::submitButton('Сохранить', ['class' => 'btn btn-block btn-sm btn-primary', 'onclick' => 'return contracts.save(this);']); ?>
	</div>
	<?php ActiveForm::end(); ?>
</div>