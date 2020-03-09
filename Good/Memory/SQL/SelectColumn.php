<?php

namespace Good\Memory\SQL;

class SelectColumn
{
    public $table;
    public $column;
    public $as;

    public function __construct($table, $column, $as)
    {
        $this->table = $table;
        $this->column = $column;
        $this->as = $as;
    }
}

?>
