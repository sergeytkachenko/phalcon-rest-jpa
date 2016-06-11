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
	public function setNewModel(Model $model) {
		$this->newModel = clone $model;
	}

	/**
	 * @param Model $model
	 */
	public function setOldModel(Model $model) {
		$this->oldModel = clone $model;
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
	public function getDiff() {
		$oldModel = $this->oldModel ? $this->oldModel->toArray() : null;
		$newModel = $this->newModel ? $this->newModel->toArray() : null;
		return $this->differ->doDiff($oldModel, $newModel);
	}
}