<?php

namespace PPA\Rest\Model;
use Phalcon\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;

/**
 * Class BaseModel
 * Служит базовым вспомагательным "класом" для потдержки общих методов моделей.
 */
trait BaseModel
{
	/**
	 * @return mixed Name of the class with namespace.
	 */
	public function getClassName() {
		return static::class;
	}
	/**
	 * тут храниться массив связей, который наполняется при вызове метода ->fetchRelations
	 * @var array
	 */
	public $relations = array();
	/**
	 * тут храниться массив связей, который наполняется при вызове метода ->joinedRelations
	 * @var array
	 */
	public $joinedRelations = array();
	/**
	 * Название ключа, по котору происходит подгрузка связанной записи для joinedRelations.
	 * @var string
	 */
	public static $referencesKeyOption = 'key';

	/**
	 * Returns all relations of model.
	 * @return Relation[] Все связи модели.
	 */
	public function getRelations() {
		return $this->getModelsManager()->getRelations(get_class($this));
	}
	
	/**
	 * Подгружает все связанные свойства из других таблиц и ложит все связи в свойство $this->relations
	 * Очень удобно когда вам нужно выбрать один методом сущность, а также все ее связи.
	 * Данный метод не подгружает значения связей "один ко многим" и "многие ко многим", если вам нужно загрузить
	 *  содержимое таких связей используйте BaseModel->joinedRelations.
	 * @param bool $isConvertToArray
	 * @param array $needleRelations
	 * @return $this
	 * @internal param array $relationsFilter
	 */
	public function fetchRelations($isConvertToArray = true, array $needleRelations = null) {
		$relations = $this->getRelations();
		foreach ($relations as $relation) {
			if ($needleRelations and !in_array($relation->getOption('alias'), $needleRelations)) {continue;}
			$this->assignRelation($relation, $isConvertToArray);
		}
		return $this;
	}

	/**
	 * Выбирает связанные записи и подгружает их в $this->joinedRelations.
	 * В отличии от fetchRelations выюирает значение сущностей "один ко многим".
	 * @throws \PPA\Rest\Exception
	 */
	public function joinedRelations() {
		$relations = $this->getRelations();
		foreach ($relations as $relation) {
			$this->joinedRelation($relation);
		}
		return $this;
	}

	/**
	 * Выбирает связанную запись и подгружает ee в $this->joinedRelations.
	 * Генерирует исключение, в случае если в модели не указан ключ связи для подгрузки связанной записи.
	 * @param \Phalcon\Mvc\Model\Relation $relation
	 * @return $this|void
	 * @throws \PPA\Rest\Exception
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
					return $this;
					/**
					throw new \PPA\Rest\Exception('If can use joinedRelation, you need to set '
						. self::$referencesKeyOption . ' of relation ' . $relationAlias
						. ' in model ' . get_class($this));
					 **/
				}
				$this->joinedHasManyRelation($relationModel, $relationAlias, $keyField);
				break;
			case 1: // HasManyToMany
				if (!$keyField) {return;}
				throw new \PPA\Rest\Exception('Sorry, relations with type HasManyToMany is not have implementations');
				break;
		}
		return $this;
	}

	/**
	 * Выбирает связанную запись и подгружает ee в $this->joinedRelations.
	 * @param \Phalcon\Mvc\Model $relationModel
	 * @param string $relationAlias
	 * @return \Phalcon\Mvc\Model
	 */
	private function joinedBelongsToRelation(Model $relationModel, $relationAlias) {
		return $this->joinedRelations[$relationAlias] = @$relationModel;
	}

	/**
	 * Выбирает связанную запись и подгружает ee в $this->joinedRelations.
	 * @param \Phalcon\Mvc\Model[] $models
	 * @param string $relationAlias
	 * @param string $keyField
	 */
	private function joinedHasManyRelation($models, $relationAlias, $keyField) {
		if (!method_exists($models, 'filter')) {return;}
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
		$data['joinedRelations'] = $this->joinedRelations;

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