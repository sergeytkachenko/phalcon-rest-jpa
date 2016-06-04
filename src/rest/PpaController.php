<?
namespace PPA\Rest;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\ResultsetInterface;
use PPA\Rest\Acl\CrudOperations;
use PPA\Rest\Acl\CheckerAccessLevel;
use PPA\Rest\Acl\Level\AllowedLevel;
use PPA\Rest\Acl\Level\DeniedLevel;
use PPA\Rest\Acl\Security;
use PPA\Rest\Url\Analyzer;
use PPA\Rest\Url\Operators;
use PPA\Rest\Utils\Params;

class PpaController extends JsonController
{
	/**
	 * @var \PPA\Rest\Acl\Security
	 */
	private $security;

	/**
	 * @return array
	 */
	public function crudAction() {
		try {
			$url = $this->request->get('_url');
			if (!$url) {
				throw new \PPA\Rest\Exception('_url is mast specific');
			}
			if (Analyzer::isSaving($url)) {
				return $this->save();
			}
			if (Analyzer::isDeleting($url)) {
				return $this->delete();
			}
			$params = Params::getMergeParams($this->request);
			$query = Operators::buildQuery($url, $params);
			$data = $query->execute();
			$this->security->check(array(
				'model' => $data,
				'action' => CrudOperations::READ,
				'modelName' => Analyzer::getModelName($url),
				'params' => $params
			));
			if (!$data) {return array();}
			return $this->getFinallyFullData($data, $params, $url);
		} catch (\PPA\Rest\Acl\Exception $e) {
			$this->response->setStatusCode(401);
			return array(
				'msg' => $e->getMessage(),
				'url' => $url
			);
		} catch (\PPA\Rest\Exception $e) {
			$this->response->setStatusCode(500);
			return array(
				'msg' => $e->getMessage(),
				'trace' => $e->getTrace()
			);
		}
	}

	/**
	 * @param ResultsetInterface $data
	 * @param array $params
	 * @param $url
	 * @return array
	 */
	private function getFinallyFullData(ResultsetInterface $data, array $params, $url) {
		$isOnlyFirst = !Analyzer::hasMany($url);
		$isFetchRelations = \PPA\Rest\Request\Params::isNeedFetchRelations($params);
		$isJoinedRelations = \PPA\Rest\Request\Params::isNeedJoinedRelations($params);
		$needleRelations = empty($params['fetchRelations']) ? array() : (array)$params['fetchRelations'];
		if ($isOnlyFirst) {
			/**
			 * @var \Phalcon\Mvc\Model $data
			 */
			$data = $data->getFirst();
			if (!$data) {return array();}
			if ($isFetchRelations) {
				return $data->fetchRelations(true, $needleRelations)->toArrayRelations();
			}
			if ($isJoinedRelations) {
				return $data->joinedRelations()->toArrayRelations();
			}
			return $data->toArray();
		}
		return $data->filter(function($model) use ($isFetchRelations, $isJoinedRelations, $needleRelations) {
			if ($isFetchRelations) {
				return $model->fetchRelations(true, $needleRelations);
			}
			if ($isJoinedRelations) {
				return $model->joinedRelations();
			}
			return $model;
		});
	}

	/**
	 * Save and create record to DB.
	 * @return array
	 */
	private function save() {
		$params = Params::getMergeParams($this->request);
		$id = intval(@$params['id']);
		$modelName = Analyzer::getModelName($this->request->get('_url'));
		if ($id) {
			/**
			 * @var \Phalcon\Mvc\Model $model
			 */
			$model = $modelName::findFirst($id);
			if (!$model) {
				return array(
					'success' => false,
					'msg' => 'Record with id '. $id .' not found'
				);
			}
			$model->assign($params);
			$this->security->check(array(
				'model' => $model,
				'action' => CrudOperations::UPDATE,
				'modelName' => $modelName,
				'params' => $params
			));
			if ($model->save()) {
				$errors = $this->saveRelations($model, Params::getRelations($this->request));
				if ($errors !== array()) {
					return array(
						'success' => false,
						'msg' => implode('<br>', $errors)
					);
				}
				return array(
					'success' => true,
					'msg' => 'Record with id '. $id .' saved!'
				);
			}
			return array(
				'success' => false,
				'msg' => $this->jsonRecursiveGetMsg($model->getMessages())
			);
		}
		unset($params['id']);
		/**
		 * @var \Phalcon\Mvc\Model $model
		 */
		$model = new $modelName();
		$model->assign($params);
		$this->security->check(array(
			'model' => $model,
			'action' => CrudOperations::CREATE,
			'modelName' => $modelName,
			'params' => $params
		));
		if ($model->save()) {
			$errors = $this->saveRelations($model, Params::getRelations($this->request));
			if ($errors !== array()) {
				return array(
					'success' => false,
					'msg' => implode('<br>', $errors)
				);
			}
			return array(
				'success' => true,
				'msg' => 'New record saved!',
				'id' => $model->id
			);
		}
		return array(
			'success' => false,
			'msg' => $this->jsonRecursiveGetMsg($model->getMessages())
		);
	}

	private function saveRelations($model, $relations, $messages = array()) {
		$id = @$model->id;
		/**
		 * @var \Phalcon\Mvc\Model $model
		 */
		if (!$model or !$id) {return;}
		foreach ($relations as $relationName => $relationValues) {
			if (!is_array($relationValues)) {continue;}
			$modelRelation = $model->getModelsManager()->getRelationByAlias(get_class($model), $relationName);
			if ($modelRelation->getType() == 0) {continue;}
			$messages = $this->deleteRelation($model, $relationName, $messages);
			$messages = $this->createRelation($model, $relationName, $relationValues, $messages);
		}
		return $messages;
	}

	private function createRelation($model, $relationName, $relationValues, $messages = array()) {
		foreach ($relationValues as $relationValue) {
			unset($relationValue['id']);
			$relation = new $relationName;
			$relation->assign($relationValue);
			$referencedFields = $model->getHasManyRelationReferencedFields($relationName);
			$relation->assign(array(
				$referencedFields => $model->id
			));
			$this->security->check(array(
				'model' => $relation,
				'action' => CrudOperations::CREATE,
				'modelName' => get_class($relation),
				'params' => $relationValues
			));
			if (!$relation->save()) {
				$messages[] = implode(', ', $relation->getMessages());
			}
		}
		return $messages;
	}

	private function deleteRelation($model, $relationName, $messages = array()) {
		foreach ($model->{$relationName} as $related) {
			$this->security->check(array(
				'model' => $related,
				'action' => CrudOperations::DELETE,
				'modelName' => get_class($related)
			));
			/**
			 * @var \Phalcon\Mvc\Model $related
			 */
			if (!$related->delete()) {
				$messages[] = implode(', ', $related->getMessages());
			}
		}
		return $messages;
	}

	/**
	 * Delete record from DB.
	 * @return array
	 */
	private function delete() {
		$params = Params::getMergeParams($this->request);
		if (empty($params['id'])) {
			return array(
				'success' => false,
				'msg' => 'param id is require'
			);
		}
		$id = intval($params['id']);
		$modelName = Analyzer::getModelName($this->request->get('_url'));
		/**
		 * @var \Phalcon\Mvc\Model $model
		 */
		$model = $modelName::findFirst($id);
		if (!$model) {
			return array(
				'success' => false,
				'msg' => 'Record with id '. $id .' not found'
			);
		}
		$this->security->check(array(
			'model' => $model,
			'action' => CrudOperations::DELETE,
			'modelName' => $modelName,
			'params' => $params
		));
		if ($model->delete()) {
			return array(
				'success' => true,
				'msg' => 'Record with id '. $id .' has removed!'
			);
		}
		return array(
			'success' => false,
			'msg' => $this->jsonRecursiveGetMsg($model->getMessages())
		);
	}

	public function beforeExecuteRoute() {
		$this->initAcl();
	}

	private function initAcl() {
		$di = $this->getDI();
		$aclServiceName = CheckerAccessLevel::DI_SERVICE_NAME;
		$checkerAccessLevel = $di->has($aclServiceName) ?  $di->get($aclServiceName) : new AllowedLevel();
		$this->security = new Security($checkerAccessLevel);
	}
}