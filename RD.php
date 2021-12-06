<?php




class Req
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




    public static function getRawInputContent($type = null){
        if($type === 'line'){
            return file('php://input');
        }

        return file_get_contents('php://input');
    }




    public static function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }




    public static function forceMethod($method){

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




    public static function isMethod($val):bool{
        return ($_SERVER['REQUEST_METHOD'] === $val);
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

        $formData = [];
        $lines = self::getRawInputContent('line');


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
        $dataString = "";
        foreach($lines as $i =>  $line){
            $searchFieldLine = 'Content-Disposition: form-data; name="'; //38 characters
            if(strpos($line, $searchFieldLine) !== false){
                $key = substr($line, strlen($searchFieldLine), -3);
                $value = trim($lines[$i + 2]);
                $dataString .= "{$key}={$value}&";
            }
        }
        $dataString = substr($dataString, 0, -1);
        parse_str($dataString, $formData);
        return $formData;
    }




}
