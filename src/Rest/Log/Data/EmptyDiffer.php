<?
namespace PPA\Rest\Log\Data;
use PPA\Rest\Log\ChangedData;

class EmptyDiffer implements ModelDiffer
{
	
	/**
	 * @param ChangedData $changedData
	 * @return bool
	 */
	public function saveDiff(ChangedData $changedData) {
		return true;
	}

	/**
	 * @param ChangedData $changedData
	 * @return mixed
	 */
	public function createDiff(ChangedData $changedData) {
		return true;
	}

	/**
	 * @param ChangedData $changedData
	 * @return mixed
	 */
	public function deleteDiff(ChangedData $changedData) {
		return true;
	}
}