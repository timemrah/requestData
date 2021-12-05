<?php


class RD //REQUEST DATA
{

    private static $inputValues = null;


    public static function GET(string $key = null){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){ return []; }

        if($_GET){
            $values = $_GET;
        } else{
            if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
            $values = self::$inputValues;
        }

        if($key === null){ return $values; }
        return self::dotNestedKeySearchFromArray($key, $values);

    }
    public static function positiveGET(string $key = null, $default = null){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){ return []; }

        $value = self::GET($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return (int) $value;
    }


    public static function POST(string $key = null){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){ return []; }

        if($key === null && $_POST){ return $_POST; }
        $value = self::dotNestedKeySearchFromArray($key, $_POST);
        if($value){ return $value; }

        //ALL VALUE TYPE
        if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
        if($key === null){ return self::$inputValues; }
        return self::dotNestedKeySearchFromArray($key, self::$inputValues);

    }
    public static function positivePOST(string $key = null, $default = null){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){ return []; }

        $value = self::POST($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return (int) $value;
    }


    public static function PUT(string $key = null){
        if($_SERVER['REQUEST_METHOD'] !== 'PUT'){ return []; }

        if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
        if($key === null){ return self::$inputValues; }
        return self::dotNestedKeySearchFromArray($key, self::$inputValues);
    }
    public static function positivePUT(string $key = null, $default = null){
        if($_SERVER['REQUEST_METHOD'] !== 'PUT'){ return []; }

        $value = self::PUT($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return (int) $value;
    }


    public static function DELETE(string $key = null){
        if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){ return []; }

        if($_GET){
            $values = $_GET;
        } else{
            if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
            $values = self::$inputValues;
        }

        if($key === null){ return $values; }
        return self::dotNestedKeySearchFromArray($key, $values);
    }
    public static function positiveDELETE(string $key = null, $default = null){
        if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){ return []; }

        $value = self::PUT($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return (int) $value;
    }





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


    private static function phpInputToValues():array{

        $nestedFormData = [];
        $formData = [];
        $lines = file('php://input');


        // JSON DATA TYPE :
        if(isJson($lines[0])){
            return json_decode($lines[0], true);
        }


        // X-WWW-FORM-URLENCODED DATA TYPE :
        if(getHeader('Content-Type') === 'application/x-www-form-urlencoded'){
            parse_str($lines[0], $formData);
            return $formData;
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
