<?php

namespace kissj\Orm;

use LeanMapper\Entity;
use LeanMapper\Fluent;
use LeanMapper\Repository as BaseRepository;

class Repository extends BaseRepository {
	
	public function isExisting(array $criteria): bool {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
		$row = $qb->fetch();
		
		$b = $row !== false;
		return ($b);
	}
	
	public function find(int $id) {
		return $this->findOneBy(['id' => $id]);
	}
	
	public function findOneBy(array $criteria, array $orderBy = []) {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);
		$row = $qb->fetch();
		
		if ($row === false) {
			throw new \Exception('Entity was not found.');
		}
		// second part
		return $this->createEntity($row);
	}

	public function findBy(array $criteria, array $orderBy = []): array {
		$qb = $this->createFluent();

		$this->addConditions($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);

//        this little boi dumps sql query
//		$qb->getConnection()->test($qb->_export(null, ['%ofs %lmt', null, null]));

        $rows = $qb->fetchAll();
		$entities = [];

		foreach ($rows as $row) {
			$entities[] = $this->createEntity($row);
		}

		return $entities;
	}
	
	public function findByMultiple(array $criterias, array $orderBy = []) : array {
		$qb = $this->createFluent();
		
		foreach ($criterias as $criterium) {
			$this->addConditions($qb, $criterium);
		}
		$this->addOrderBy($qb, $orderBy);
		
		$rows = $qb->fetchAll();
		$entities = [];
		
		foreach ($rows as $row) {
			$entities[] = $this->createEntity($row);
		}
		
		return $entities;
	}

	public function countBy(array $criteria): int {
		/** @var Fluent $qb */
		$qb = $this->connection->select('count(*)')->from($this->getTable());
		$this->addConditions($qb, $criteria);
		$row = $qb->fetchSingle();
		
		return $row;
	}
	
	protected function addConditions(Fluent $qb, array $criteria) {
		foreach ($criteria as $field => $value) {
			if ($value instanceof Entity) {
				$columnName = $this->mapper->getRelationshipColumn($this->table, $this->mapper->getTable(get_class($value)));
				$qb->where("$columnName = %i", $value->id);
			} else if ($value instanceof Relation) {
				if ($value->relation === 'IN') {
					$qb->where("$field $value->relation %in", $value->value);
				} else {
					$qb->where("$field $value->relation %s", $value->value);
				}
            } else {
			    if (is_bool($value)) {
                    $qb->where("$field = %b", $value);
                } else if (is_int($value)) {
                    $qb->where("$field = %i", $value);
                } else if (is_float($value)) {
                    $qb->where("$field = %f", $value);
                } else {
                    $qb->where("$field = %s", $value);
                }
			}
		}
	}
	
	public function findIdBy(array $criteria): int {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
		$id = $qb->fetchSingle();
		
		return $id;
	}
	
	public function findAll() {
		return $this->createEntities(
			$this->createFluent()
				->fetchAll()
		);
	}

    /**
     * @param $qb
     * @param array $orderBy
     */
    protected function addOrderBy($qb, array $orderBy)
    {
        foreach ($orderBy as $order => $asc) {
            $qb->orderBy($order);
            $qb->asc($asc);
        }
    }

}