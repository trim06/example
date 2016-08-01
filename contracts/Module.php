<?php

/** Модуль для работы с договорами */

namespace app\modules\contracts;

class Module extends \yii\base\Module {

    public function init() {
        parent::init();
    }
	
	/**
	 * Возвращает пункты меню модуля
	 * @return array
	 */
	public function getMenuItems() {
		return \Yii::$app->menu->getChildMenuFor('Заявки');
	}

}
