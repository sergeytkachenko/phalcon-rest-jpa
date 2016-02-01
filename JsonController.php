<?
namespace Lib\Rest;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

class JsonController extends Controller {

	protected $_isJsonResponse = false;

	public function setJson() {
		$this->view->disable();

		$this->_isJsonResponse = true;
		$this->response->setContentType('application/json', 'UTF-8');
	}

	public function afterExecuteRoute(Dispatcher $dispatcher) {
		$this->view->disable();
		$data = $dispatcher->getReturnedValue();
		if (is_array($data)) {
			$data = json_encode($data);
		}

		$this->response->setContentType('application/json', 'UTF-8');
		$this->response->setContent($data);
		$this->response->send();
		// TODO bug fix
		exit;
	}

	public function jsonRecursiveGetMsg ($dataList) {
		$messages = array();
		foreach($dataList as $data) {
			$messages[] = $data->getMessage();
		}
		return $messages;
	}

}