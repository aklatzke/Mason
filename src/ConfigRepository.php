<?php

namespace AKL;

class ConfigRepository
{
	public $config = array();

	public function set( $key, $value )
	{
		$config[$key] = $value;

		return $this;
	}

	public function get( $key )
	{
		return $config[$key];
	}
}