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

	public function build( $path, $options = [] )
	{
		$options = array_merge( ["compiledName" => 'template', "with" => [] ], $options );
		var_dump($options);
		return file_put_contents(
			Mason::getCompiledPath() . $options['compiledName'] . '.php' ,
			$this->parser->parse(
				$path,
				$this->filterByGroups($options['with'], $this->symbols),
				$this->filterByGroups($options['with'], $this->directives)
				)
			);
	}

	public function buildString( $str, $options = [] )
	{
		$options = array_merge( ["compiledName" => 'template', "with" => [] ], $options );

		return file_put_contents(
			$options['compiledName'],
			$this->parser->parseString(
				$str,
				$this->filterByGroups($options['with'], $this->symbols),
				$this->filterByGroups($options['with'], $this->directives)
				)
			);
	}

	public function filterByGroups( $groups, $items )
	{
		if( empty($groups) ) return $items;

		$filtered = [];

		foreach( $items as $item )
		{
			$group = $item->getGroup();

			if( array_search($group, $groups) !== false ) $filtered[] = $item;
		}

		return $filtered;
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

	public function symbolMap( $arr, $group = '')
	{
		foreach( $arr as $original => $replacement )
		{
			$this->symbols[] = $this->symbolFactory->create()->set( $original, $replacement )->group($group);
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