<?php

namespace Good\Memory\SQL;

class Join
{
    public $tableNumberOrigin;
    public $fieldNameOrigin;
    public $tableNameDestination;
    public $tableNumberDestination;
    public $fieldNameDestination;

    public function __construct($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination, $tableNumberDestination, $fieldNameDestination)
    {
        $this->tableNumberOrigin = $tableNumberOrigin;
        $this->fieldNameOrigin = $fieldNameOrigin;
        $this->tableNameDestination = $tableNameDestination;
        $this->tableNumberDestination = $tableNumberDestination;
        $this->fieldNameDestination = $fieldNameDestination;
    }
}

?>
