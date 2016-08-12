<?php
class Min_NewAtt_Helper_Report extends Mage_Catalog_Helper_Output {
    private static $data = array();
    private static $xmlTableTag = 'Products';
    private static $xmlRowTag = 'Product';
    private static $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
    private static $xml = '';

    /**
     * Set input data
     * @param Array $data
     * @param String $xmlTableTag
     * @param String $xmlRowTag
     * @return Response
     */
    public function setData($data = array(), $xmlTableTag = '', $xmlRowTag = ''){

        if($data){
            self::$data = $data;
        }

        if($xmlTableTag){
            self::$xmlTableTag = $xmlTableTag;
        }

        if($xmlRowTag){
            self::$xmlRowTag = $xmlRowTag;
        }

        self::buildXML();

        return true;
    }

    /**
     * Display XML
     * @return Response
     */
    public function display(){

        if(self::$data){

            header('Content-type: application/xml');

            echo self::$xmlHeader;
            echo self::$xml;

            return;
        }
        echo 'Data is empty.';
    }

    /**
     * Get XML
     * @return Response XML
     */
    public static function getXML(){
        return self::$xmlHeader.self::$xml;
    }

    /**
     * Force Object to Array
     * @param Array/Object $data
     * @return Array
     */
    private static function objectToArray($data){

        if(is_object($data)){

            $result = array();

            foreach($data as $key => $value){
                $result[$key] = $value;
            }

            return $result;
        }

        return $data;
    }

    /**
     * Build XML
     * @return Bool
     */
    private static function buildXML(){

        foreach(self::$data as $row){

            $row = self::objectToArray($row);

            $xml = '';
            foreach($row as $key => $value){

                if(is_bool($value)){
                    $value = $value === true ? 'true' : 'false';
                }

                $xml .= self::tag($key, '<![CDATA['.$value.']]>');
            }

            self::$xml .= self::tag(self::$xmlRowTag, $xml);
        }

        self::$xml = self::tag(self::$xmlTableTag, self::$xml);

        return true;
    }

    /**
     * Build Tag
     * @return String
     */
    private static function tag($tagName = '', $value = ''){
        return '<'.$tagName.'>'.$value.'</'.$tagName.'>';
    }

    /**
     * Format Currentcy
     * @return number
     */
    public function currency($number = 0, $seperator = ',', $fseperator = '.') {
        return number_format((int) $number, 0, $fseperator, $seperator);
    }

    /**
     * Remove accents
     * @param  [type] $str       [description]
     * @param  string $separator [description]
     * @return [type]            [description]
     */
    public static function remove_accents($str, $separator = ' ') {
        $str = preg_replace("/(à|á|?|?|ã|â|?|?|?|?|?|?|?|?|?|?|?)/", 'a', $str);
        $str = preg_replace("/(è|é|?|?|?|ê|?|?|?|?|?)/", 'e', $str);
        $str = preg_replace("/(ì|í|?|?|?)/", 'i', $str);
        $str = preg_replace("/(ò|ó|?|?|õ|ô|?|?|?|?|?|?|?|?|?|?|?)/", 'o', $str);
        $str = preg_replace("/(ù|ú|?|?|?|?|?|?|?|?|?)/", 'u', $str);
        $str = preg_replace("/(?|ý|?|?|?)/", 'y', $str);
        $str = preg_replace("/(?)/", 'd', $str);
        $str = preg_replace("/(À|Á|?|?|Ã|Â|?|?|?|?|?|?|?|?|?|?|?)/", 'A', $str);
        $str = preg_replace("/(È|É|?|?|?|Ê|?|?|?|?|?)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|?|?|?)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|?|?|Õ|Ô|?|?|?|?|?|?|?|?|?|?|?)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|?|?|?|?|?|?|?|?|?)/", 'U', $str);
        $str = preg_replace("/(?|Ý|?|?|?)/", 'Y', $str);
        $str = preg_replace("/(?)/", 'D', $str);
        $str = str_replace(array("&quot;", ":", ".", "'", ",", ";", ")", "(", "?", "@", "%", "*", "&", "^", "!", "=", "{", "}", "\\", '"', '-', '‘', '’', '•'), " ", $str);
        $str = trim($str);
        $str = preg_replace('/\s+/', ' ', $str);
        $str = stripslashes($str);
        $str = str_replace(array(' ', '--', '|', "/", '_', "[", "]", "+"), $separator, $str);
        $str = strtolower($str);
        return $str;
    }

    /**
     * [aj_sub_string description]
     * @param  [type]  $str     [description]
     * @param  [type]  $len     [description]
     * @param  boolean $more    [description]
     * @param  string  $charset [description]
     * @return [type]           [description]
     */
    public static function aj_sub_string($str, $len, $more = '', $trim_space = false, $charset = 'UTF-8') {
        $str = html_entity_decode($str, ENT_QUOTES, $charset);
        $str = strip_tags($str);
        if (mb_strlen($str, $charset) > $len && mb_strpos($str, ' ') !== false) {
            $arr = explode(' ', $str);
            $str = mb_substr($str, 0, $len, $charset);
            $arrRes = explode(' ', $str);
            $last = $arr[count($arrRes) - 1];
            unset($arr);
            if (strcasecmp($arrRes[count($arrRes) - 1], $last)) {
                unset($arrRes[count($arrRes) - 1]);
            }
            $str = strip_tags(implode(' ', $arrRes)).$more;
            return $trim_space ? self::trim_space($str) : $str;
        }
        return $str;
    }

    /**
     * [trim_space description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public static function trim_space($string) {
        $string = preg_replace('/\s+/', ' ', $string);
        return $string;
    }
}