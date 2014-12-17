<?php

namespace AKL;

class Directive extends ParsableItem
{
	/**
	 * Simple hash that includes 'name' => 'closure'
	 * @var array
	 */
	protected $hash = array();

	/**
	 * Sets the parameters of the directive
	 * @param [array] $orig   the original symbol that is being replaced
	 * @param [closure] $action callback to be executed
	 * @param [array] $opts   optional options
	 */
	public function set( $orig, $action, $opts = [] )
	{
		$this->orig = $orig;
		$this->action = $action;
		$this->opts = $opts;

		$this->hash[$orig[0] .  '?' . $orig[1]] = $action;

		return $this;
	}

	/**
	 * Replaces the original symbol with the directive's callback return value
	 * @param  [string] $str template to be formatted
	 * @return [string]      string that has been transformed by the directives
	 */
	public function replace( $str )
	{
		$strCopy = $str;
		$resArray = [];

		$regex = Mason::delimiterizeRegex('(\b|\n|\t|\s|^)' . $this->escapeForRegex($this->orig[0]) . '(.){0,}' . $this->escapeForRegex($this->orig[1]));

 		$res = preg_match_all($regex, $str, $resArray);

		$resArray = array_shift($resArray);

		foreach( $resArray as $result )
		{
			$parameter = $this->getDirectiveParameter($result);
			$args = $this->getDirectiveArguments($result);

			$exploded = explode($result, $strCopy);
			$strCopy = implode( call_user_func_array( $this->action, [$parameter, $args] ), $exploded);
		}

		return $strCopy;
	}


	/**
	 * Extracts the parameter
	 * @param  string $capture 	captured string
	 * @return string          		parameter
	 */
	private function getDirectiveParameter( $capture )
	{
		$temp = $capture;

		foreach( $this->orig as $removable )
		{
			$temp = str_replace($removable, '', $temp);
		}

		return $temp;
	}

	/**
	 * Extracts the arguments from the captured string
	 * @param  string $str 	captured string
	 * @return array      	arguments as hash
	 */
	private function getDirectiveArguments( $str )
	{
		$temp = $str;
		$matchArr = [];

		$arguments = preg_match_all('#\[(.){0,}\]#', $str, $matchArr);

		if( ! empty($matchArr[0]) )
		{
			$parameters = $matchArr[0][0];
			$parameters = trim($parameters, "[]");
			$parameters = explode("=", $parameters);
			$collection = [];

			for( $i = 0; $i < count($parameters); $i = $i + 2 )
			{
				$collection[$parameters[$i]] = $parameters[$i + 1];
			}

			return $collection;
		}
		else
		{
			return [];
		}
	}
}