<?

namespace PPA\Rest\Acl;

class Security {

	protected $checkerAccessLevel;
	protected $params = array();

	public function __construct(CheckerAccessLevel $checkerAccessLevel) {
		$this->checkerAccessLevel = $checkerAccessLevel;
	}

	public function check() {
		$access = $this->checkerAccessLevel->doCheck($this->params);
		if (true !== $access) {
			throw new Exception('Sorry, you are not have permission for this crud operation');
		}
	}
}