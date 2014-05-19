<?php
namespace LogParser;

/* 
 * Key Value Log Parser 
 * Extends Log Parser Abstract for key value parsing functionality
 */
class KeyValue extends LogParserAbstract
{
    /**
     * Implement the format line method for log parser 
     * 
     * @param string $lineString current line string
     * @return array|false return array with formatted data or false if format failed
     */
    protected function formatLine($lineString)
    {
        // params for filtering
        $params = [
            'datetime' =>  ["/((\d+\/)+\d{4} (\d{2}:)+\d{2}\s(AM|PM))/", false],
            'ip' => ["/\s(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|unknown)\s/", false],
            'key_val' => ["/(.[^\s]*?)=(.*?)\s/", true],
        ];
        // params filtering
        $filteredParams = $this->filterParams($params, $lineString);
        
        // fill line data if all params is right
        if ($filteredParams['datetime'][1] && $filteredParams['ip'][1] 
            && $filteredParams['key_val'][1]
        ) { 
            $lineData = [];
            $lineData['datetime'] = $filteredParams['datetime'][1];
            $lineData['ip'] = $filteredParams['ip'][1];
            foreach ($filteredParams['key_val'][1] as $key => $val){
                $lineData[trim($val)] = $filteredParams['key_val'][2][trim($key)];
            }

            return $lineData;
        } 
        
        return false;
    }
}

