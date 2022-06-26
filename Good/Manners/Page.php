<?php

namespace Good\Manners;

class Page
{
    private $size;
    private $startAt;

    public function __construct(int $size, int $startAt = 0)
    {
        $this->size = $size;
        $this->startAt = $startAt;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getStartAt()
    {
        return $this->startAt;
    }

    public function next()
    {
        if ($this->startAt === null)
        {
            $startAt = 0;
        }
        else
        {
            $startAt = $this->startAt;
        }

        return new Page($this->size, $startAt + $this->size);
    }
}

?>
