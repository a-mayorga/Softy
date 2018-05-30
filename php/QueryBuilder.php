<?php

namespace Softy;

require_once("Conexion.php");

use Softy\Conexion;

class QueryBuilder
{
    private $query;
    private $table;
    private $conn;

    public function __construct($table)
    {
        $this->query = '';
        $this->table = $table;
        $this->conn = new Conexion();
    }

    private function getColNames($columns)
    {
        $colNames = '';

        foreach ($columns as $key => $value) {
            if ($value != end($columns)) {
                $colNames .= "{$value}, ";
            } else {
                $colNames .= $value;
            }
        }

        return $colNames;
    }

    private function getColValues($columns)
    {
        $colValues = '';

        foreach ($columns as $key => $value) {
            if ($value != end($columns)) {
                $colValues .= "'{$value}', ";
            } else {
                $colValues .= "'{$value}'";
            }
        }

        return $colValues;
    }

    public function select($columns)
    {
        if (is_array($columns)) {
            $colNames = $this->getColNames($columns);
            $this->query .= "SELECT {$colNames}";
        } else {
            $this->query .= "SELECT {$columns}";
        }

        return $this;
    }

    public function from()
    {
        $this->query .= " FROM {$this->table}";
        return $this;
    }

    public function insert($insert)
    {
        $colNames = $this->getColNames(array_keys($insert));
        $colValues = $this->getColValues($insert);

        $this->query .= "INSERT INTO {$this->table}($colNames) VALUES($colValues)";

        return $this;
    }

    public function where($columns)
    {
        $this->query .= " WHERE";

        foreach ($columns as $key => $value) {
            if ($value != end($columns)) {
                $this->query .= " {$key} {$value[0]} '{$value[1]}' AND";
            } else {
                $this->query .= " {$key} {$value[0]} '{$value[1]}'";
            }
        }

        return $this;
    }

    public function update()
    {
        $this->query .= "UPDATE {$this->table}";
        return $this;
    }

    public function set($values)
    {
        $this->query .= " SET";

        foreach ($values as $key => $value) {
            if ($value != end($values)) {
                $this->query .= " {$key} = '{$value}',";
            } else {
                $this->query .= " {$key} = '{$value}'";
            }
        }

        return $this;
    }

    public function delete()
    {
        $this->query .= "DELETE FROM {$this->table}";
        return $this;
    }

    public function execute()
    {
        $this->query .= ";";
        $result = $this->conn->queryRes($this->query);
        $this->query = "";
        return $result;
    }
}
