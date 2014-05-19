<?php
namespace LogParser;

use LogParser\Exception\LogParserException as LogParserException;
/**
 * Log Parser Abstract Class
 * Provides basic functionality for read log files and save formatted data to 
 * output file in csv format. 
 * 
 * @author Andrei Rosliuk
 */
abstract class LogParserAbstract
{
    /**
     * @const Set overwrite mode for output file writing
     */
    const LOG_PARSER_OVERWRITE = 'w';
    
    /**
     * @const Set write to end mode for output file writing
     */
    const LOG_PARSER_CONTINUE = 'a';
    
    /**
     * Count of lines
     * 
     * @var int
     */
    private $_linesCount = 0;
    
    /**
     * Path to the Input Log File
     * 
     * @var string
     */
    private $_inFile = false;
    
    /**
     * Name of output csv file
     * 
     * @var string
     */
    private $_outFile = false;
    
    /**
     * Write mode for output file
     * 
     * @var string
     */
    private $_writeMode = false;
    
    /**
     * Input File fields
     * 
     * @var array
     */
    private $_fields = false;
    
	/**
     * Path to the Input Log File
     * 
     * @var string
     */
    private $_isValid = false;
	
    /**
     * Constructor 
     * 
     * @param string $inFile log file path for parsing
     * @param string $outFile output csv file path 
     * @throws LogParserException
     */
    public function __construct($inFile = false, $outFile = false) 
    {
        // check intput file
        if (file_exists($inFile)) {
            $this->_inFile = $inFile;
        } else {
            throw new LogParserException("File not exists or empty path to the file");
        }
        // check output file and output file dir
        if ($outFile) {
            $dirname = dirname($outFile);
            if (!is_dir($dirname)) {
                if(!mkdir($dirname, 0777, true)) {
                    throw new LogParserException("Can't create dir for output file");
                }
            }
            $this->_outFile = $outFile;
        } else {
            throw new LogParserException("Output filename is empty");
        }
        // get fields from input file
		if ($fields = $this->_analyseFields()) {
			$this->_fields = $fields;
			// if has fields then set valid to the true
			$this->_isValid = true;
		}
    }
	
	/**
	* Search all params fields in an input file
	*
	* @return array of all fields
	* @throws LogParserException
	*/
	private function _analyseFields() 
	{
		$handle = @fopen($this->_inFile, 'r');
        $fields = [];
        if ($handle) {
            while (($lineString = fgets($handle, 4096)) !== false) {
				// get line data
                $lineData = $this->formatLine($lineString);
				if ($lineData) {
					// get fields of current line and merge difference with founded
					$currentFields = array_keys($lineData);
					$fields = array_merge($fields, array_diff($currentFields, $fields));
				}
            }
            fclose($handle);
            
            return $fields;
        } else {
            throw new LogParserException("Can't read file");
        }
	}
	
	/**
	* Check validation format param
	*
	* @return boolean
	*/
	public function isValidFormat() 
	{
		return $this->_isValid;
	}
    
    /**
     * Read input file and parse data to csv format 
     * 
     * @return int return count of parsed lines
     * @throws LogParserException
     */
    public function parse() 
    {
		if (!$this->isValidFormat()) {
			throw new LogParserException("File is empty or has a wrong format");
		}
        $handle = @fopen($this->_inFile, 'r');
        if ($handle) {
            while (($lineString = fgets($handle, 4096)) !== false) {
                $this->_incrementLinesCount();
                $linesCount = $this->getLinesCount(); 
                // overwrite data in output file if current line is first
                if ($linesCount == 1) {
                    $this->_writeMode = self::LOG_PARSER_OVERWRITE;
                } else {
                    $this->_writeMode = self::LOG_PARSER_CONTINUE;
                }
                // format data to a key value array
                $lineData = $this->formatLine($lineString);
                
                if ($lineData) {
                    // write a line to a csv file
                    $this->_writeFormattedLine($lineData);
                } else {
                    $this->errorLog("Line data is empty or has a wrong format");
                }
            }
            fclose($handle);
            
            return $this->getLinesCount();
        } else {
            throw new LogParserException("Can't read file");
        }
    }
    
     /**
     * Get lines count
     * 
     * @return int
     */
    public function getLinesCount() 
    {
        return $this->_linesCount;
    }
    
    /**
     * Abstract function for format line data
     * 
     * @param string $lineString current line string
     * @return array|false return formated data array or false if format failed
     */
    protected abstract function formatLine($lineString);
    
    /**
     * Filter params with reg expressions
     * 
     * @param array $params key as param name, value as array with regex
     *                      and regex compare mode
     * @param string $string string for filtering
     * @return array of filtered params
     */
    protected function filterParams($params, $string)
    {
        $filteredParams = [];
        foreach ($params as $param => $regex) {
            if (!is_array($regex) || !isset($regex[0]) || !isset($regex[1])) {
                throw new LogParserException("Wrong filter params format");
            }
            if ($regex[1]) {
                preg_match_all($regex[0], $string, $filteredParams[$param]);
            } else {
                preg_match($regex[0], $string, $filteredParams[$param]);
            }
        }
        return $filteredParams;
    }
    
    /**
     * Write errors to error.log
     * 
     * @param string $errorMessage error message
     * @return void;
     * @throws LogParserException 
     */
    protected function errorLog($errorMessage)
    {
        $handle = @fopen(__DIR__ . 'error.log', "a");
        if ($handle) {
            $errorString = date('Y-m-d H:i:s') . ' ';
            $errorString .= $this->getLinesCount() . ' ';
            $errorString .= $errorMessage . PHP_EOL;
            $isWritten = @fwrite($handle, $errorString);
            fclose($handle);
            if (!$isWritten) {
                throw new LogParserException("Can't save error string to a log file");
            }
        } else {
            throw new LogParserException("Can't create or open error.log");
        }
        return;
    }
    
    /**
     * Increment lines count
     * @return int count of lines
     */
    private function _incrementLinesCount() 
    {
        $this->_linesCount = $this->_linesCount + 1;
        return $this->_linesCount;
    }
    
    /**
     * Format data array values order by fields and convert to string
     * 
     * @param array $lineData key value array with formated data
     * @return formatted string
     */
    private function _getStringFormattedByFields($lineData) 
    {
        $string = '';
        // if line is first and isset fields then add fields header to the file 
        if ($this->getLinesCount() == 1 && $this->_fields) {
            foreach ($this->_fields as $field) {
                $string .= $field . ';';
            }
            $string .= PHP_EOL;
        }
        foreach ($this->_fields as $field) {
            $string .= isset($lineData[$field]) ? $lineData[$field] . ';' : ';';
        }
        $string .= PHP_EOL;
        return $string;
    }
    
    /**
     * Write a line to the csv file
     * 
     * @param array $lineData key value array with formated data
     * @return boolean
     * @throws LogParserException 
     */
    private function _writeFormattedLine($lineData)
    {
        $handle = @fopen($this->_outFile, $this->_writeMode);
        if ($handle) {
            if ($this->_fields) {
                // get formatted line string by fields
                $formattedString = $this->_getStringFormattedByFields($lineData);
                $isWritten = @fwrite($handle, $formattedString); 
            } else {
                $isWritten = @fputcsv($handle, $lineData, ";");
            }
           
            fclose($handle);
            if (!$isWritten) {
                $this->errorLog("Can't save parsed string to the file");
            } else {
                return true;
            }
        } else {
            throw new LogParserException("Can't create or open output csv file");
        }
        
        return false;
    }    
}