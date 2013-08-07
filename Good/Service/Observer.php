<?php

namespace Good\Service;

interface Observer
{
    public function notifyObserver(Observable $observable);
}

?>