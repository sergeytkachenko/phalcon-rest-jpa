<?

namespace PPA\Rest\Log;

use Phalcon\Mvc\Model;
use Phalcon\Text;
use PPA\Rest\Log\Data\EmptyDiffer;

class Manager
{
	/**
	 * @var \PPA\Rest\Log\Data\ModelDiffer
	 */
	protected $modelDiffer;

	/**
	 * @var string
	 */
	protected $modelName;

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
		$this->setModelName($model);
		$this->changeData->setNewModel($model);
		$this->invokeDiffer();
	}

	/**
	 * @param Model $model
	 */
	public function createModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$this->setModelName($model);
		$this->changeData->setOldModel(null);
		$this->changeData->setNewModel($model);
		$this->invokeDiffer();
	}
	
	/**
	 * @param Model $model
	 */
	public function deleteModel(Model $model) {
		if ($this->isEmptyDiffer()) {return;}
		$this->setModelName($model);
		$this->changeData->setOldModel($model);
		$this->changeData->setNewModel(null);
		$this->invokeDiffer();
	}

	public function setOldModel($model) {
		$className = $model->getClassName();
		$oldModel = clone $className::findFirstById($model->id);
		$this->changeData->setOldModel($oldModel);
	}

	/**
	 * @param \Phalcon\Mvc\Model $model
	 */
	public function setModelName($model) {
		$className = $model->getClassName();
		$this->modelName = $className;
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

	private function invokeDiffer() {
		$diffModels = $this->changeData->getDiffModels($this->modelName);
		$this->modelDiffer->diff($diffModels);
	}

	public function setLogChangeGroupId() {
		$this->logChangeGroupId = Text::random();
	}

}