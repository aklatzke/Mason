<?php

namespace AKL;

class MasonEngine
{
	public $symbols = [];
	public $directives = [];
	private $symbolFactory;
	private $directiveFactory;

	public function __construct( SymbolFactory $factory,  DirectiveFactory $dFactory, Parser $parser )
	{
		$this->symbolFactory = $factory;
		$this->parser = $parser;
		$this->directiveFactory = $dFactory;
	}

	public function build( $path, $compiledName = 'template' )
	{
		return file_put_contents(Mason::getCompiledPath() . $compiledName . '.php' , $this->parser->parse($path, $this->symbols, $this->directives) );
	}

	public function buildString( $str, $outputPath )
	{
		return file_put_contents( $outputPath , $this->parser->parseString($str, $this->symbols, $this->directives) );
	}

	public function process( $path, $compiledName ='template'  )
	{
		return $this->parser->parse($path, $this->symbols, $this->directives);
	}

	public function setSymbol( $original, $replacement )
	{
		return $this->symbols[] = $this->symbolFactory->create()->set( $original, $replacement );
	}

	public function setDirective( $original, $callback )
	{
		return $this->directives[] = $this->directiveFactory->create()->set( $original, $callback );
	}

	public function symbolMap( $arr )
	{
		foreach( $arr as $original => $replacement )
		{
			$this->symbols[] = $this->symbolFactory->create()->set($original, $replacement );
		}
	}

	public function getSymbolMap(  )
	{
		$collection = [];

		foreach( $this->symbols as $key => $symbol )
		{
			$collection[] = $symbol->getHash();
		}

		return $collection;
	}
}