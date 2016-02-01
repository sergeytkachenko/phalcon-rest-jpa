<?
namespace Lib\Rest;

use Phalcon\Text;

abstract class PPAOperators
{
	const LIKE_PATTERN = '/^(.*[a-z0-9])Like$/';

	public static function buildOperation($fieldName) {
		if (preg_match(self::LIKE_PATTERN, $fieldName)) {
			$fieldName = preg_replace(self::LIKE_PATTERN, '$1', $fieldName);
			return Text::uncamelize($fieldName) . ' LIKE :' . lcfirst($fieldName) . ':';
		}
		return Text::uncamelize($fieldName) . ' = :' . lcfirst($fieldName) . ':';
	}

	public static function getClearColumnsName($columns) {
		foreach ($columns as $key => $column) {
			if (preg_match(self::LIKE_PATTERN, $column)) {
				$columns[$key] = preg_replace(self::LIKE_PATTERN, '$1', $column);
			}
		}
		return $columns;
	}
}