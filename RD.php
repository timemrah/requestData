<?php




class Request
{



    private static $inputValues = null;




    public static function queryStr(string $key = null){

        if($key === null){
            return $_GET;
        }

        return self::dotNestedKeySearchFromArray($key, $_GET);
    }




    public static function positiveQueryStr($key, $default = null){

        $value = self::queryStr($key);

        if(!is_numeric($value) || $value < 1){
            return $default;
        }

        return $value;
    }




    public static function body(string $key = null){

        $values = ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST) ? $_POST : self::getInputValues();
        if($key === null){
            return $values;
        }

        return self::dotNestedKeySearchFromArray($key, $values);

    }




    public static function positiveBody($key, $default = null){

        $value = self::body($key);

        if(!is_numeric($value) || $value < 1){
            return $default;
        }

        return $value;
    }




    public static function header(string $key = null){

        $headers = apache_request_headers();
        if($key === null){
            return $headers;
        }

        if(isset($headers[$key])){
            return $headers[$key];
        }

        return null;

    }




    public static function data(string $key = null){

        $queryString = (array) self::queryStr();
        $body        = (array) self::body();
        $header      = (array) self::header();
        $mergedData   = array_merge($header, $queryString, $body);

        if($key === null){
            return $mergedData;
        }

        return self::dotNestedKeySearchFromArray($key, $mergedData);

    }




    public static function isMethod($method){

        if($method !== $_SERVER['REQUEST_METHOD']){
            return new class{
                static function queryStr(){}
                static function positiveQueryStr(){}
                static function body(){}
                static function positiveBody(){}
                static function header(){}
            };
        }

        return Request::class;
    }








    // PRIVATE :
    private static function formValueTypeCorrection($value){
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || strpos($value, ',') !== false){
                //is double
                $value = (float) $value;
            } else{
                //is integer
                $value = (int) $value;
            }
        }
        return $value;
    }




    private static function dotNestedKeySearchFromArray($dotNestedKey, $array){
        $parts    = strpos($dotNestedKey, '.') ? explode('.', $dotNestedKey) : [$dotNestedKey];
        $delegate = $array;

        foreach($parts as $part){
            if(isset($delegate[$part])){
                $delegate = $delegate[$part];
                continue;
            }
            return null;
        }

        return self::formValueTypeCorrection($delegate);;
    }




    private static function getInputValues(){
        if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
        return self::$inputValues;
    }




    private static function phpInputToValues():array{

        $nestedFormData = [];
        $formData = [];
        $lines = file('php://input');


        // X-WWW-FORM-URLENCODED DATA TYPE :
        if(getHeader('Content-Type') === 'application/x-www-form-urlencoded'){
            parse_str($lines[0], $formData);
            return $formData ?? [];
        }


        // JSON DATA TYPE :
        if(isJson($lines[0])){
            return json_decode($lines[0], true);
        }


        // FORM-DATA DATA TYPE :
        //RAW KEY VALUE CREATE
        foreach($lines as $i =>  $line){
            $searchFieldLine = 'Content-Disposition: form-data; name="'; //38 characters
            if(strpos($line, $searchFieldLine) !== false){
                $key = substr($line, strlen($searchFieldLine), -3);
                $value = trim($lines[$i + 2]);
                $formData[$key] = $value;
            }
        }




        //RAW INPUT DATA TO NESTED KEY VALUE
        foreach($formData as $rawKeys => $value){

            $nestedInputData = [];
            $delegate = &$nestedInputData;

            //NESTED KEY PARSE
            $dirtyNestedKeys = explode('[', $rawKeys);
            foreach($dirtyNestedKeys as $dirtyKey){
                $key = $dirtyKey;
                //CLEAR DIRTY KEY
                if(substr($dirtyKey, -1) === ']'){
                    $key = substr($dirtyKey, 0, -1);
                }
                $delegate = &$delegate[$key];
            }
            $delegate = $value;
            $nestedFormData = array_merge_recursive($nestedFormData, $nestedInputData);
        }

        return $nestedFormData;
    }




}
