<?php

namespace Good\Memory\Database;

class SQLiteResult
{
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function fetch()
    {
        $row = $this->result->fetchArray(SQLITE3_ASSOC);

        if ($row === false)
        {
            return null;
        }

        return $row;
    }
}
