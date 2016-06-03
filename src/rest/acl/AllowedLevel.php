<?

namespace PPA\Rest\Acl\Level;

class AllowedLevel implements CheckerAccessLevel {
	
	/**
	 * @inheritdoc CheckerAccessLevel
	 */
	public function doCheck(array $params) {
		return true;
	}
}