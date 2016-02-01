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
		$params = $this->getParams();
		$url = $this->request->get('_url');
		if (!$url) {
			throw new Exception('_url is mast specific');
		}

		$hasMany = PPACriteria::hasMany($url);
		$data = in_array('fetchRelations', $params)
			? PPACriteria::fetchWithRelations($url, $params)
			: PPACriteria::fetch($url, $params);

		if (!$data) {return array();}
		return $hasMany ? $data : $data[0];
	}
}