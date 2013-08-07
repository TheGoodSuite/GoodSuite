<?php

namespace Good\Memory\Database;

interface Database
{
    public function query($sql);
    public function getLastInsertedId();
    public function escapeText($text);
    public function getResult();
}

?>