<?

namespace PPA\Rest\Log;

use Phalcon\Mvc\Model;

class Manager
{
	public function save(Model $model) {
		$data = new Data();
		$data->fetchModel($model);
		debug($data);
	}

	public function create(Model $model) {
		$data = new Data();
		$data->setNewModel($model);
		debug($data);
	}
	
	public function delete(Model $model) {
		$data = new Data();
		$data->setOldModel($model);
		debug($data);
	}

}