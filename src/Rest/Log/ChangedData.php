<?

namespace PPA\Rest\Log;

use Diff\Differ\MapDiffer;
use Phalcon\Mvc\Model;

class ChangedData {

	/**
	 * @var \Phalcon\Mvc\Model
	 */
	protected $oldModel;
	/**
	 * @var \Phalcon\Mvc\Model
	 */
	protected $newModel;
	/**
	 * @var \Diff\Differ\MapDiffer
	 */
	protected $differ;

	/**
	 * ChangedData constructor.
	 */
	public function __construct() {
		$this->differ = new MapDiffer();
	}

	/**
	 * @param Model $model
	 */
	public function setNewModel(Model $model = null) {
		$this->newModel = $model ? clone $model : null;
	}

	/**
	 * @param Model $model
	 */
	public function setOldModel(Model $model = null) {
		$this->oldModel = $model ? clone $model : null;
	}

	/**
	 * @return Model
	 */
	public function getOldModel() {
		return $this->oldModel;
	}

	/**
	 * @return Model
	 */
	public function getNewModel() {
		return $this->newModel;
	}

	/**
	 * @return \Diff\DiffOp\DiffOp[]
	 */
	protected function getDiff() {
		$oldModel = $this->oldModel ? $this->oldModel->toArray() : array();
		$newModel = $this->newModel ? $this->newModel->toArray() : array();
		return $this->differ->doDiff($oldModel, $newModel);
	}

	/**
	 * @param string $modelName
	 * @param array $models
	 * @return Data\Model
	 */
	public function getDiffModels($modelName, $models = array()) {
		$diffList = $this->getDiff();
		foreach ($diffList as $columnName => $diff) {
			$model = new \PPA\Rest\Log\Data\Model();
			$model->assign($diff);
			$model->modelName = $modelName;
			$model->columnName = $columnName;
			if ($model->oldValue == $model->newValue) {
				continue;
			}
			$models[] = $model;
		}
		return $models;
	}
}