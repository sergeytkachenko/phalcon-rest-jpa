<?
namespace PPA\Rest\Model;
use Phalcon\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;

/**
 * Class BaseModel
 * Служит базовым вспомагательным "класом" для потдержки общих методов моделей
 */
trait BaseModel
{

	/**
	 * тут храниться массив связей, который наполняется при вызове метода ->fetchRelations
	 * @var array
	 */
	public $relations = array();
	public $joinedRelations = array();
	public static $referencesKeyOption = 'key';

	/**
	 * Returns all relations of model.
	 * @return Relation[]
	 */
	public function getRelations() {
		return $this->getModelsManager()->getRelations(get_class($this));
	}

	/**
	 * Подгружает все связанные свойства из других таблиц и ложит все связи в свойство $this->relations
	 * Очень удобно когда вам нужно выбрать один методом сущность, а также все ее связи
	 * @param bool $isConvertToArray
	 * @return $this
	 */
	public function fetchRelations($isConvertToArray = true) {
		$relations = $this->getRelations();
		foreach ($relations as $relation) {
			$this->assignRelation($relation, $isConvertToArray);
		}
		return $this;
	}

	/**
	 * @throws Exception
	 */
	public function joinedRelations() {
		$relations = $this->getRelations();
		foreach ($relations as $relation) {
			$this->joinedRelation($relation);
		}
	}

	/**
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 * @throws Exception
	 */
	public function joinedRelation($relation) {
		$keyField = $relation->getOption(self::$referencesKeyOption);
		$relationAlias = $relation->getOption('alias');
		$relationModel = @$this->$relationAlias;
		if (!$relationModel) {return;}
		switch ($relation->getType()) {
			case 0: // BelongsTo
				$this->joinedBelongsToRelation($relationModel, $relationAlias);
				break;
			case 2: // HasMany
				if (!$keyField) {
					throw new Exception('If can use joinedRelation, you need to set '
						. self::$referencesKeyOption . ' of relation ' . $relationAlias
						. ' in model ' . get_class($this));
				}
				$this->joinedHasManyRelation($relationModel, $relationAlias, $keyField);
				break;
			case 1: // HasManyToMany
				if (!$keyField) {return;}
				throw new Exception('Sorry, relations with type HasManyToMany is not have implementations');
				break;
		}
	}

	/**
	 * @param \Phalcon\Mvc\Model $relationModel
	 * @param string $relationAlias
	 * @return \Phalcon\Mvc\Model
	 */
	private function joinedBelongsToRelation(Model $relationModel, $relationAlias) {
		return $this->joinedRelations[$relationAlias] = @$relationModel;
	}

	/**
	 * @param \Phalcon\Mvc\Model[] $models
	 * @param string $relationAlias
	 * @param string $keyField
	 */
	private function joinedHasManyRelation($models, $relationAlias, $keyField) {
		$this->joinedRelations[$relationAlias] = $models->filter(function($model) use ($keyField) {
			$related = $model->getRelated($keyField);
			return $related ? $related : array();
		});
	}

	/**
	 * Возвращает связь многие ко многим по alias.
	 * @param $relationAlias
	 * @return Relation
	 */
	public function getHasManyRelation($relationAlias) {
		/**
		 * @var Relation[] $relations
		 */
		$relations = $this->getModelsManager()->getHasMany($this);
		foreach ($relations as $relation) {
			if ($relation->getOption('alias') == $relationAlias) {
				return $relation;
			}
		}
	}

	/**
	 * Возвращает название колонки связи многие ко многим, по alias связи.
	 * @param $relationAlias
	 * @return array|string
	 */
	public function getHasManyRelationReferencedFields($relationAlias) {
		$relation = $this->getHasManyRelation($relationAlias);
		return $relation->getReferencedFields();
	}

	/**
	 * Обращается и подгружает связанные данные сущности.
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 * @param bool $isConvertToArray - Конвертировать ли в массив.
	 * @return $this|void
	 */
	private function  assignRelation(Relation $relation, $isConvertToArray = true) {
		$alias = $relation->getOption('alias');
		$this->relations[$alias] = $this->getAssignRelation($this, $relation, $isConvertToArray);
		return $this;
	}

	/**
	 * @param \Phalcon\Mvc\Model $model
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 * @param bool $isConvertToArray
	 * @return array|Model\Resultset|\Phalcon\Mvc\Model|void
	 */
	private function getAssignRelation(Model $model, Relation $relation, $isConvertToArray = true) {
		$alias = $relation->getOption('alias');
		if (!$alias) {return;}
		$data = $model->$alias;
		return $data && $isConvertToArray ? $data->toArray() : $data;
	}

	/**
	 * Делает из обькта массив, с учетом свойства relations
	 * @return mixed
	 */
	public function toArrayRelations() {
		$data = $this->toArray();
		$data['relations'] = $this->relations;

		return $data;
	}

	/**
	 * Сохраняет в модели только те поля, которые в ней есть
	 * @param array $data
	 * @return mixed
	 */
	public function saveOnlyAttributes(array $data) {
		$this->assign($data);
		return $this->save($data);
	}

}