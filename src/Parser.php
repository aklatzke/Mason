<?php

namespace AKL;

class Parser
{
	private $tokenList = [];

	public function parse( $path, $symbols, $directives )
	{
		$file = file_get_contents( $path );

		return $this->parseString($file, $symbols, $directives);
	}

	public function parseString( $file, $symbols, $directives )
	{
		$indexes = [];
		$lines = explode("\n", $file);
		if( is_array($lines) )
		{
				$temp = [];

				foreach ($lines as $lineNumber => $line)
				{
					foreach( $symbols as $symbol )
					{
						$line = $symbol->replace($line);
					}

					$temp[$lineNumber] = $line;
				}

				$file = implode("\n", $temp);
		}
		else
		{
			foreach( $symbols as $symbol )
			{
				$file = $symbol->replace($file);
			}
		}

		foreach ($directives as $index => $directive)
		{
			$newIndex = $directive->capture($file);
			$indexes = array_merge($indexes, $newIndex);

			foreach($newIndex as $thisIndex)
			{
				$this->tokenList[ $thisIndex["token"] ] = $thisIndex;
			}

			$file = $directive->setTokens($file);
		}

		return $this->replaceTokens( $file );
	}

	public function replaceTokens( $file )
	{
		$indexes = $this->tokenList;

		foreach ($indexes as $token => $item)
		{
			// THIS NEEDS TO BE CALCULATED AT RUNTIME NOT PREVIOUSLY
			if( strpos($file, $token) > -1 )
			{
				$result = call_user_func_array($item['action'], $item['actionArgs']);
				$file = str_replace($token, Mason::EOL($result), $file);
			}
		}

		return $file;
	}

	protected function sortByIndex( Array $matches )
	{
		usort($matches, function($carry, $next){
			return $carry['index'] >= $next['index'];
		});

		return $matches;
	}
}
