<?php

namespace AKL;

final class Mason
{
	private static $engine;
	public static $templatePath;
	public static $partialPath;
	public static $compiledPath;
	public static $fileExtension;
	public static $regexDelimiter;
	public static $replacerArg;
	public static $document;
	public static $config;

	public static function start(  )
	{
		if( ! isset( self::$engine ) )
		{
			self::$engine = new MasonEngine(  new SymbolFactory, new DirectiveFactory, new Parser );
		}

		if( ! isset( self::$config ) )
		{
			self::$config = new ConfigRepository(  );
		}

		return self::$engine;
	}

	public static function setTemplatePath( $path )
	{
		return self::$templatePath = $path;
	}

	public static function setPartialPath( $path )
	{
		return self::$partialPath = $path;
	}

	public static function setCompiledPath( $path )
	{
		return self::$compiledPath = $path;
	}

	public static function getTemplatePath(  )
	{
		return self::$templatePath;
	}

	public static function getPartialPath(  )
	{
		return self::$partialPath;
	}

	public static function getCompiledPath(  )
	{
		return self::$compiledPath;
	}

	public static function setFileExtension( $ext )
	{
		return self::$fileExtension = $ext;
	}

	public static function setRegexDelimiter( $char )
	{
		return self::$regexDelimiter = $char;
	}

	public static function setReplacerArg( $chars )
	{
		return self::$replacerArg = $chars;
	}

	public static function getFileExtension(  )
	{
		return self::$fileExtension;
	}

	public static function build(  $fileName, $compiledName )
	{
		self::start();

		return  self::$engine->build(  self::$templatePath . $fileName . self::$fileExtension, $compiledName );
	}

	public static function buildString( $str, $options = [] )
	{
		self::start();

		return self::$engine->buildString( $str, $options );
	}

	public static function process(  $fileName, $compiledName = '', $partial = false )
	{
		self::start();

		$path = $partial  ? self::$partialPath : self::$templatePath;

		return  self::$engine->process(  $path . $fileName . self::$fileExtension, $compiledName );
	}

	public static function symbol( $to, $from, $options )
	{
		self::start();

		return self::$engine->setSymbol($to, $from, $options);
	}

	public static function directive( $key, $callback, $options = [] )
	{
		self::start();

		return self::$engine->setDirective( $key, $callback, $options );
	}

	public static function getSymbolMap(  )
	{
		self::start();

		return self::$engine->getSymbolMap();
	}

	public static function symbolMap( $arr, $options, $group = '' )
	{
		self::start();

		return self::$engine->symbolMap($arr, $options, $group);
	}

	public static function delimiterizeRegex( $str )
	{
		return self::$regexDelimiter . $str . self::$regexDelimiter;
	}

	public static function stripSpaces( $str )
	{
		return trim(preg_replace('(\|\t)', ' ', $str));
	}

	public static function EOL( $str )
	{
		return $str . "\n";
	}

	public static function PHP( $str )
	{
	 	return "<?php $str ?>";
	}

	public static function config( $key, $value = false )
	{
		self::start();

		if( $value )
		{
			return self::$config->set( $key, $value );
		}
		else
		{
			return self::$config->get( $key );
		}
	}

}
