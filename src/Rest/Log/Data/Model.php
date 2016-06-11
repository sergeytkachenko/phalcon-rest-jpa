<?
namespace PPA\Rest\Log\Data;

use Diff\DiffOp\DiffOp;

class Model {
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

	public function assign(DiffOp $diff) {
		$data = $diff->toArray();
		$this->oldValue = @$data['oldvalue'];
		$this->newValue = @$data['newvalue'];
		return $this;
	}
}