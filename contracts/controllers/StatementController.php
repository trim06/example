<?php
namespace app\modules\contracts\controllers;
/**
 * Description of StatementController
 *
 * @author anton2
 */

use common\models\Contract;
use common\models\ContractStatement;
use crm\modules\contracts\models\StatementFilterForm;
use crm\modules\contracts\models\StatementForm;
use yii\web\HttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

class StatementController extends BaseContractController {
	public function behaviors() {
		$behaviours = parent::behaviors();

		$rules = [
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return user()->can('view_contract_statements');
				}
			]
		];
		$behaviours['access']['rules'] = array_merge($behaviours['access']['rules'], $rules);

		return $behaviours;
	}
	
	# Страница со списком всех заявлений
	public function actionIndex($route = null) {
		# по умолчанию показываем заявления, по которым не вынесено решения
		if (!request()->isAjax && $route == null) {
			$route = "sN";
		}
		
		$filter = new StatementFilterForm($route);
		return request()->isAjax ? $this->renderAjax('index', ['filter' => $filter]) : $this->render('index', ['filter' => $filter]);
	}
	
	/**
	 * Создание заявления для контракта
	 * @param int $id ID контракта, по которому создается заявление
	 * @return array пустой массив в случае успешного завершения
	 */
	public function actionCreate($id) {
		$contract = Contract::findOne(['id' => $id]);
		$statementForm = new StatementForm([], new ContractStatement, $contract);
		if (request()->isGet) {
			return $this->renderAjax('create', ['statementForm' => $statementForm]);
		}
		if (request()->isPost) {
			$statementForm->load(request()->post());
			$statementForm->file = UploadedFile::getInstances($statementForm, 'file');
		}
		$this->validateAndSaveForm($statementForm, []);
		return [];
	}
	
	public function actionResolve($id) {
		$statement = ContractStatement::find()->with('contract', 'abonent')->andWhere(['id' => $id])->one();
		if (is_null($statement)) {
			throw new HttpException(404, 'Заявление не найдено');
		}
		$contract = $statement->contract;
		$statementForm = new StatementForm([], $statement, $contract);		
		if (request()->isGet) {
			return request()->isAjax ? $this->renderAjax('resolve', ['statementForm' => $statementForm]) : $this->render('resolve', ['statement' => $statement]);
		}
		$statementForm->setScenario('resolve');
		$this->validateAndSaveForm($statementForm, request()->post(), 'resolve');
		return true;
	}
	
	/**
	 * Выдает файл для скачивания
	 * @param int $statementId ID заявления
	 * @param string $fileName Имя файла
	 */
	public function actionDownloadFile($statementId, $fileName) {
		$statement = ContractStatement::findOne($statementId);
		if (file_exists($statement->getPath().$fileName)) {
			$content = file_get_contents($statement->getPath().$fileName);
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$fileName.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize($statement->getPath().$fileName));
			echo $content;
		}
	}
}

?>
