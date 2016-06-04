<?php

namespace PPA\Rest\Column;

use PPA\Rest\Url\Operations;

class Portion
{
	/**
	 * @var string $portion Column portion.
	 */
	private $portion = null;

	/**
	 * Portion constructor.
	 * @param string $portion
	 */
	public function __construct($portion) {
		$this->portion = $portion;
	}

	/**
	 * @return string Column name.
	 */
	public function getColumnName() {
		return Operations::truncateOperation($this);
	}

	/**
	 * @return string
	 */
	public function getPortion() {
		return $this->portion;
	}
}