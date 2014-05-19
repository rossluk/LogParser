
    If you want to create your custom parser, you just need to implement 
    formatData($lineString) method in your custom parser class. Your custom 
    class must extend LogParserAbstract. Also you have to add your custom 
	class name to the type array in LogParserFactory, if you want to create
	parsers through the factory.
    FormatData expects current line string of log file and must return 
    array of keys and values like:

            ['param1' => val, 'param2' => val, 'paramN' => val];

    Or return false if formatting failed.

    You also can use the filterParams() method in your implementation of 
    formatData method if you want to filter the params with custom way. 
    It just a wrap for preg_match and preg_macth_all methods.
    Data for filterParam Method must be like:

            ['paramName' => ['regexForFiltering', mode]];

            mode - must be true or false. if mode is true filter 
                   use preg_match_all else it use preg_match.
 