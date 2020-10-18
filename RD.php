<?php


class RD //REQUEST DATA
{

    private static $inputValues = null;



    public static function GET(string $key = null){
        if($key === null){ return $_GET; }
        return self::dotNestedKeySearchFromArray($key, $_GET);
    }
    public static function positiveGET(string $key = null, $default = null){
        $value = self::GET($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return $value;
    }


    public static function POST(string $key = null){
        if($key === null){ return $_POST; }
        return self::dotNestedKeySearchFromArray($key, $_POST);
    }
    public static function positivePOST(string $key = null, $default = null){
        $value = self::POST($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return $value;
    }


    public static function PUT(string $key = null){
        if(self::$inputValues === null){ self::$inputValues = self::phpInputToValues(); }
        if($key === null){ return self::$inputValues; }
        return self::dotNestedKeySearchFromArray($key, self::$inputValues);
    }
    public static function positivePUT(string $key = null, $default = null){
        $value = self::PUT($key);
        if(!is_numeric($value) || $value < 1){ return $default; }
        return $value;
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

    private static function phpInputToValues(){

        $nestedFormData = [];
        $formData = [];
        $lines = file('php://input');

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