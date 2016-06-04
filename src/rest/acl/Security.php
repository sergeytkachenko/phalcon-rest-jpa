<?

namespace PPA\Rest\Acl;

class Security {

	protected $checkerAccessLevel;

	public function __construct(CheckerAccessLevel $checkerAccessLevel) {
		$this->checkerAccessLevel = $checkerAccessLevel;
	}

	public function check(array $params = null) {
		$access = $this->checkerAccessLevel->doCheck($params);
		if (true !== $access) {
			throw new Exception('Sorry, you are not have permission for this crud operation');
		}
	}
}