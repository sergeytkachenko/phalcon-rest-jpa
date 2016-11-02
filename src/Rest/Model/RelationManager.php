<?

namespace PPA\Rest\Model;

use Phalcon\Di\Injectable;
use PPA\Rest\Acl\CrudOperations;
use PPA\Rest\Exception;
use PPA\Rest\PpaController;
use PPA\Rest\Utils\Params;

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
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 * @return \Phalcon\Mvc\Model\ResultsetInterface
	 */
	private function getNeedDelete(array $relationsArray, $model, $relation) {
		$modelRelation = $relation->getReferencedModel();
		$referencedField = $relation->getReferencedFields();
		$relationsArrayIdList = $this->getRelationsArrayIdList($relationsArray);
		/**
		 * @var \Phalcon\Mvc\Model\Criteria $q
		 */
		$q = $modelRelation::query();
		if (!empty($relationsArrayIdList)) {
			$q->notInWhere('id', $relationsArrayIdList);
		}
		$q->andWhere($referencedField . ' = :referencedField:', array('referencedField' => $model->id));
		$result = $q->execute();
		return $result;
	}

	/**
	 * @param array $relationsArray
	 * @param array $idList
	 * @return array
	 */
	private function getRelationsArrayIdList(array $relationsArray, $idList = array()) {
		foreach ($relationsArray as $requestRelation) {
			if (empty($requestRelation['id'])) {continue;}
			$id = $requestRelation['id'];
			$idList[] = $id;
		}
		return $idList;
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
		$referencedModel = $modelRelation->getReferencedModel();
		$referencedField = $modelRelation->getReferencedFields();
		$relationModel = new $referencedModel();
		$relationData[$referencedField] = $model->id;
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
		$relation = $model->getModelsManager()->getRelationByAlias(get_class($model), $relationName);
		$relationClass = $relation->getReferencedModel();
		$modelRelation = $relationClass::findFirstById($id);
		if (!$modelRelation) {
			throw new Exception($relationClass . '::findFirstById, with id=' . $id . ' not found');
		}
		$modelRelation->assign($relationData);
		$this->securityManager->check(array(
			'model' => $modelRelation,
			'action' => CrudOperations::CREATE,
			'modelName' => get_class($modelRelation),
			'params' => $relationData
		));
		$this->logManager->setOldModel($modelRelation);
		$result = $modelRelation->save();
		if ($result) {
			$this->logManager->updateModel($modelRelation);
		}
		return $result;
	}

	/**
	 * @param array $relationsArray
	 * @param \Phalcon\Mvc\Model $model
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 */
	public function save(array $relationsArray, $model, $relation) {
		$relationAlias = $relation->getOption('alias');
		$needDeleteRelations = $this->getNeedDelete($relationsArray, $model, $relation);
		$this->delete($needDeleteRelations);
		foreach ($relationsArray as $relationData) {
			$relationData = Params::convertDate($relationData, $this->getDI());
			if (empty($relationData['id'])) {
				$this->create($relationData, $model, $relationAlias);
			} else {
				$this->update($relationData, $model, $relationAlias);
			}
		}
	}

}