<?php


namespace System\Core;


use System\DB\Mysql;

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
}