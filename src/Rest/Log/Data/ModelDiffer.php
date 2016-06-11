<?
namespace PPA\Rest\Log\Data;

interface ModelDiffer {

	const DI_SERVICE_NAME = 'modelDifferService';

	/**
	 * @param \PPA\Rest\Log\Data\Model[] $models
	 */
	public function diff(array $models);
}