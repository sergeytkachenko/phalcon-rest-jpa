<?

namespace PPA\Rest\Acl\Level;

use PPA\Rest\Acl\CheckerAccessLevel;

class AllowedLevel implements CheckerAccessLevel {
	
	/**
	 * @inheritdoc CheckerAccessLevel
	 */
	public function doCheck(array $params) {
		return true;
	}
}