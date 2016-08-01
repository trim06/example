<?php

/* Список фыайлов */

/* @var $this \yii\web\View */
/* @var $contractFile \crm\models\File */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
 
	<div class="row" style="margin: 20px 20px;">		
		<div class="piece-of-sheet">
			<?php $form = ActiveForm::begin(['id' => 'fileForm', 'action' => '/contracts/upload-file/'.$fileForm->getDocument()->primaryKey, 'enableClientValidation' => TRUE, 'fieldClass' => 'common\extensions\widgets\SmartActiveField', 'options' => ['class' => 'smart-form', 'data-id' => $fileForm->getDocument()->primaryKey, 'enctype' => 'multipart/form-data']]); ?>
				<div class="col-xs-12 col-sm-5"><?= $form->field($fileForm, 'file', ['enableLabel' => FALSE])->fileInput([]) ?></div>
				<div class="col-xs-12 col-sm-5"><?= $form->field($fileForm, 'desc', ['enableLabel' => FALSE])->textInput(['placeholder' => $fileForm->getAttributeLabel('desc')]) ?></div>
				<div class="col-xs-2">
					<?= Html::submitButton('Добавить', ['class' => 'btn btn-block btn-sm btn-primary']); ?>
				</div>
			<?php ActiveForm::end(); ?>
			<div class="col-xs-12" id="progress" style="display: none;">
				<div class="text-center">Загрузка файла <span id="persent">0%</span></div>
				<div class="progress">
					<div class="progress-bar progress-bar-success">
						<span class="sr-only">35% Complete (success)</span>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div id="message"></div>	
		</div>
		
	</div>
	<div style="margin: 20px;">
		<h3>Файлы документа</h3>
		<table class="table table-hover files-list js">
		<?php foreach($listFile as $contractFile): ?>
			<tr>
				<td><a href="/contracts/download/<?= $contractFile->id ?>"><?= $contractFile->desc ?></a></td>
				<td><small><?= FormatText::rusDate($contractFile->date, 'D, j F Y в H:i') ?></small></td>
				<td><small><?= $contractFile->user->name ?></small></td>
			</tr>
		<?php endforeach; ?>
		</table>
	</div>