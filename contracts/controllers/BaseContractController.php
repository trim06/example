<?php

namespace app\modules\contracts\controllers;

use common\components\BaseController;
use yii\filters\AccessControl;

class BaseContractController extends BaseController {
	public function behaviors() {
		$behaviours =  [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => false,
						'matchCallback' => function ($rule, $action) {
							return !user()->can('view_contract_module');
						}
					],
				]
			]
		];

		return array_merge(parent::behaviors(), $behaviours);
	}
}