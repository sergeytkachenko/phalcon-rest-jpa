<?

namespace PPA\Rest\Model;

use Phalcon\Di\Injectable;
use PPA\Rest\Acl\CrudOperations;
use PPA\Rest\Exception;
use PPA\Rest\PpaController;

class RelationManager extends Injectable {
	/**
	 * @var \PPA\Rest\Acl\Security
	 */
	private $securityManager;
	/**
	 * @var \PPA\Rest\Log\Manager
	 */
	private $logManager;

	/**
	 * Relation constructor.
	 */
	public function __construct() {
		$di = $this->getDI();
		/**
		 * @var \PPA\Rest\PpaController $ppaController
		 */
		$ppaController = $di->get(PpaController::SERVICE_NAME);
		$this->securityManager = $ppaController->getSecurityManager();
		$this->logManager = $ppaController->getLogManager();
	}

	/**
	 * @param array $relationsArray
	 * @param \Phalcon\Mvc\Model $model
	 * @param string $relationName
	 * @return \Phalcon\Mvc\Model\ResultsetInterface
	 */
	private function getNeedDelete(array $relationsArray, $model, $relationName) {
		foreach ($relationsArray as $requestRelation) {
			if (empty($requestRelation['id'])) {continue;}
			$id = $requestRelation['id'];
			$idList[] = $id;
		}
		$relation = $model->$relationName;
		/**
		 * @var \Phalcon\Mvc\Model\Criteria $q
		 */
		$q = $relation::query();
		$q->notInWhere('id', $idList);
		return $q->execute();
	}

	/**
	 * @param \Phalcon\Mvc\Model\ResultsetInterface $relations
	 * @param array $errors
	 * @return array $errors
	 */
	private function delete($relations, $errors = array()) {
		/**
		 * @var \Phalcon\Mvc\Model\Resultset $relation
		 */
		foreach ($relations as $relation) {
			$this->securityManager->check(array(
				'model' => $relation,
				'action' => CrudOperations::DELETE,
				'modelName' => get_class($relation)
			));
			if(!$relation->delete()) {
				$messages = $relation->getMessages();
				$errors[] = implode(". ", $messages);
			} else {
				$this->logManager->deleteModel($relation);
			};
		}
		return $errors;
	}

	/**
	 * @param array $relationData
	 * @param \Phalcon\Mvc\Model $model
	 * @param string $relationName
	 * @return mixed
	 */
	private function create(array $relationData, $model, $relationName) {
		$modelRelation = $model->getModelsManager()->getRelationByAlias(get_class($model), $relationName);
		$relationModel = new $modelRelation();
		$relationModel->assign($relationData);
		$this->securityManager->check(array(
			'model' => $relationModel,
			'action' => CrudOperations::CREATE,
			'modelName' => get_class($relationModel),
			'params' => $relationData
		));
		$result = $relationModel->save();
		if ($result) {
			$this->logManager->createModel($relationModel);
		}
		return $result;
	}

	/**
	 * @param array $relationData
	 * @param \Phalcon\Mvc\Model $model
	 * @param string $relationName
	 * @return mixed
	 * @throws Exception
	 */
	private function update(array $relationData, $model, $relationName) {
		if (empty($relationData['id'])) {
			throw new Exception('relation "' . $relationName . '" have need id for update operation.');
		}
		$id = intval($relationData['id']);
		$modelRelationClass = $model->getModelsManager()->getRelationByAlias(get_class($model), $relationName);
		$modelRelation = $modelRelationClass::findById($id);
		$modelRelation->assign($relationData);
		$this->securityManager->check(array(
			'model' => $modelRelation,
			'action' => CrudOperations::CREATE,
			'modelName' => get_class($modelRelation),
			'params' => $relationData
		));
		$this->logManager->setOldModel($modelRelation);
		$result = $model->save();
		if ($result) {
			$this->logManager->updateModel($modelRelation);
		}
		return $result;
	}

	/**
	 * @param array $relationData
	 * @param \Phalcon\Mvc\Model $model
	 * @param string $relationName
	 */
	public function save(array $relationData, $model, $relationName) {
		$needDeleteRelations = $this->getNeedDelete($relationData, $model, $relationName);
		$this->delete($needDeleteRelations);
		if (empty($requestRelation['id'])) {
			$this->create($relationData, $model, $relationName);
		} else {
			$this->update($relationData, $model, $relationName);
		}
	}

}