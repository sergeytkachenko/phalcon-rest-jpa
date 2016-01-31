<?
namespace Lib\Rest;

use Phalcon\Mvc\Dispatcher;

class PpaController extends JsonController
{
	public function crudAction() {
		$data = PPACriteria::fetch($this->request->get('_url'), $this->request->get());
		return $data;
	}
}