<?
namespace PPA\Rest\Log\Data;
use PPA\Rest\Log\Data\Model as DataModel;

class EmptyDiffer implements ModelDiffer
{

	/**
	 * @param \PPA\Rest\Log\Data\Model $model
	 */
	public function diff(DataModel $model) {
		// empty differ 
	}
}