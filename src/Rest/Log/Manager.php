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
	 * @var \PPA\Rest\Log\ChangedData
	 */
	protected $changeData;

	/**
	 * Manager constructor.
	 * @param $modelDiffer
	 */
	public function __construct($modelDiffer) {
		$this->modelDiffer = $modelDiffer;
		$this->changeData = new ChangedData();
	}

	/**
	 * @param Model $model
	 */
	public function saveModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$this->changeData->setNewModel($model);
		$this->modelDiffer->saveDiff($this->changeData);
	}

	/**
	 * @param Model $model
	 */
	public function createModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$this->changeData->setOldModel(null);
		$this->changeData->setNewModel($model);
		$this->modelDiffer->createDiff($this->changeData);
	}
	
	/**
	 * @param Model $model
	 */
	public function deleteModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$this->changeData->setOldModel($model);
		$this->changeData->setNewModel(null);
		$this->modelDiffer->deleteDiff($this->changeData);
	}

	/**
	 * @return bool
	 */
	private function isEmptyDiffer() {
		return $this->modelDiffer instanceof EmptyDiffer;
	}

	/**
	 * @return \PPA\Rest\Log\ChangedData
	 */
	public function getChangeData() {
		return $this->changeData;
	}
}