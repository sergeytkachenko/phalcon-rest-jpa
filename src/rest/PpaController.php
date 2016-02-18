<?
namespace PPA\Rest;

use Phalcon\Exception;
use Phalcon\Mvc\Dispatcher;
use PPA\Rest\Url\Analyzer;

class PpaController extends JsonController
{
	private function getParams() {
		$jsonRawBody = (array)$this->request->getJsonRawBody();
		return array_merge($this->request->get(), $this->request->getPost(), $this->request->getPut(), $jsonRawBody);
	}

	public function crudAction() {
		$url = $this->request->get('_url');
		if (!$url) {
			throw new Exception('_url is mast specific');
		}
		if (Analyzer::isSaving($url)) {
			return $this->save();
		}
		if (Analyzer::isDeleting($url)) {
			return $this->delete();
		}
		$params = $this->getParams();
		$isOnlyFirst = !PPACriteria::hasMany($url);
		$isFetchRelations = array_key_exists('fetchRelations', $params);

		$data = PPACriteria::fetch($url, $params);
		if (!$data) {return array();}
		if ($isOnlyFirst) {
			$data = $data->getFirst();
			if(!$data) {return array();}
			return $isFetchRelations ? $data->fetchRelations()->toArrayRelations() : $data->toArray();
		}
		if ($isFetchRelations) {
			return $data->filter(function($model) {
				return $model->fetchRelations();
			});
		}
		return $data->toArray();
	}

	/**
	 * Save and create record to DB.
	 * @return array
	 */
	private function save() {
		$params = $this->getParams();
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
			return array(
				'success' => true,
				'msg' => 'New record saved!'
			);
		}
		return array(
			'success' => false,
			'msg' => $this->jsonRecursiveGetMsg($model->getMessages())
		);
	}

	/**
	 * Delete record from DB.
	 * @return array
	 */
	private function delete() {
		$params = $this->getParams();
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