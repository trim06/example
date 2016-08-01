<?php
namespace app\modules\contracts\controllers;

use common\models\ContractCredit;
use common\models\Contract;
use crm\modules\contracts\models\ContractCreditForm;
use yii\base\Exception;
use yii\filters\AccessControl;

/**
 * Работа с кредитами во вкладке клиента
 */
class CreditController extends BaseContractController {
	public function behaviors() {
		$behaviours = parent::behaviors();

		$rules = [
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return user()->can('view_contract_credits');
				}
			]
		];
		$behaviours['access']['rules'] = array_merge($behaviours['access']['rules'], $rules);

		return $behaviours;
	}
	
	/**
	 * Создание кредита для контракта
	 * @param int $contract_id ID контракта, по которому создается кредит
	 * @return array пустой массив в случае успешного завершения
	 */
	public function actionCreate($contract_id = null) {
		$contract = Contract::findOne(['id' => $contract_id]);
		$contractCreditForm = new ContractCreditForm([], new ContractCredit(), $contract);
		
		if (request()->isPost) {
			return $this->validateAndSaveForm($contractCreditForm, request()->post());
		}
		
		return $this->renderAjax('create', ['contractCreditForm' => $contractCreditForm]);
	}
	
	/**
	 * Редактирование кредита с заданным ID
	 * @param type $id
	 */
	public function actionEdit($id) {
		$contractCredit = ContractCredit::findOne(['id' => $id]);
		$contract = Contract::findOne(['id' => $contractCredit->contract_id]);
		$contractCreditForm = new ContractCreditForm([], $contractCredit, $contract);
		
		if (request()->isPost) {
			return $this->validateAndSaveForm($contractCreditForm, request()->post());
		}
		
		return $this->renderAjax('edit', ['contractCreditForm' => $contractCreditForm]);
	}
	
	/**
	 * Удаляет кредит по заданному ID
	 * @param int $id
	 * @return boolean
	 * @throws Exception
	 */
	public function actionDelete($id) {
		if (!ContractCredit::deleteAll(['id' => $id])) throw new Exception("Не удалось удалить кредит, либо он уже удален");
		return true;
	}
	
}
