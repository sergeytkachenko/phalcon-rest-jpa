<?

namespace PPA\Rest\Acl;

class AllowedLevel implements CheckerAccessLevel {
	
	/**
	 * @inheritdoc CheckerAccessLevel
	 */
	public function doCheck(array $params) {
		return true;
	}
}