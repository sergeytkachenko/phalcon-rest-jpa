<?

namespace PPA\Rest\Acl;

abstract class CrudOperations {
	const CREATE = 'C';
	const READ = 'R';
	const UPDATE = 'U';
	const DELETE = 'D';

	public static function isCreate($code) {
		return self::CREATE === $code;
	}

	public static function isRead($code) {
		return self::READ === $code;
	}

	public static function isUpdate($code) {
		return self::UPDATE === $code;
	}

	public static function isDelete($code) {
		return self::DELETE === $code;
	}
}