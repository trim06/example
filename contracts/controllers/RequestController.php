<?php

namespace app\modules\contracts\controllers;

use yii\filters\AccessControl;
use common\models\Blocker;
use common\models\Contract;
use crm\modules\contracts\models\ContractForm;
use common\models\Request;
use common\models\Unit;
use common\components\ValidateException;
use crm\modules\contracts\models\DiscardForm;

class RequestController extends BaseContractController {

	public function behaviors() {
		$behaviours = parent::behaviors();

		$rules = [
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return user()->can('view_contract_requests');
				}
			]
		];
		$behaviours['access']['rules'] = array_merge($behaviours['access']['rules'], $rules);

		return $behaviours;
	}

	/**
	 * Список заявок
	 * @param str $route	Маршрут для построения фильтра
	 * @return str
	 */
	public function actionIndex($route = NULL) {
		# по умолчанию показываем только новые заявки с посадочных по данному городу, добавленные вручную
		if (!request()->isAjax && $route == null) {
			$route = "snew_c".Unit::find()->currentByUser()->one()->city_id."_ulanding_tuser";
		}

		$filter = new \crm\modules\contracts\models\RequestFilterForm($route);
		return request()->isAjax ? $this->renderAjax('index', ['filter' => $filter]) : $this->render('index', ['filter' => $filter]);
	}

	/**
	 * Создание договора из заявки
	 * @return str
	 */
	public function actionCreateContract($requestId) {
		$request = $this->getRequestById($requestId);
		Blocker::isBlock('request', $requestId);

		# создем новый договор по заявке
		$contractForm = new ContractForm([], new Contract());
		$contractForm->setRequest($request);

		# если GET-запрос возвращаем форму
		if (request()->isGet) {
			return $this->renderAjax('create', ['contractForm' => $contractForm, 'request' => $request]);
		}

		$this->validateAndSaveForm($contractForm, request()->post(), 'saveContractOnRequest');

		# возвращаем id договора
		return ['contract_id' => $contractForm->getContract()->primaryKey];
	}

	/**
	 * Удаление заявки
	 * @return str
	 */
	public function actionDelete($requestId) {
		$request = $this->getRequestById($requestId);
		$request->status = Request::DELETED_REQUEST;
		if ($request->save()) {
			if ($request->uid === '' || $request->uid === null) {
				db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::DELETED_REQUEST."' WHERE r.phone = '".$request->phone."'")->execute();
			} else {
				db()->createCommand("UPDATE request AS r SET r.`status` = '".Request::DELETED_REQUEST."' WHERE r.uid = '" . $request->uid . "' AND r.phone = '" . $request->phone . "'")->execute();
			}
		}
	}

	/**
	 * return Contract
	 */
	public function getRequestById($id) {
		$request = Request::find()->andWhere(['id' => $id, 'status' => ['new', 'discard', 'checked']])->one();
		if (!$request) {
			throw new ValidateException('Заявка не найдена или уже обработана', 404);
		}
		return $request;
	}
	
	public function actionDiscard($id) {
		$request = Request::findOne(['id' => $id]);
		$discardForm = new DiscardForm([], $request);
		
		if (request()->isGet) {
			return $this->renderAjax('discard', ['discardForm' => $discardForm]);
		}
		
		$this->validateAndSaveForm($discardForm, request()->post());
		return [];
	}

}
