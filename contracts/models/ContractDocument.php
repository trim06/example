<?php

namespace crm\modules\contracts\models;

use Yii;
use common\models\User;
use common\models\Contract;

/**
 * @property integer $document_id
 * @property integer $contract_id
 */
class ContractDocument extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contract_document}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contract_id', 'document_id'], 'required'],
            [['document_id', 'contract_id'], 'integer'],
        ];
    }
	
	/**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contract_id' => 'ID контракта',
            'document_id' => 'ID документа',
        ];
    }
}