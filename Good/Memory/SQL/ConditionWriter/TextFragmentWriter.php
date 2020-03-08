<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Memory\SQLStorage;

class TextFragmentWriter extends ScalarFragmentWriter
{
    private $storage;

    public function __construct(SQLStorage $storage, $field)
    {
        parent::__construct($field);

        $this->storage = $storage;
    }

    protected function parseScalar($value)
    {
        return $this->storage->parseText($value);
    }
}

?>
