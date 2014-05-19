<?php
namespace LogParser;

use LogParser\LogParserException as LogParserException;

class LogParserFactory 
{
	private static $_types = [
		'Json', 
		'KeyValue'
	];
	

	public static function createParser($inFile = false, $outFile = false)
	{
		foreach (self::$_types as $type) {
			$type = __NAMESPACE__ . '\\' . $type;
			$class = new $type($inFile, $outFile);
			$isValid = $class->isValidFormat();
			if ($isValid) {
				return $class;
			} else {
				$class = null;
			}
		}
		throw new LogParserException("Can't load parser, maybe file type is wrong");
	}
}