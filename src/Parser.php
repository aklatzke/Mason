<?php

namespace AKL;

class Parser
{
	public function parse( $path, $symbols, $directives )
	{
		$file = file_get_contents( $path );

		return $this->parseString($file, $symbols, $directives);
	}

	public function parseString( $file, $symbols, $directives )
	{
		$lines = explode("\n",$file);
		$finished = [];
		echo "<pre>";
		foreach( $lines as $index => $line) :
			$finished[$index] = $line;

			foreach( $symbols as $symbol  )
			{
				$finished[$index] = $symbol->replace($finished[$index] );
			}

			foreach( $directives as $directive  )
			{
				$finished[$index] = $directive->replace($finished[$index] );
			}
		endforeach;


		return implode('', $finished);
	}
}