<?php
namespace LogParser;

/* 
 * Json Log Parser 
 * Extends Log Parser Abstract for json parsing functionality
 */
class Json extends LogParserAbstract
{
    /**
     * Implement the format line method for log parser 
     * 
     * @param string $lineString current line string
     * @return array|false return array with formatted data or false if format failed
     */
    protected function formatLine($lineString)
    {
        return json_decode($lineString, true);
    }
}

