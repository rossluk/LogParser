<?php
namespace LogParser;

use LogParser\Exception\LogParserException as LogParserException;

class LogParserFactory 
{
    public static function createParser($inFile = false, $outFile = false)
    {       
        if (file_exists($inFile)) {
            preg_match('/\w+-(\w+)-\d/', $inFile, $result);
            $className = $result[1] ? __NAMESPACE__ . '\\' . $result[1] : false;
            if (class_exists($className)) {
                return new $className($inFile, $outFile);
            } else {
                throw new LogParserException("Can't load parser, maybe file type is wrong");
            }
        } else {
            throw new LogParserException("File not exists or empty path to the file");
        }
    }
}