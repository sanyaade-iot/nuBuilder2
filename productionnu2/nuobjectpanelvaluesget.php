<?php
    
    if(!function_exists('json_encode'))
    {
        function json_encode($a=false)
        {
            // Some basic debugging to ensure we have something returned
            if (is_null($a)) return 'null';
            if ($a === false) return 'false';
            if ($a === true) return 'true';
            if (is_scalar($a))
            {
                if (is_float($a))
                {
                    // Always use '.' for floats.
                    return floatval(str_replace(',', '.', strval($a)));
                }
                if (is_string($a))
                {
                    static $jsonReplaces = array(array('\\', '/', "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
                }
                else
                    return $a;
            }
            $isList = true;
            for ($i = 0, reset($a); true; $i++) {
                if (key($a) !== $i)
                {
                    $isList = false;
                    break;
                }
            }
            $result = array();
            if ($isList)
            {
                foreach ($a as $v) $result[] = json_encode($v);
                return '[' . join(',', $result) . ']';
            }
            else
            {
                foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
                return '{' . join(',', $result) . '}';
            }
        }
    }    
    
    header('Content-type: application/json');
	header("Cache-Control: no-cache, must-revalidate");
    
    $zzsys_object_id    = $_GET['zzsys_object_id'];
    $dir                = $_GET['dir'];
    
    require_once("../$dir/database.php");
    require_once('common.php');
    
    $objQRY = nuRunQuery("
        SELECT * FROM zzsys_object WHERE zzsys_object_id = '$zzsys_object_id'
    ");
    $objJSON = mysql_fetch_object($objQRY);
    
    print json_encode($objJSON);
    
?>