<?php
use Phalcon\Mvc\Model;
use PPA\Rest\Model\BaseModel;

class TestModel extends Model
{
	use BaseModel;
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $title;

	/**
	 *
	 * @var string
	 */
	public $lastName;

	public function getSource()
	{
		return 'test_model';
	}

	/**
	 * Allows to query a set of records that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Brands[]
	 */
	public static function find($parameters = null)
	{
		return parent::find($parameters);
	}

	/**
	 * Allows to query the first record that match the specified conditions
	 *
	 * @param mixed $parameters
	 * @return Brands
	 */
	public static function findFirst($parameters = null)
	{
		return parent::findFirst($parameters);
	}
}