<?php
namespace PPA\Rest\Url;

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
	 * @return bool Пришел ли запрос на выборку сущности.
	 */
	public static function isFetching($url) {
		return !self::isDeleting($url) && !self::isSaving($url);
	}

	/**
	 * @param $url Url всего ppa запроса.
	 * @return bool Пришел ли запрос на удаление сущности.
	 */
	public static function isDeleting($url) {
		return (bool) preg_match('/\/delete(\/)?(\?.*)?$/', $url);
	}

	public static function getModelName($url) {
		return preg_replace('/.*ppa\/(s\/)?([a-zA-Z0-9]+).*/', '$2', $url);
	}
}