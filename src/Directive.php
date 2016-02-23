<?php

namespace AKL;

use AKL\Interfaces\Parsable;

class Directive extends ParsableItem implements Parsable
{
	/**
	 * Simple hash that includes 'name' => 'closure'
	 * @var array
	 */
	protected $hash = array();
	protected $history = [];

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

		$this->opts = array_merge([
			"argDelim" => ["[", "]"],
			"argAssign" => "=",
			"argSeparator" => ",",
			"_defaults" => []
		], $opts);

		$this->hash[$orig[0] .  '?' . $orig[1]] = $action;

		return $this;
	}

	/**
	 * Replaces the original symbol with the directive's callback return value
	 * @param  [string] $str template to be formatted
	 * @return [string]      string that has been transformed by the directives
	 */
	public function capture( $document )
	{
		if( isset($this->history[md5($document)]) )
			return $this->history[md5($document)];

		$resArray = [];
		$docArray = [];

		$mod = ".";
		if( $this->orig[1] === "\n" )
			$mod = "[^\n]";

		$matchLineStartNonCapturing = '(?:\b|\n|\t|\s|^)';
		$openingParameter = $this->escapeForRegex($this->orig[0]);
		$maybeASpace = '( )?';
		$matchAnything = '(' . $mod . '{0,})';
		$closingParameterNonCapturing = '(?:' . $this->escapeForRegex($this->orig[1]) . ')';
		$dotMatchesNewLinesFlag = "s";

		$regex = Mason::delimiterizeRegex("{$matchLineStartNonCapturing}{$openingParameter}{$maybeASpace}{$matchAnything}{$closingParameterNonCapturing}") . $dotMatchesNewLinesFlag;;

		$matches = $this->getCapturePositions( $regex, $document );

		$withValues = $matches;

		$tempMatches = [];
		foreach ($matches as $index => $match)
		{
			$tempMatches = array_merge($tempMatches, $this->checkSiblings($match));
		}

		foreach ($matches as $index => $value)
		{
			$value['capture'] = $this->checkSiblings($value['capture']);

			list($parameters, $content) = $this->getDirectiveParameters($value['capture']);
			$args = array_merge($this->opts['_defaults'], $this->getDirectiveArguments($value['capture']));

			$withValues[$index]['args'] = $args;
			$withValues[$index]['param'] = $parameters;
			$withValues[$index]['origCapture'] = $value['capture'];
			$withValues[$index]['token'] = $this->createToken($value['capture']);
			$withValues[$index]['action'] = $this->action;
			$withValues[$index]['actionArgs'] = [$parameters, $content, $args];
		}

		$this->history[md5($document)] = $withValues;

		return $withValues;
	}

	public function setTokens( $document )
	{
		$captures = $this->capture($document);

		foreach ($captures as $index => $capture)
		{
			$token = $this->createToken($capture['origCapture']);

			$document = Mason::EOL(str_replace($capture['origCapture'], $token, $document));
		}

		return $document;
	}

	/**
	 * Extracts the parameter
	 * @param  string $capture 	captured string
	 * @return string          		parameter
	 */
	private function getDirectiveParameters( $capture )
	{
		$temp = $capture;
		$paramTemp = [];

		$lines = array_values(array_filter(explode("\n", $temp)));

		$firstLine = str_replace($this->orig[0], "", $lines[0]);
		$parameters = array_filter(explode(" ", trim($firstLine)));

		$keyVals = array_search($this->opts['argDelim'][0], $parameters);

		if( $keyVals !== false  )
		{
			$firstLineParams = array_slice($parameters, 0, $keyVals);

			if( is_array( $firstLineParams ) )
			{
				foreach ($firstLineParams as $index => $value)
				{
					$paramTemp[] = $value;
				}
			}

			$parameters = $paramTemp;
		}

		# unset the first and last line to capture the content
		$content = $lines;

		unset($content[count($content) - 1]);
		unset($content[0]);

		$content = implode("\n", $content);

		return [$parameters, $content];
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
		$argDelim = $this->opts['argDelim'];
		$argAssign = $this->opts['argAssign'];
		$argSeparator = $this->opts['argSeparator'];

		$regex = "#\\" . $argDelim[0] . "(.){0,}\\" . $argDelim[1] . "(\b| )?#";
		$arguments = preg_match_all($regex, $str, $matchArr);

		if( ! empty($matchArr[0]) )
		{
			$parameters = $matchArr[0][0];
			$parameters = trim($parameters, implode("", $argDelim));

			$parameters = explode($argSeparator, $parameters);

			$parameters = array_map(function($el) use ($argAssign){
					if( strpos($el, $argAssign) )
						return explode($argAssign, $el );
					else
						return $el;
			}, $parameters);

			$collection = [];
			foreach ($parameters as $index => $param)
			{
				if( isset($param[1]) )
					$collection[trim($param[0])] = trim($param[1]);
				else
					$collection[$param[0]] = $param[0];
			}

			return $collection;
		}
		else
		{
			return [];
		}
	}

	private function getRegexParts()
	{
		$matchLineStartNonCapturing = '(?:\b|\n|\t|\s|^)';
		$openingParameter = $this->escapeForRegex($this->orig[0]);
		$maybeASpace = '( )?';
		$closingParameterNonCapturing = '(?:' . $this->escapeForRegex($this->orig[1]) . ')';
		$dotMatchesNewLinesFlag = "s";

		return [
			$matchLineStartNonCapturing,
			$openingParameter,
			$maybeASpace,
			$closingParameterNonCapturing,
			$dotMatchesNewLinesFlag
		];
	}

	private function checkSiblings($str)
	{
		$newStr = $str;

		return $newStr;
	}

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

					// this means we have a multi match that needs to be processed for validity.
					// There are two options here:
					// 1) The tags are siblings e.g.:
					// :div
					//
					// :enddiv
					//
					// :div
					//
					// :enddiv
					//
					// 2) Tags are nested e.g.:
					// :div
					// 	:div
					// 	:enddiv
					// :enddiv
					list(
						$matchLineStartNonCapturing,
						$openingParameter,
						$maybeASpace,
						$closingParameterNonCapturing,
						$dotMatchesNewLinesFlag
					) = $this->getRegexParts();
					// lets take a greedy capture of the terms start and end set.
					// If it contains an opening tag, it means that it's a nested
					// set rather than siblings.
					// In this case, we should leave the capture alone as it's a parent > child
					// relationship.
					$mod = ".";
					if( $this->orig[1] === "\n" )
						$mod = "[^\n]";

					$matchAnythingNonGreedy = "({$mod}+?)";

					$regex = "{$matchLineStartNonCapturing}{$openingParameter}{$maybeASpace}{$matchAnythingNonGreedy}{$closingParameterNonCapturing}";
					$initialGreedyCapture = preg_match_all(Mason::delimiterizeRegex($regex) . $dotMatchesNewLinesFlag, $keyword, $matches);

					if( $matches && count($matches[0]) > 1 )
					{
						foreach ($matches[0] as $index => $capture)
						{
							$return[] = [
								"capture" => $capture,
								"index" => ""
							];
						}
					}
					else
					{
						$return[] = [
							"capture" => $keyword,
							"index" => $index,
						];
					}
				}
		}

		return $return;
	}
}
