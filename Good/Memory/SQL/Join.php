<?php

namespace Good\Memory\SQL;

class Join
{
    public $tableNumberOrigin;
    public $fieldNameOrigin;
    public $tableNameDestination;
    public $tableNumberDestination;
    public $fieldNameDestination;
    public $selectedFieldName;

    public function __construct($tableNumberOrigin, $fieldNameOrigin, $tableNameDestination, $tableNumberDestination, $fieldNameDestination, $selectedFieldName)
    {
        $this->tableNumberOrigin = $tableNumberOrigin;
        $this->fieldNameOrigin = $fieldNameOrigin;
        $this->tableNameDestination = $tableNameDestination;
        $this->tableNumberDestination = $tableNumberDestination;
        $this->fieldNameDestination = $fieldNameDestination;
        $this->selectedFieldName = $selectedFieldName;
    }
}

?>
