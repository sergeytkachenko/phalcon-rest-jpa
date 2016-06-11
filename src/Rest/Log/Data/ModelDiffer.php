<?
namespace PPA\Rest\Log\Data;

use PPA\Rest\Log\Data\Model as DataModel;

interface ModelDiffer {

	const DI_SERVICE_NAME = 'modelDifferService';

	/**
	 * @param \PPA\Rest\Log\Data\Model $model
	 */
	public function diff(DataModel $model);
}