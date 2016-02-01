<?
namespace Lib\Rest;

use Phalcon\Exception;
use Phalcon\Mvc\Dispatcher;

class PpaController extends JsonController
{
	private function getParams() {
		return array_merge($this->request->get(), $this->request->getPost(), $this->request->getPut());
	}

	public function crudAction() {
		$url = $this->request->get('_url');
		if (!$url) {
			throw new Exception('_url is mast specific');
		}
		$params = $this->getParams();
		$isOnlyFirst = !PPACriteria::hasMany($url);
		$isFetchRelations = array_key_exists('fetchRelations', $params);

		$data = PPACriteria::fetch($url, $params);
		if (!$data) {return array();}
		if ($isOnlyFirst) {
			$data = $data->getFirst();
			return $isFetchRelations ? $data->fetchRelations()->toArrayRelations() : $data->toArray();
		}
		if ($isFetchRelations) {
			return $data->filter(function($model) {
				return $model->fetchRelations();
			});
		}
		return $data->toArray();
	}
}