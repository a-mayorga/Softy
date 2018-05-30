<?php

namespace Softy;

require_once("CRUD.php");
require_once("QueryBuilder.php");

use Softy\CRUD;
use Softy\QueryBuilder;

class Model implements CRUD
{
    private $qb;

    public function __construct($table)
    {
        $this->qb = new QueryBuilder($table);
    }

    public function find($select, $where = '')
    {
        if ($where == '') {
            $query = $this->qb->select($select)->from()->execute();
        } else {
            $query = $this->qb->select($select)->from()->where($where)->execute();
        }
        return $query;
    }

    public function save($insert)
    {
        $query = $this->qb->insert($insert)->execute();
        return $query;
    }

    public function update($params, $where)
    {
        $query = $this->qb->update()->set($params)->where($where)->execute();
        return $query;
    }

    public function delete($where)
    {
        $query = $this->qb->delete()->where($where)->execute();
        return $query;
    }
}
