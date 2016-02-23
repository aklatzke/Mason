<?php

namespace AKL;

use AKL\Interfaces\Parsable;

class Symbol extends ParsableItem implements Parsable
{

	private $original;
	private $replacement;
	protected $hash = array();

	public function set( $from, $to, $options )
	{
		$this->original = $from;
		$this->replacement = $to;

		$this->opts = array_merge([
			"argDelim" => ["[", "]"],
			"argSeparator" => " "
		], $options);

		$this->hash[$from] = $to;

		return $this;
	}

	public function replace( $str )
	{
		$regex = Mason::delimiterizeRegex('(?:\b|\t|^| )' . $this->original . ' ?(?:' . $this->opts['argDelim'][0] . '(.+)' . $this->opts['argDelim'][1] . '){0,1}(?:\b|$)');
		$replacement = "";

		if( is_callable( $this->replacement) && strpos($str, $this->opts['argDelim'][0] ) > -1 )
		{

			preg_match_all($regex, $str, $matches);

			$match = $matches[1][0];

			$match = str_replace($this->opts['argDelim'][0], "", $match);
			$match = trim(str_replace($this->opts['argDelim'][1], "", $match));
			$args = explode($this->opts['argSeparator'], $match);

			$replacement = call_user_func_array($this->replacement, $args);
		}
		
		$res = preg_replace($regex, $replacement, $str);

		return $res ? $res : $str;
	}

	public function capture($string){}

	public function setTokens($string){}
}
