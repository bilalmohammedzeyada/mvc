<?php


namespace System\Core;


use System\DB\Mysql;
use System\Exceptions\QueryBuildModeException;

abstract class Models extends Mysql
{
    protected $table = '';
    protected $pk = 'id';
    protected $sql = '';
    protected $select = '*';
    protected $conditions = null;
    protected $order = null;
    protected $offset = null;
    protected $limit = null;

    public function select($columns = '*') {
        if (is_array($columns)) {
            $this->select = implode(', ', $columns);
        }
        else {
            $this->select = $columns;
        }

        return $this;
    }

    public function where($column, $value, $operator = '=') {
        if (is_null($this->conditions)) {
            $this->conditions = "{$column} {$operator} '{$value}''";
        }
        else {
            $this->conditions .= "AND {$column} {$operator} '{$value}''";
        }

        return $this;
    }

    public function orWhere($column, $value, $operator = '=') {

            $this->conditions .= "OR {$column} {$operator} '{$value}''";

        return $this;

    }

    public function order($column, $direction = 'ASC') {
        if (is_null($this->order)) {
            $this->order = "{$column} {$direction}";
        }
        else {
            $this->order .= ", {$column} {$direction}";
        }
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;

        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;

        return $this;
    }

    public function get() {
        $this->buildQuery('select');
        $this->query($this->sql);

        if ($this->num_rows() == 1) {
            $data = $this->fetch_assoc()[0];

            foreach ($data as $k => $v) {
                $this->{$k} = $v;
            }

            return true;
        }
        elseif ($this->num_rows() > 1) {
            $data = $this->fetch_assoc();
            $ret = [];
            $class_name = get_class($this);

            foreach ($data as $item) {
                $obj = new $class_name;

                foreach ($item as $k => $v) {
                    $obj->{$k} = $v;
                }
                $ret[] = $obj;
            }
            $this->reset();
            return $ret;
        }
        else {
            $this->reset();

            return null;
        }

    }

    public function load($id) {
        $this->where($this->pk, $id)->get();
    }

    public function save() {
        if (isset($this->{$this->pk}) && !empty($this->{$this->pk})) {
            $this->buildQuery('update');
            $flg = 0;
        }
        else {
            $this->buildQuery('insert');
            $flg = 1;
        }

        $this->query($this->sql);

        if ($flg == 1) {
            $this->{$this->pk} = $this->last_id();
        }

        $this->{$this->pk} = $this->last_id();

        return true;
    }

    protected function buildQuery($mode) {
        switch ($mode) {
            case 'select':
                $this->buildSelectQuery();
                break;
            case 'insert':
                $this->buildInsertQuery();
                break;
            case  'update':
                $this->buildUpdateQuery();
                break;

            default:
                throw new QueryBuildModeException("Query build mode '{$mode}' does not exist.");

        }
    }
    protected function buildSelectQuery() {
        $this->sql = "SELECT($this->select) FROM {$this->table}";
            if (!is_null($this->conditions)) {
                $this->sql .= "WHERE {$this->conditions}";
            }
            if (!is_null($this->order)) {
                $this->sql .= "ORDER BY {$this->order}";
            }
            if (!is_null($this->limit)) {
                if (is_null($this->limit)) {
                    $this->sql .= "LIMIT {$this->limit}";
                }
                else {
                    $this->sql .= "LIMIT {$this->offset},{$this->limit}";
                }
            }
        }

        protected function buildInsertQuery() {
            $columns =  $this->getDataColumns('data');

            $this->sql = "INSERT INTO {$this->table} SET";

            $cond = [];
            foreach ($columns as $k => $v) {
                $cond[] = "{$k} = '{$v}'";
            }
            $this->sql .= implode('. ', $cond);
        }

    protected function buildUpdateQuery() {
        $columns =  $this->getDataColumns('data');

        $this->sql = "UPDATE {$this->table} SET";

        $cond = [];
        foreach ($columns as $k => $v) {
            $cond[] = "{$k} = '{$v}'";
        }
        $this->sql .= implode('. ', $cond);
        $this->sql .= " WHERE {$this->pk} = '{$this->{$this->pk}}'";
    }

        protected function getDataColumns($type = 'keys') {
            $all = get_object_vars(get_class($this));
            $vars = get_object_vars($this);

            $diff = array_diff_key($vars, $all);

            if ($type == 'data') {
                return $diff;
            }
            else {
                return array_keys($diff);
            }

        }

        protected function reset() {
        $this->sql = '';
        $this->select = '*';
        $this->conditions = 'null';
        $this->order = 'null';
        $this->offset = 'null';
        $this->limit = 'null';
        }
}