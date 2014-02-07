<?php

interface IRegistry
{
	const REGISTRY_TYPE_ARRAY = 1;

	public static function load();
	public static function add($itemId, $item);
	public static function get($itemId);
	public static function getAll();
	public static function remove($itemId);
	public static function removeAll();
}

?>