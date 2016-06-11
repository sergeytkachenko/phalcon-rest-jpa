<?
namespace PPA\Rest\Log\Data;

use Diff\DiffOp\DiffOp;

class Model {
	/**
	 * @var int
	 */
	public $modelId;
	/**
	 * @var string
	 */
	public $modelName = null;
	/**
	 * @var string
	 */
	public $columnName = null;
	/**
	 * @var string
	 */
	public $oldValue = null;
	/**
	 * @var string
	 */
	public $newValue = null;

	/**
	 * @var string null
	 */
	public $requestId = null;

	public function assign(DiffOp $diff) {
		$data = $diff->toArray();
		$this->oldValue = @$data['oldvalue'];
		$this->newValue = @$data['newvalue'];
		return $this;
	}
}