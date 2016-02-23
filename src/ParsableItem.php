<?php

namespace AKL;

class ParsableItem
{
	protected $hash;
	protected $group;

	public function getHash(  )
	{
		return $this->hash;
	}

	/**
	 * Escapes a string for regex search
	 * @param  [string] $str string to be sanitized
	 * @return [string]      string after sanitization
	 */
	protected function escapeForRegex( $str )
	{
		return str_replace('\\\n' ,'\n', preg_quote($str));
	}
	/**
	 * Adds a group flag on this item
	 * @param  string $str group name
	 * @return this
	 */
	public function group( $str )
	{
		$this->group = $str;

		return $this;
	}

	public function getGroup()
	{
		return $this->group;
	}

	public function createToken( $key )
	{
		$this->token = md5($key);

		return "##! > " . $this->token;
	}

	public function matchToken( $key )
	{

	}
	/**
	 * Returns array
	 */
	protected function getCapturePositions( $regex, $string )
	{
		$return = [];
		$res = preg_match_all($regex, $string, $matches, PREG_OFFSET_CAPTURE);

		$matches = $matches[0];

		if( $res )
		{
				foreach ($matches as $index => $match)
				{
					$keyword = $match[0];
					$index = $match[1];

					$return[] = [
						"capture" => $keyword,
						"index" => $index,
					];
				}
		}

		return $return;
	}
}
