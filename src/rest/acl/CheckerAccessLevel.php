<?
namespace PPA\Rest\Acl;

interface CheckerAccessLevel {
	const DI_SERVICE_NAME = 'checkerAccessLevel'; 
	
	/**
	 * Checks access to crud operation.
	 * array
	 *  ['modelName'] string Name of the model.
	 *  ['model'] \Phalcon\Mvc\Model Fetched model with data.
	 *  ['action'] string Crud action name ('c' - create, 'r' - read, 'u' - update, 'd' - delete).
	 *  ['params'] array Fields name and fields values.
	 * @param array $params
	 * @return bool Return true if access is allowed, otherwise returns false.
	 */
	public function doCheck(array $params);
}