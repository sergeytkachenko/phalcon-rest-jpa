<?

namespace PPA\Rest\Log;

use Phalcon\Mvc\Model;

class Data
{
	/**
	 * @var \Phalcon\Mvc\Model
	 */
	protected $oldModel;
	/**
	 * @var \Phalcon\Mvc\Model
	 */
	protected $newModel;

	/**
	 * @param Model $model
	 */
	public function fetchModel(Model $model) {
		$className = $model->getClassName();
		$this->oldModel = $className::findFirstById($model->id);
		$this->newModel = $model;
	}

	/**
	 * @param Model $model
	 */
	public function setNewModel(Model $model) {
		$this->newModel = $model;
	}

	/**
	 * @param Model $model
	 */
	public function setOldModel(Model $model) {
		$this->oldModel = $model;
	}

	public function getDiff() {
		return null; // TODO : impl diff 
	}
}