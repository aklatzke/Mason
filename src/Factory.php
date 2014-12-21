<?php

namespace AKL;

class Factory
{
	/**
	 * The name of the object that will be factorized
	 * Can include namespace definition (e.g. 'AKL\\Factory')
	 * @var string
	 */
	protected $progeny = '';

	/**
	 * Creates an object of type $progeny
	 * @return [mixed] Created $progeny or false on failure
	 */
	public function create(  )
	{
		if( $this->progeny )
			return new $this->progeny;
		else
			return false;
	}
}