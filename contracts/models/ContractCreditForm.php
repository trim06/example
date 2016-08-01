<?php
namespace crm\modules\contracts\models;

use common\models\ContractCredit;
/**
 * Description of ContractCreditForm
 *
 * @author anton2
 */
class ContractCreditForm extends \yii\base\Model {
	
	public $creditor_name;
	public $loan_agreement;
	public $debt_amount;
	public $month_payment;
	
	private $_contractCredit;
	private $_contract;
	private $_listContractCredit;


	public function __construct($config = [], $contractCredit, $contract) {
		parent::__construct($config);
		$this->setContractCredit($contractCredit);
		$this->_contract = $contract;
	}


	
	public function rules() {
		return [
			[['creditor_name', 'loan_agreement'], 'string'],
			[['debt_amount', 'month_payment'], 'integer'],
			['creditor_name', 'required'],
		];
	}
	
	public function attributeLabels() {
		return parent::attributeLabels() + [
			'creditor_name' => 'Название кредитной организации',
			'loan_agreement' => 'Номер и дата кредитного договора',
			'debt_amount' => 'Сумма долга',
			'month_payment' => 'Ежемесячный платеж',
		];
	}

	/* Геттеры и сеттеры */
	
	public function getContractCredit() {
		return $this->_contractCredit;
	}

	public function setContractCredit($contractCredit) {
		$this->_contractCredit = $contractCredit;
		$this->creditor_name = $contractCredit->creditor_name;
		$this->loan_agreement = $contractCredit->loan_agreement;
		$this->debt_amount = $contractCredit->debt_amount;
		$this->month_payment = $contractCredit->month_payment;
	}
	
	public function getContract() {
		return $this->_contract;
	}
	
	public function setContract($contract) {
		$this->_contract = $contract;
	}
	
	
	/**
	 * Сохраняет кредит контракта
	 * @return boolean
	 * @throws \common\components\ValidateException
	 */
	public function save() {
		$this->_contractCredit->creditor_name = $this->creditor_name;
		$this->_contractCredit->loan_agreement = $this->loan_agreement;
		$this->_contractCredit->debt_amount = $this->debt_amount;
		$this->_contractCredit->month_payment = $this->month_payment;
		$this->_contractCredit->contract_id = $this->getContract()->id;
		if (!$this->_contractCredit->save()) {
			throw new \common\components\ValidateException('Не удалось сохранить кредит');
		}
		return true;
	}
	
	/**
	 * Возвращает список кредитных договоров по данному договору
	 * @return array
	 */
	public function getListContractCredit() {
		if (!$this->_listContractCredit) {
			$list = ContractCredit::find()->where(['contract_id' => $this->_contract->id])->all();
			$this->_listContractCredit = $list ? $list : [new ContractCredit];
		}
		return $this->_listContractCredit;
	}
	
}

?>
