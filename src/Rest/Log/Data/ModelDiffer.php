<?
namespace PPA\Rest\Log\Data;

use Phalcon\Mvc\Model;
use PPA\Rest\Log\ChangedData;

interface ModelDiffer {
	const DI_SERVICE_NAME = 'modelDifferService';
	
	/**
	 * @param ChangedData $changedData
	 */
	public function saveDiff(ChangedData $changedData);

	/**
	 * @param ChangedData $changedData
	 * @return mixed
	 */
	public function createDiff(ChangedData $changedData);

	/**
	 * @param ChangedData $changedData
	 * @return mixed
	 */
	public function deleteDiff(ChangedData $changedData);
}