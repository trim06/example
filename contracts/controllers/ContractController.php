<?php

/** Упаравление договорами */

namespace app\modules\contracts\controllers;


use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;
use common\components\ValidateException;
use common\models\Blocker;
use common\models\User;
use common\models\Contract;
use common\models\ContractLog;
use crm\modules\contracts\models\ContractForm;
use crm\modules\contracts\models\ContractFilterForm;
use crm\modules\contracts\models\FreeContractFilterForm;
use common\models\Task;
use common\models\Request;
use common\models\Service;
use common\models\ContractCredit;
use common\models\ContractService;
use crm\models\File;
use crm\models\Document;
use crm\models\ContractPayment;
use crm\modules\contracts\models\DeleteForm;
use crm\modules\contracts\models\FileForm;
use crm\modules\contracts\models\DocumentFileForm;
use crm\modules\contracts\models\ServiceForm;
use crm\modules\contracts\models\ContractPaymentForm;
use yii\helpers\ArrayHelper;
use crm\modules\payment\models\UnionPlat;
use crm\modules\payment\models\YandexRequest;


# Формы задач
use crm\modules\contracts\models\taskforms\MeetTaskForm;
use crm\modules\contracts\models\taskforms\GetPaymentTaskForm;
use crm\modules\contracts\models\taskforms\CallTaskForm;
use crm\modules\contracts\models\taskforms\AnalysisDocTaskForm;
use crm\modules\contracts\models\taskforms\RequestDocTaskForm;
use crm\modules\contracts\models\taskforms\CourtTaskForm;
use crm\modules\contracts\models\taskforms\CreateDocTaskForm;
use crm\modules\contracts\models\taskforms\SendDocTaskForm;

class ContractController extends BaseContractController {

	public function behaviors() {
		$behaviours = parent::behaviors();

		$rules = [
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return user()->can('view_contract_agreements');
				}
			]
		];
		$behaviours['access']['rules'] = array_merge($behaviours['access']['rules'], $rules);

		return $behaviours;
	}
	
	/**
	 * Список договоров
	 * @param str $route Маршрут для построения фильтра
	 * @return str
	 */
	public function actionIndex($route = NULL) {
		# по умолчанию показываем только договоры по данному пользователю
		if (!request()->isAjax && $route == null) {
			$route = "m0$".user()->identity->current_unit_id.".1$".user()->id/*."_sprocessing"*/;
		}
		$filter = new ContractFilterForm($route);
		return request()->isAjax ? $this->renderAjax('index', ['filter' => $filter]) : $this->render('index', ['filter' => $filter]);
	}
	
	/**
	 * Список свободных договоров
	 * @param str $route Маршрут для построения фильтра
	 * @return str
	 */
	public function actionFree($route = NULL) {
		$filter = new FreeContractFilterForm($route);
		return request()->isAjax ? $this->renderAjax('index', ['filter' => $filter]) : $this->render('index', ['filter' => $filter, 'freeFilter' => true]);
	}
	
	/**
	 * Список задач
	 * @return str
	 */
	public function actionTask($route = NULL) {
		$filter = new \crm\modules\contracts\models\TaskFilterForm($route);
		return request()->isAjax ? $this->renderAjax('tasks', ['filter' => $filter]) : $this->render('tasks', ['filter' => $filter]);
	}

	/**
	 * Создание договора
	 * @return str
	 */
	public function actionCreate() {
		$contractForm = new ContractForm([], new Contract());

		# если GET-запрос возвращаем форму
		if (request()->isGet) return $this->renderAjax('create', ['contractForm' => $contractForm]);

		$this->validateAndSaveForm($contractForm, request()->post(), 'saveContract');
		
		# возвращаем id договора
		return ['contract_id' => $contractForm->getContract()->primaryKey];
	}
	
	public function actionFastSearch($value, $type) {
		$contracts = Contract::find()->joinWith('abonent')->andWhere(['like', $type, $value])->all();
		return $this->renderAjax('list_by_phone', ['contracts' => $contracts]);
	}

	/**
	 * Карточка клиента
	 * @param type $id
	 * @return type
	 */
	public function actionCard($id, $action = NULL) {
		$contract = $this->getContractById($id);
		Blocker::isBlock('contract', $id);
		$cardVars = [];

		# если action null|false, то грузим все табы иначе загружаем выбранный
		if($action == 'client' || !$action) {
			$tasks = Task::find()->with(['contract.abonent'])->where(['contract_id' => $contract->id, 'status' => Task::STATUS_NEW])->orderBy('start_at ASC')->all();
			$cardVars['client']['tasks'] = $tasks;
			$cardVars['client']['contractForm'] = new ContractForm([], $contract);
			$cardVars['client']['contractServices'] = ContractService::find()->with('service')->andWhere(['contract_id' => $contract->id])->all();
		}
		if($action == 'files' || !$action) {
			//$cardVars['files']['documentFileForm'] = new DocumentFileForm([], new Document, $contract);
			$cardVars['files']['contractId'] = $contract->id;
			//$cardVars['files']['listDocument'] = $contract->documents;
			$cardVars['files']['listDocuments'] = \common\models\Document::find()->andWhere(['contract_id' => $contract->id])->all();
		}
		if($action == 'payments' || !$action) {
			$cardVars['payments']['listPayments'] = ContractPayment::find()->with('contractPaymentItems')->andWhere(['contract_id' => $contract->id, 'status' => ContractPayment::STATUS_PAID])->all();
		}
		if($action == 'history' || !$action) {
			$cardVars['history']['listHistory'] = ContractLog::find()->where(['contract_id' => $contract->id])->with('user', 'logAction')->orderBy('date DESC')->all();
		}
		if($action == 'services' || !$action) {
			$cardVars['services']['serviceForm'] = new ServiceForm([], new ContractService, $contract);
			$cardVars['services']['listContractService'] = ContractService::find()->with(['user', 'service', 'credit', 'paymentItems'])->andWhere(['contract_id' => $contract->id])->orderBy('date DESC')->all();
			if (count(ArrayHelper::getColumn($contract->services, 'id')) > 0) {
				$cardVars['services']['listService'] = Service::find()->indexBy('id')->andWhere(['status' => 'active', 'city_id' => $contract->city_id])->andWhere(['or', ['not in', 'id', ArrayHelper::getColumn($contract->services, 'id')], ['work_with_credit' => 'Y']])->all();
			} else {
				$cardVars['services']['listService'] = Service::find()->indexBy('id')->andWhere(['status' => 'active', 'city_id' => $contract->city_id])->all();
			}			
			$cardVars['services']['agreementForm'] = new \crm\modules\contracts\models\AgreementForm([], $contract);
			$cardVars['services']['contractStatus'] = $contract->status;
			$cardVars['services']['listContractCredit'] = ContractCredit::find()->andWhere(['contract_id' => $contract->id])->all();
		}
		if($action == 'credits' || !$action) {
			$cardVars['credits']['contract'] = $contract;
			$cardVars['credits']['listContractCredit'] = ContractCredit::find()->andWhere(['contract_id' => $contract->id])->all();
		}
		return $this->renderAjax('card', ['cardVars' => $cardVars], true);
	}
	
	/**
	 * Редактирование договора
	 * @param int $id		ID договора
	 * @param boolean $agreement Маркер, указывающий на применение сценария agreement
	 * @return str
	 * @throws HttpException
	 */
	public function actionEdit($id, $agreement = false) {
		$contract = $this->getContractById($id);
		$contractForm = new ContractForm([], $contract);
		if ($agreement) {
			$contractForm->setScenario('agreement');
		}
		$this->validateAndSaveForm($contractForm, request()->post(), 'saveContract');

		return [];
	}
	
	public function actionCreateFromRequest($requestId) {
		$request = Request::findOne($requestId);
		if(!$request) throw new ValidateException('Документ не найден', 404);
		$contractForm = new ContractForm([], new Contract);
		$contractForm->phone = $request->phone;
		if (request()->isGet) return $this->renderAjax('create_from_request', ['contractForm' => $contractForm, 'request' => $request]);
		
		$this->validateAndSaveForm($contractForm, request()->post(), 'saveContract');
		
		$request->status = Request::CREATED_CONTRACT;
		if(!$request->save()) throw new ValidateException('Проблема при сохранении заявки');

		# возвращаем id договора
		return ['contract_id' => $contractForm->getContract()->primaryKey];
	}

	/**
	 * Действие Назначение встречи
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskMeet($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new MeetTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == MeetTaskForm::LOG_MEET_END) $taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}		
		
		# если GET-запрос возвращаем форму
		if (request()->isGet) return $this->renderAjax('/_' . Task::TYPE_MEET, ['taskForm' => $taskForm, 'history' => $history], true);
		
		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');
		
		return [];
	}
	
	 /**
	  * Прием платежа по договору
	  * @param int $id ID контракта
	  * @param int $taskId ID задачи
	  */
	public function actionTaskGetPayment($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task();
		$taskForm = new GetPaymentTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == GetPaymentTaskForm::LOG_GET_PAYMENT_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}
		
		# если GET-запрос возвращаем форму
		if (request()->isGet) return $this->renderAjax ('/_' . Task::TYPE_GET_PAYMENT, ['taskForm' => $taskForm, 'history' => $history], true);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');
		
		return [];
	}

	/**
	 * Действие Перезвонить
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskCall($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new CallTaskForm([], $task, $contract);
				
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == CallTaskForm::LOG_CALL_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}
		
		if (request()->isGet) return $this->renderAjax('/_' . Task::TYPE_CALL, ['taskForm' => $taskForm, 'history' => $history], true);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие Анализ документов
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskAnalysisDoc($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new AnalysisDocTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == AnalysisDocTaskForm::LOG_ANALYSIS_DOC_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}
		
		if (request()->isGet) return $this->renderAjax('/_' . Task::TYPE_ANALYSIS_DOC, ['taskForm' => $taskForm, 'history' => $history]);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие Запрос документов
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskRequestDoc($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new RequestDocTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == RequestDocTaskForm::LOG_REQUEST_DOC_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}
		
		if (request()->isGet) return $this->renderAjax('/_' . Task::TYPE_REQUEST_DOC, ['taskForm' => $taskForm, 'history' => $history]);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие судебное дело
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskCourt($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new CourtTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == CourtTaskForm::LOG_COURT_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}

		if (request()->isGet) return $this->renderAjax('/_' . Task::TYPE_COURT, ['taskForm' => $taskForm, 'history' => $history]);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие формирование документов
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskCreateDoc($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new CreateDocTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == CreateDocTaskForm::LOG_CREATE_DOC_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}

		if (request()->isGet)
			return $this->renderAjax('/_' . Task::TYPE_CREATE_DOC, ['taskForm' => $taskForm, 'history' => $history]);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие отправка документов
	 * @param int $id
	 * @param int $taskId ID задачи
	 */
	public function actionTaskSendDoc($id, $taskId = NULL) {
		$contract = $this->getContractById($id);
		$task = $taskId ? Task::findOne($taskId) : new Task;
		$taskForm = new SendDocTaskForm([], $task, $contract);
		
		# задаем стандартный сценарий для формы
		$taskForm->setScenario('createTask');
		# загружаем данные в модель
		$taskForm->load(request()->post());
		# если это выполнение задачи, то меняем сценарий
		if ($taskForm->action_id == SendDocTaskForm::LOG_SEND_DOC_END)
			$taskForm->setScenario('completeTask');

		$history = [];
		if ($taskId) {
			# находим все логи, связанные с этой таской
			$history = ContractLog::find()->with('logAction')->andWhere(['task_id' => $taskForm->task_id])->orderBy('date DESC')->all();
		}

		if (request()->isGet)
			return $this->renderAjax('/_' . Task::TYPE_SEND_DOC, ['taskForm' => $taskForm, 'history' => $history]);

		# Сохраняем задачу
		$this->validateAndSaveForm($taskForm, request()->post(), 'saveTask');

		return [];
	}

	/**
	 * Действие удаление
	 * @param int $id
	 */
	public function actionDelete($id) {
		$contract = $this->getContractById($id);
		$deleteForm = new DeleteForm([], $contract);
		if (request()->isGet) return $this->renderAjax('/_delete', ['deleteForm' => $deleteForm]);

		# Сохраняем задачу
		$this->validateAndSaveForm($deleteForm, request()->post(), 'saveContract');

		return [];
	}
	
	/**
	 * Восстановление удаленного договора
	 * @param int $id ID договора
	 * @return
	 */
	public function actionRestore($id) {
		$contract = $this->getContractById($id);
		if (request()->isPost) {
			$contractForm = new ContractForm([], $contract);
			$contractForm->load(request()->post());
			$contractForm->status = Contract::STATUS_PROCESSING;
			$this->validateAndSaveForm($contractForm, [], 'saveContract');
			$contract->saveLog(ContractLog::LOG_RESTORE, null, null, 'Договор восстановлен пользователем '.user()->identity->name);
		}
		return [];
	}
	
	/**
	 * Действие оплата
	 * @param int $id
	 */
	public function actionPaid($id) {
		$contract = $this->getContractById($id);
		$paymentForm = new ContractPaymentForm([], new ContractPayment, $contract);
		$contractServices = ContractService::find()->with(['service', 'credit'])->andWhere(['contract_id' => $id])->all();
		if (request()->isGet) return $this->renderAjax('/_paid', ['paidForm' => $paymentForm, 'contractServices' => $contractServices]);
		
		# Сохраняем задачу
		$this->validateAndSaveForm($paymentForm, request()->post());
		if ($paymentForm->paymentType == ContractPayment::PAYMENT_TYPE_UNION_PLAT) {
			if (user()->id == 65501) {
				return YandexRequest::getParams($paymentForm->getContractPayment()->cost, $paymentForm->getContractPayment()->id,
					$paymentForm->getContractPayment()->contract_id, $paymentForm->paymentType, $paymentForm->getContractPayment()->cost);
			} else {
				return ['redirectUrl' => UnionPlat::url($paymentForm->getContractPayment(), app()->urlManager->createAbsoluteUrl('/'), app()->urlManager->createAbsoluteUrl('/'))];
			}
		}

		return [];
	}
	
	/**
	 * Действие договор
	 * @param int $id
	 */
	public function actionAgreement($id) {
		$contract = $this->getContractById($id);
		$agreementForm = new \crm\modules\contracts\models\AgreementForm([], $contract);
		$listContractCredit = ContractCredit::find()->andWhere(['contract_id' => $contract->id])->all();
		if (request()->isGet) return $this->renderAjax('/_agreement', ['agreementForm' => $agreementForm, 'listContractCredit' => $listContractCredit], true);
		# Сохраняем задачу
		$this->validateAndSaveForm($agreementForm, request()->post());
		
		if ($agreementForm->paymentType == ContractPayment::PAYMENT_TYPE_UNION_PLAT) {
			if (user()->id == 65501) {
				return YandexRequest::getParams($agreementForm->getContractPayment()->cost, $agreementForm->getContractPayment()->id,
					$agreementForm->getContractPayment()->contract_id, $agreementForm->paymentType, $agreementForm->getContractPayment()->cost);
			} else {
				return ['redirectUrl' => UnionPlat::url($agreementForm->getContractPayment(), app()->urlManager->createAbsoluteUrl('/'), app()->urlManager->createAbsoluteUrl('/'))];
			}
		}

		return [];
	}
	
	/**
	 * Загрузка файла
	 * @param type $id
	 * @return type
	 */
	public function actionUploadFile($documentId) {
		$folderFile = Document::findOne($documentId);
		if(!$folderFile) throw new ValidateException('Документ не найден', 404);
		
		$fileForm = new FileForm([], new File, $folderFile);
		$fileForm->load(request()->post());
		$fileForm->file = \yii\web\UploadedFile::getInstance($fileForm, 'file');
		$this->validateAndSaveForm($fileForm, []);
		return [];
	}
	
	/**
	 * Список файлов папки
	 * @param type $id
	 * @return type
	 */
	public function actionFiles($documentId) {
		$document = Document::findOne($documentId);
		if(!$document) throw new ValidateException('Документ не найден', 404);
		$listFile = File::find()->where(['document_id' => $documentId])->orderBy('date DESC')->all();
		$fileForm = new FileForm([], new File, $document);
		return $this->renderAjax('files', ['fileForm' => $fileForm,'listFile' => $listFile]);
	}
	
	/**
	 * Добавление услуги
	 * @param type $id
	 * @return type
	 */
	public function actionService($id) {
		$contract = $this->getContractById($id);
		$serviceForm = new ServiceForm([], new ContractService, $contract);
		
		$this->validateAndSaveForm($serviceForm, request()->post());
		return [];
	}


	/**
	 * Качаем файл резюме
	 * @param type $resumeId
	 * @return type
	 */
	public function actionDownload($fileId) {
		$contractFile = File::findOne($fileId);
		if ( ! $contractFile) return;
		
		header("Content-Disposition: attachment;filename=" . $contractFile->nameUser);
		echo file_get_contents(File::getPathUpload().$contractFile->name);
	}
	
	/**
	 * Создание папки для файлов
	 * @param type $id
	 * @return type
	 */
	public function actionCreateFolder($id) {
		$contract = $this->getContractById($id);
		$documentFileForm = new DocumentFileForm([], new Document, $contract);
		
		$this->validateAndSaveForm($documentFileForm, request()->post());
		return [];
	}
	
	/**
	 * Создает запись в лог по заданным ID договора и действия
	 * @param type $contract_id ID договора
	 * @param type $action_id ID действия
	 */
	public function actionSaveLog($contract_id, $action_id) {
		$contract = Contract::findOne(['id' => $contract_id]);
		$contract->saveLog($action_id);
	}
	
	public function actionSendAdvertSms($id) {
		$contract = Contract::find()->with('abonent')->andWhere(['id' => $id])->one();
		$abonent = $contract->abonent;
		# если абоненту уже отсылалась смс с рекламой, то игнорим
		if ($abonent->got_sms === 'Y') { return true; };
		# формируем текст смс и отправляем
		$workPhone = user()->identity->userSettings->work_phone;
		$phone = ($workPhone === '' || is_null($workPhone)) ? user()->identity->currentUnit->phone : $workPhone;
		$message = 'Избавление от кредитов и страховок. Консультация бесплатно 8'.$phone;
		app()->sms->send($abonent->phone, $message);
		# сохраняем лог
		$contract->saveLog(ContractLog::LOG_ADVERT_SMS, null, null, 'Отправка рекламного сообщения с текстом: "'.$message.'"');
		# сохраняем абонента
		$abonent->got_sms = 'Y';
		if (!$abonent->save(['got_sms'])) { throw new \Exception('Не удалось сохранить абонента'); };
	}
	
	public function actionVerifyPhone($phoneNumber) {
		$abonent = \common\models\Abonent::find()->andWhere(['phone' => $phoneNumber])
				->with(['contracts' => function($query){
					$query->andWhere(['in', 'unit_id', ArrayHelper::getColumn(user()->identity->userUnits, 'unit_id')]);
				}])->one();
		$contracts = [];
		if (isset($abonent->contracts) && count($abonent->contracts) > 0) {
			foreach ($abonent->contracts as $contract) {
				$contracts[] = $contract->id;
			}
			return je($contracts);
		} else {
			return false;
		}
	}

	/**
	 * Список активных пользователей
	 * @param int $id
	 */
	public function getListUsers() {
		return User::find()->active()->all();
	}

	/**
	 * Список активных пользователей из текущего офиса
	 * @param int $id
	 */
	public function getListUnitUsers() {
		return User::find()->active()->andWhere(['current_unit_id' => user()->identity->current_unit_id])->all();
	}

	/**
	 * Возвращает модель контракта по id
	 * return Contract
	 */
	public function getContractById($id) {
		$contract = Contract::findOne($id);
		if (!$contract) throw new ValidateException('Договор не найден', 404);
		return $contract;
	}
}
