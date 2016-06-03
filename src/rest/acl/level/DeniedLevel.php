<?

namespace PPA\Rest\Acl\Level;

class DeniedLevel implements CheckerAccessLevel {
	
	/**
	 * @inheritdoc CheckerAccessLevel
	 */
	public function doCheck(array $params) {
		return false;
	}
}