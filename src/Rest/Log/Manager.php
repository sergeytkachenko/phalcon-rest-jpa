<?

namespace PPA\Rest\Log;

use Phalcon\Mvc\Model;
use PPA\Rest\Log\Data\EmptyDiffer;

class Manager
{
	/**
	 * @var \PPA\Rest\Log\Data\ModelDiffer
	 */
	protected $modelDiffer;

	/**
	 * Manager constructor.
	 * @param $modelDiffer
	 */
	public function __construct($modelDiffer) {
		$this->modelDiffer = $modelDiffer;
	}

	/**
	 * @param Model $model
	 */
	public function saveModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$data = new ChangedData();
		$data->fetchModel($model);
		$this->modelDiffer->saveDiff($data);
	}

	/**
	 * @param Model $model
	 */
	public function createModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$data = new ChangedData();
		$data->setNewModel($model);
		$this->modelDiffer->createDiff($data);
	}
	
	/**
	 * @param Model $model
	 */
	public function deleteModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$data = new ChangedData();
		$data->setOldModel($model);
		$this->modelDiffer->deleteDiff($data);
	}

	/**
	 * @return bool
	 */
	private function isEmptyDiffer() {
		return $this->modelDiffer instanceof EmptyDiffer;
	}
}