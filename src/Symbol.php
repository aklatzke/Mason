<?php

namespace AKL;

class Symbol extends ParsableItem
{

	private $original;
	private $replacement;
	protected $hash = array();

	public function set( $from, $to )
	{
		$this->original = $from;
		$this->replacement = $to;

		$this->hash[$from] = $to;

		return $this;
	}

	public function replace( $str )
	{
		$regex = Mason::delimiterizeRegex('(\b|\n|\t|\s|^)' . $this->escapeForRegex($this->original) . '(\n|\s|\t|\b|$)');
		return preg_replace($regex, $this->replacement, $str);
	}
}