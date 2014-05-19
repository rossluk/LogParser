<?php
// include custom parser for json logs
require_once 'LogParser/Autoloader.php';

set_time_limit(0); // this line needed if log file is too big 

try {
    /*  Parser expects two required params:
     *	- first is a log file which need to be parsed;
     *	- second is an output csv file path, if file or dir not exists 
     *    it will be created.
     *	Third parameter is an array of an input file param names, 
     *  default it false. If you will use third param then in the 
     *  output file will be created header with the passed field
     *	names. Also all data from each lines will be placed in proper 
     *  column.
     *  
     *  If you want to create custom parser you need to read the readme.txt in
     *  log parsers dir
     */
	
    //This is example for parsing json log to csv with factory.
    $jsonParser = LogParser\LogParserFactory::createParser('input_file.log', 'output_file.csv'); 
    
    // default parse method. it's return lines count, error lines you can see 
    // in the error.log which place in parser dir.
    $jsonLogLinesCount = $jsonParser->parse(); 
    echo 'Json Log lines count: ' . $jsonLogLinesCount . '<br>';

    //This is example for parsing key value log. As you can see both methods the same.
	// It's because type of parser has been selected in Factory automatically.
    $keyValParser = LogParser\LogParserFactory::createParser('input_file_2.log', 'output_file_2.log');
    $kvLogLinesCount = $keyValParser->parse();
    echo 'Key=Value Log lines count: '  . $kvLogLinesCount;
	
	//You can also call each parser directly.
	$keyValParser = new LogParser\KeyValue('input_file_3.log', 'output_file_3.log');
	$kvLogLinesCount = $keyValParser->parse();
    
} catch (LogParser\Exception\LogParserException $e) {
    // if something important goes wrong then parser throw an exception
    echo $e->getMessage();
}

?>
