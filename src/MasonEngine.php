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

	public function build( $path, $targetPath )
	{
		$options = ["compiledName" => $targetPath, "with" => [] ];

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

		return $this->parser->parseString(
				$str,
				$this->filterByGroups($options['with'], $this->symbols),
				$this->filterByGroups($options['with'], $this->directives)
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

	public function setSymbol( $original, $replacement, $options )
	{
		return $this->symbols[] = $this->symbolFactory->create()->set( $original, $replacement, $options );
	}

	public function setDirective( $original, $callback, $options = [] )
	{
		return $this->directives[] = $this->directiveFactory->create()->set( $original, $callback, $options );
	}

	public function symbolMap( $arr, $options, $group = '')
	{
		foreach( $arr as $original => $replacement )
		{
			$this->symbols[] = $this->symbolFactory->create()->set( $original, $replacement, $options )->group( $group );
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
