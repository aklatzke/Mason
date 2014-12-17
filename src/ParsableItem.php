<?php

namespace AKL;

class ParsableItem
{
	protected $hash;

	public function getHash(  )
	{
		return $this->hash;
	}

	/**
	 * Just runs preg_quotes but exists if additional sanitization is ever needed
	 * @param  [string] $str string to be sanitized
	 * @return [string]      string after sanitization
	 */
	protected function escapeForRegex( $str )
	{
		return str_replace('\\\n' ,'\n', preg_quote($str));
	}
}