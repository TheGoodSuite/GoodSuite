<?php

interface GoodMemoryDatabase
{
	public function query($sql);
	public function getLastInsertedId();
	public function escapeText($text);
	public function getNextResult();
}

?>