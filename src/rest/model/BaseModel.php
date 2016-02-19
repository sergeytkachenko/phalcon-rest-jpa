<?
namespace PPA\Rest\Model;

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

	/**
	 * Подгружает все связанные свойства из других таблиц и ложит все связи в свойство $this->relations
	 * Очень удобно когда вам нужно выбрать один методом сущность, а также все ее связи
	 * @return $this
	 */
	public function fetchRelations() {
		$relations = $this->getModelsManager()->getBelongsTo($this);
		foreach ($relations as $relation) {
			$this->assignRelation($relation);
		}
		$relations = $this->getModelsManager()->getHasMany($this);
		foreach ($relations as $relation) {
			$this->assignRelation($relation);
		}
		$relations = $this->getModelsManager()->getHasManyToMany($this);
		foreach ($relations as $relation) {
			$this->assignRelation($relation);
		}
		return $this;
	}

	/**
	 * Возвращает связь многие ко многим по alias.
	 * @param $relationAlias
	 * @return \Phalcon\Mvc\Model\Relation
	 */
	public function getHasManyRelation($relationAlias) {
		/**
		 * @var \Phalcon\Mvc\Model\Relation[] $relations
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
	 * Обращается и подгружает связанные данные сущности
	 * @param $relation
	 * @return $this|void
	 */
	private function  assignRelation($relation) {
		$options = $relation->getOptions();
		$alias = @$options['alias'];

		if (!$alias) {
			return;
		}
		$data = $this->$alias;
		$this->relations[$alias] = $data ? $data->toArray() : $data;

		return $this;
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