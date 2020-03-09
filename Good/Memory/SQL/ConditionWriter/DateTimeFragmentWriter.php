<?php

namespace Good\Memory\SQL\ConditionWriter;

use Good\Memory\SQLStorage;

class DateTimeFragmentWriter extends ScalarFragmentWriter
{
    private $storage;

    public function __construct(SQLStorage $storage)
    {
        $this->storage = $storage;
    }

    protected function parseScalar($value)
    {
        return $this->storage->parseDatetime($value);
    }
}

?>
