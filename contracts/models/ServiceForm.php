<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\Contract;
use common\models\ContractService;

class ServiceForm extends \yii\base\Model {
	
	public $cost;
	
	private $_contract;
	private $_contractService;
	
	public function __construct($config = [], ContractService $contractService, Contract $contract) {
		parent::__construct($config);

		$this->setContract($contract);
		$this->setContractService($contractService);
	}
	
	public function rules() {
		return  [
			[['cost'], 'required'],
			[['cost'], 'integer'],
		];
	}

	public function attributeLabels() {
		return [
			'cost' => 'Цена',
		];
	}
	
	public function setContract($contract) {
		$this->_contract = $contract;
	}
	
	/**
	 * 
	 * @return Contract
	 */
	public function getContract() {
		return $this->_contract;
	}
	
	public function setContractService($contractService) {
		$this->_contractService = $contractService;
	}
	
	/**
	 * 
	 * @return ContractService
	 */
	public function getContractService() {
		return $this->_contractService;
	}
	
	public function save() {
		$this->getContractService()->cost = $this->cost;
		$this->getContractService()->user_id = user()->id;
		$this->getContractService()->contract_id = $this->getContract()->id;
		if($this->getContractService()->save()) {
			return TRUE;
		}
		return FALSE;
	}

}
