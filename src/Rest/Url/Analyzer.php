<?php
namespace PPA\Rest\Url;

use Phalcon\Text;

abstract class Analyzer
{
	/**
	 * @param $url Url всего ppa запроса.
	 * @return bool Пришел ли запрос на сохранение сущности.
	 */
	public static function isSaving($url) {
		return (bool) preg_match('/\/save(\/)?(\?.*)?$/', $url);
	}

	/**
	 * @param $url Url всего ppa запроса.
	 * @return bool Пришел ли запрос на удаление сущности.
	 */
	public static function isDeleting($url) {
		return (bool) preg_match('/\/delete(\/)?(\?.*)?$/', $url);
	}

	/**
	 * @param $url Url всего ppa запроса.
	 * @return bool Пришел ли запрос на выборку сущности.
	 */
	public static function isFetching($url) {
		return !self::isDeleting($url) && !self::isSaving($url);
	}

	/**
	 * @param $url Url всего ppa запроса.
	 * @return bool Пришел ли запрос на поиск по всем колонкам модели.
	 */
	public static function isSearchingAllColumns($url) {
		return (bool) preg_match('/\/s\/[a-zA-Z0-9]+\/search(\/)?(\?.*)?$/', $url);
	}

	/**
	 * Выбирает название модели.
	 * @param $url Url всего ppa запроса.
	 * @return string Название модели.
	 */
	public static function getModelName($url) {
		$modelName = preg_replace('/.*ppa\/(s\/)?([a-zA-Z0-9]+).*/', '$2', $url);
		return Text::camelize(Text::uncamelize($modelName));
	}

	/**
	 * @param $url
	 * @return bool
	 */
	public static function hasMany($url) {
		return (bool) preg_match('/\/s\//', $url);
	}
}