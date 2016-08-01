<?php
namespace crm\modules\contracts\models;

use common\models\Request;
use yii\base\Model;

/**
 * Description of DiscardForm
 * Форма для отбраковывания заявки
 * @author anton2
 */
class DiscardForm extends Model {
	
	public $reason;
	
	private $_request;
	
	public function __construct($config = [], Request $request) {
		parent::__construct($config);
		$this->setRequest($request);
	}
			
	public function rules() {
		return [
			['reason', 'required'],
		];
	}
	
	public function attributeLabels() {
		return [
			'reason' => 'Укажите причину',
		];
	}
	
	public function save() {
		$this->getRequest()->reason = $this->reason;
		$this->getRequest()->status = Request::DISCARD;
		if (!$this->getRequest()->save()) {
			throw new \Exception('Не удалось сохранить причину отбраковки');
		}
		# устанавливаем статус 'discard' всем заявкам с тем же телефоном и uid
		if ($this->getRequest()->uid === '' || $this->getRequest()->uid === null) {
			db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::DISCARD."' WHERE r.phone = '" . $this->getRequest()->phone . "'")->execute();
		} else {
			db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::DISCARD."' WHERE r.uid = '" . $this->getRequest()->uid . "' AND r.phone = '" . $this->getRequest()->phone . "'")->execute(); // todo anton2 заменить OR на AND
		}
		
		return true;
	}
	
	public function getRequest() {
		return $this->_request;
	}
	
	public function setRequest(Request $request) {
		$this->_request = $request;
	}
}

?>
