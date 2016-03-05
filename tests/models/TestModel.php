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
	public $last_name;

	public function getSource()
	{
		return 'test_model';
	}

}