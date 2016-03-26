<?
namespace PPA\Rest;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\ResultsetInterface;
use PPA\Rest\Url\Analyzer;
use PPA\Rest\Url\Operators;
use PPA\Rest\Utils\Params;

class PpaController extends JsonController
{
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
			if (!$data) {return array();}
			return $this->getFinallyFullData($data, $params, $url);
		} catch (\PPA\Rest\Exception $e) {
			debug($e);
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
		if ($isOnlyFirst) {
			/**
			 * @var \Phalcon\Mvc\Model $data
			 */
			$data = $data->getFirst();
			if (!$data) {return array();}
			if ($isFetchRelations) {
				return $data->fetchRelations()->toArrayRelations();
			}
			if ($isJoinedRelations) {
				return $data->joinedRelations()->toArrayRelations();
			}
			return $data->toArray();
		}
		return $data->filter(function($model) use ($isFetchRelations, $isJoinedRelations) {
			if ($isFetchRelations) {
				return $model->fetchRelations();
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
		if (!$model or !$id) {return;}
		foreach ($relations as $relationName => $relationValues) {
			if (!is_array($relationValues)) {continue;}
			$messages = $this->deleteRelation($model, $relationName, $messages);
			$messages = $this->createRelation($model, $relationName, $relationValues, $messages);
		}
		return $messages;
	}

	private function createRelation($model, $relationName, $relationValues, $messages = array()) {
		foreach ($relationValues as $relationValue) {
			$relation = new $relationName;
			$relation->assign($relationValue);
			$referencedFields = $model->getHasManyRelationReferencedFields($relationName);
			$relation->assign(array(
				$referencedFields => $model->id
			));
			if (!$relation->save()) {
				$messages[] = implode(', ', $relation->getMessages());
			}
		}
		return $messages;
	}

	private function deleteRelation($model, $relationName, $messages = array()) {
		foreach ($model->{$relationName} as $related) {
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
}