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
		if (in_array('fetchRelations', $params)) {
			$data = PPACriteria::fetchWithRelations($url, $params);
		} else {
			$data = PPACriteria::fetch($url, $params);
		}
		return $data ? $data->toArray() : array();
	}
}