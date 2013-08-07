<?php

namespace Good\Service;

interface Observable
{
    public function registerObserver(Observer $observer);
    public function unregisterObserver(Observer $observer);
}

?>