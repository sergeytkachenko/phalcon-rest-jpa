<?

namespace PPA\Rest\Acl;

class DeniedLevel implements CheckerAccessLevel {
	
	/**
	 * @inheritdoc CheckerAccessLevel
	 */
	public function doCheck(array $params) {
		return false;
	}
}