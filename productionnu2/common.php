<?php
/*
** File:           common.php
** Author:         nuSoftware
** Created:        2007/04/26
** Last modified:  2013/09/30
**
** Copyright 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012 nuSoftware
**
** This file is part of the nuBuilder source package and is licensed under the
** GPLv3. For support on developing in nuBuilder, please visit the nuBuilder
** wiki and forums. For details on contributing a patch for nuBuilder, please
** visit the `Project Contributions' forum.
**
**   Website:  http://www.nubuilder.com
**   Wiki:     http://wiki.nubuilder.com
**   Forums:   http://forums.nubuilder.com
*/

set_time_limit(0);
mb_internal_encoding('UTF-8');

$GLOBALS['nuRunQuery'] = 0;
$GLOBALS['nuEvent']    = '';
require_once('dbfunctions.php');
$GLOBALS['nuSetup']    = nuSetup();

function nuSubformArray($pSubformName, $pJustUnticked = false){

	$Rows             = $_POST["rows$pSubformName"];
	$a                = Array();
	for($i = 0 ; $i < $Rows ; $i ++){
		$p            = $pSubformName . right('000'.$i,4);

		if($pJustUnticked){
			if($_POST["row$p"]  != 'on'){
				$a[]  = $p;
			}
		}else{
			$a[]      = $p;
		}
	}
	return $a;
}
			

function nuTranslate($pPhrase){

	$setup    = $GLOBALS['nuSetup'];
	$phrase   = mysql_real_escape_string($pPhrase);
	$t        = nuRunQuery("SELECT trl_translation FROM zzsys_translate WHERE trl_language = '$setup->set_language' AND trl_english = '$phrase'");
	$r        = db_fetch_row($t);
	if($r[0] == ''){
		return str_replace( "'", "&#146",$pPhrase);
	}else{
		return str_replace( "'", "&#146",$r[0]);
	}

}
			
function nuTranslateArray($pPhrase){

	return str_replace( "&#146", "\\'", nuTranslate($pPhrase));

}
			
			
			
function getCSS($pClass, $pProperty){

	$s  = "SELECT ssp_property_value FROM zzsys_style_property ";
	$s .= "INNER JOIN zzsys_style ON ssp_zzsys_style_id = zzsys_style_id ";
	$s .= "WHERE sst_class = '$pClass' ";
	$s .= "AND ssp_property_name = '$pProperty'";
	$t  = nuRunQuery($s);
	$r  = db_fetch_row($t);
	return $r[0];
}

function jsinclude($pfile){
	$timestamp = date("YmdHis", filemtime($pfile));        // Add timestamp so javascript changes are effective immediately
	print "<script type='text/javascript' src='$pfile?ts=$timestamp' language='javascript'></script>\n";
}

function cssinclude($pfile){
	$timestamp = date("YmdHis", filemtime($pfile));        // Add timestamp so javascript changes are effective immediately
	print "<link rel='stylesheet' href='$pfile?ts=$timestamp' />\n";
}

// Make a hex code lighter in shade
function LighterHex($hex,$factor = 40){ 

	$hex = ColorToHex($hex);
    $new_hex = ''; 
    
    $base['R'] = hexdec($hex[0].$hex[1]); 
    $base['G'] = hexdec($hex[2].$hex[3]); 
    $base['B'] = hexdec($hex[4].$hex[5]); 
    
    foreach ($base as $k => $v) { 
        $amount = 255 - $v; 
        $amount = $amount / 100; 
        $amount = round($amount * $factor); 
        $new_decimal = $v + $amount; 
    
        $new_hex_component = dechex($new_decimal); 
        if (strlen($new_hex_component) < 2){ 
            $new_hex_component = "0".$new_hex_component; 
        }
        $new_hex .= $new_hex_component; 
    } 
    return $new_hex;
}


//----- returns url ie. 'https://www.nubuilder.com/productionnu/'
function getPHPurl() {
        if ($_SERVER[HTTPS] == 'on') {
                $start = "https://";
        } else {
                $start = "http://";
        }
        $base_host = $_SERVER[SERVER_NAME];
        $path = "";
        $pieces = explode("/", $_SERVER[SCRIPT_NAME]);
        for ($x=0; $x<count($pieces); $x++) {

                if (substr_count($pieces[$x], '.') == 0 ) {
                        $path = $path.$pieces[$x]."/";
                } else {
                        $x = count($pieces) + 1;
                }
        }
        $php_url = $start.$base_host.$path;
        return $php_url;
}

//-----setup php code just used for this database
$setup                           = $GLOBALS['nuSetup'];
$sVariables                      = recordToHashArray('zzsys_session', 'zzsys_session_id', $_GET['ses']);  //--session values (access level and user etc. )

$GLOBALS['nuEvent'] = '(nuBuilder PHP Library) : ';
eval(replaceHashVariablesWithValues($sVariables, getLib()));                                  //--replace hash variables then run code
$GLOBALS['nuEvent'] = '';

//--- see if activity can be run without being logged in
function activityPasswordNeeded($pReportID){

	$t = nuRunQuery("SELECT sat_all_zzsys_form_id FROM zzsys_activity WHERE sat_all_code = '$pReportID'");
	$r = db_fetch_row($t);
	return  passwordNeeded($r[0]);
	
}

// BEGIN - 2009/06/02 - Michael
setClientTimeZone();

function setClientTimeZone()
{
        global $setup;
        if ($setup->set_timezone)
                date_default_timezone_set($setup->set_timezone);
        else
                date_default_timezone_set("Australia/Adelaide");
} // func
// END - 2009/06/02 - Michael

//--- turn uniqid into a number

function hexNo($pLetter){
   $hex      = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');
   for($i=0;$i<count($hex);$i++){
      if($hex[$i]==$pLetter){return $i;}
   }
}

function buildIDNumber(){

   $id       = uniqid('1');
   $ar       = array_reverse(str_split($id));
   $value    = 0;

   for($i    = 0 ; $i < 7 ; $i++){
      $pow   = pow(16,$i) * hexNo($ar[$i]);
      $value = $value + $pow;
   }
   return $value;
}



function passwordNeeded($pFormID){

	$t   = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$pFormID'");
	$r   = db_fetch_object($t);
	return $r->sfo_access_without_login != '1';

}



function addToLog($pSQL){
	

}



function str_hex($string){

    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;

}


function hex_str($hex){

    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;

}



function makeCSS(){
	
	$class           = '*';
	$s               =      "SELECT * FROM `zzsys_style` ";
	$s               = $s . "INNER JOIN zzsys_style_property ON ";
	$s               = $s . "zzsys_style_id = ssp_zzsys_style_id ";
	$s               = $s . "ORDER BY sst_class DESC";
	$t               = nuRunQuery($s);
	$s               = "<style type='text/css'>";
	$endCurl         = '';
	while($r         = db_fetch_object($t)){
		if($class   != $r->sst_class){
			$class   = $r->sst_class;
			$s       = $s . "$endCurl\n$r->sst_class {";
		}
		$s           = $s . "$r->ssp_property_name:$r->ssp_property_value;";
		$endCurl     = '}';
	}	
	$s               = $s."}\n</style>\n";
	return $s;
}

function getSqlRecordValues($pSQL, $pRecordValues){
	
	$a              = getArrayFromString($pSQL,'#');
	for($i=0;$i<count($a);$i++){
		$pSQL       = str_replace('#' . $a[$i] . '#', $pRecordValues[$a[$i]], $pSQL);
	}
	return $pSQL;
}

function getSqlFormValues($pSQL, $pFormSessionID){
	
	$s              = array();
	$t              = nuRunQuery("SELECT sva_name, sva_value FROM zzsys_variable WHERE sva_id = '$pFormSessionID'");
	while($r=db_fetch_row($t)){
		$s[$r[0]]   = $r[1];
	}
	$a              = getArrayFromString($pSQL,'#');
	for($i=0;$i<count($a);$i++){
		$pSQL       = str_replace('#' . $a[$i] . '#', $s[$a[$i]], $pSQL);
	}
	return $pSQL;
}

//cookie functions
//require $_GET['dir'] to be set in the script including this script (common.php)
//the 'dir' get var is used to make cookies unique for each db

function nuCreateCookie($name, $value, $days = 0){
	if($days > 0){
		$time = time() + $days*24*60*60*1000;
	}
	setcookie(nuCookieName($name), $value, $time, "/");
	return nuCookieName($name);
}

function nuReadCookie($name){
	if(isset($_COOKIE[nuCookieName($name)])){
		return $_COOKIE[nuCookieName($name)];
	}else{
		return null;
	}
}

function nuEraseCookie($name){
	nuCreateCookie($name, "", -1);
}

function safeCustomDirectory(){
	return str_replace('/','',$_GET['dir']);
}

function nuCookieName($name){
	return $name.safeCustomDirectory();
}

function continueSession(){

	//-- 
	if($_SESSION['nu_session'] != $_GET['ses']){
		return false;
	}

	$setup          = $GLOBALS['nuSetup'];
	$allowedTimeGap = $setup->set_time_out_minutes * 60;
	
	if(time() > $_SESSION['nu_last_login'] + $allowedTimeGap){
		return false;
	}
	
	$_SESSION['nu_last_login'] = time();
	return true;

}

function getFormIDifObjectID($formID){
	
// get form_id from object_id (if $formID is not a form_id)
	$T                     = nuRunQuery("SELECT count(*) FROM zzsys_form WHERE zzsys_form_id = '$formID'");
	$R                     = db_fetch_row($T);
	if($R[0] == 1){ // is a form_id
		return $formID;
	}
	$t                     = nuRunQuery("SELECT sob_lookup_zzsysform_id FROM zzsys_object WHERE zzsys_object_id = '$formID'");
	$r                     = db_fetch_object($t);
	return $r->sob_lookup_zzsysform_id;

}


function accessableForm(){

	$a            = $_SESSION['nu_access_level']; 

	$theFormID    = getFormIDifObjectID($_GET['f']);
	if($theFormID == 'index'){
		return true;
	}
	if($a == 'globeadmin'){
		return true;
	}
	if($theFormID == 'run'){

		if($_GET['r'] == ''){ //-- run activity Browse Form
			return true;
		}
		
		$lookfor  = $_GET['r']; //-- its an activity

		//-- add activities that are allowed by this access level or don't need an access level

		$s        = "SELECT sav_zzsys_activity_id as id FROM zzsys_access_level ";
		$s       .= "INNER JOIN zzsys_access_level_activity ON sav_zzsys_access_level_id = zzsys_access_level_id  ";
		$s       .= "INNER JOIN zzsys_activity ON sav_zzsys_activity_id = zzsys_activity_id  ";
		$s       .= "INNER JOIN zzsys_form ON sat_all_zzsys_form_id = zzsys_form_id  ";
		$s       .= "WHERE sal_name = '$a' ";
		$s       .= "OR sfo_access_without_login = '1' ";

	}else{
		$lookfor  = $theFormID; //-- its a form

	//-- get forms that are opened by buttons on the index page, allowed by this access level
/*
		$s        = "SELECT sob_button_zzsys_form_id as id FROM zzsys_access_level ";
		$s       .= "INNER JOIN zzsys_access_level_object ON sao_zzsys_access_level_id = zzsys_access_level_id  ";
		$s       .= "INNER JOIN zzsys_object ON sao_zzsys_object_id = zzsys_object_id  ";
		$s       .= "WHERE sob_all_type = 'button'  ";
		$s       .= "AND sob_zzsys_form_id = 'index'  ";
		$s       .= "AND sal_name = '$a' ";
*/
//-- now must have access to the form in access levels

		$s        = "SELECT saf_zzsys_form_id as id FROM zzsys_access_level_form ";
		$s       .= "INNER JOIN zzsys_access_level ON saf_zzsys_access_level_id = zzsys_access_level_id ";
		$s       .= "AND sal_name = '$a' ";

	}
	//-- add forms that need no access level

	$s .= "UNION  ";
	$s .= "SELECT zzsys_form_id as id FROM zzsys_form  ";
	$s .= "WHERE sfo_access_without_login = '1' ";

	$t  = nuRunQuery($s);
	while($r = db_fetch_row($t)){
		if($r[0] == $lookfor){
			return true;
		}
	}

	return false;

}


function hideFormButton($pButton){

	$a  = $_SESSION['nu_access_level']; 
    $f  = $_GET['f'];
	
	if($a == 'globeadmin'){
		return false;
	}
	
	$s  = "SELECT saf_" . $pButton . "_button as button FROM zzsys_access_level_form ";
	$s .= "INNER JOIN zzsys_access_level ON saf_zzsys_access_level_id = zzsys_access_level_id ";
	$s .= "AND sal_name = '$a' ";
	$s .= "AND saf_zzsys_form_id = '$f' ";

	$t  = nuRunQuery($s);
	$r  = db_fetch_object($t);

	return $r->button == '1';

}



function getSelectionFormVariables($pID){
	
//	$size                               = 15;
	$v                                  = array();
	$t                                  = nuRunQuery("SELECT count(*) AS thecount, sva_name, sva_value, sva_table FROM zzsys_variable WHERE sva_id = '$pID' GROUP BY sva_name, sva_table");
	while($r                            = db_fetch_object($t)){
		if($r->sva_table                != '1'){
			$v[$r->sva_name]            = $r->sva_value;
		}else{ //--a listbox
	    	$T                          = nuRunQuery("SELECT * FROM zzsys_variable WHERE sva_id = '$pID' AND sva_name = '$r->sva_name' ORDER BY zzsys_variable_id");
			$tableName                  = TT();
			$v[$r->sva_name]            = $tableName;
			$GLOBALS['table'][]         = $tableName;
//			nuRunQuery("CREATE TABLE $tableName (id VARCHAR(15) NOT NULL, $tableName VARCHAR(15) NULL ,PRIMARY KEY (id), INDEX ($tableName))");
			nuRunQuery("CREATE TABLE $tableName (id VARCHAR(15) NOT NULL, $tableName VARCHAR(150) NULL ,PRIMARY KEY (id), INDEX ($tableName))");
	    	while($R                    = db_fetch_object($T)){
	    		$id                     = uniqid('1');
/* don't need this
				if($size                < strlen($R->sva_value)){ //--allow for longer data
					$size               = strlen($R->sva_value);
					nuRunQuery("ALTER TABLE `$tableName` CHANGE `$tableName` `$tableName` VARCHAR( $size )");
				}
*/				
	    		nuRunQuery("INSERT INTO $tableName (id, $tableName) VALUES ('$id', '$R->sva_value')");
	    	}
		}
	}
	return $v;
}
    

function getSelectionFormTempTableNames($pID, $vArray){
	
	$v                                  = array();
	$t                                  = nuRunQuery("SELECT sva_name FROM zzsys_variable WHERE sva_id = '$pID' AND sva_table = '1' GROUP BY sva_name");
	while($r                            = db_fetch_object($t)){
		$v[]                            = $vArray[$r->sva_name];
	}
	return $v;
}
    

function formatTextValue($pValue, $pFormatNumber){

	if($pFormatNumber == ''){return $pValue;}
	$format=textFormatsArray();
	if($format[$pFormatNumber]->type=='number'){

		if($pValue==''){
			return '';
		}else{
			return number_format($pValue, $format[$pFormatNumber]->format, $format[$pFormatNumber]->decimal, $format[$pFormatNumber]->separator);
		}
	}

	if($format[$pFormatNumber]->type=='date'){
		if($pValue=='' or $pValue=='0000-00-00'){
			return '';
		}else{
			return nuDateFormat($pValue,$format[$pFormatNumber]->phpdate);
		}
	}
	return $pValue;

}

/* removed 17-01-2012 by SC

function top_left(){
	$s = $s . "<div style='top:0;left:0;width:8;height:8;position:absolute;font-size:100px;font-family:arial;overflow:hidden;'>\n";
	$s = $s . "<div class='unselected' style='top:-30;left:-4;position:absolute;font-size:70px;font-family:arial;overflow:hidden;'>&bull;</div>\n";
	$s = $s . "</div>\n";
	return $s;
}

function bottom_left(){
	$s = $s . "<div style='bottom:0;left:0;width:8;height:8;position:absolute;font-size:100px;font-family:arial;overflow:hidden;'>\n";
	$s = $s . "<div class='unselected' style='top:-39;left:-4;position:absolute;font-size:70px;font-family:arial;overflow:hidden;'>&bull;</div>\n";
	$s = $s . "</div>\n";
	return $s;
}

function top_right(){
	$s = $s . "<div style='top:0;right:0;width:8;height:8;position:absolute;font-size:100px;font-family:arial;overflow:hidden;'>\n";
	$s = $s . "<div class='unselected' style='top:-30;left:-13;position:absolute;font-size:70px;font-family:arial;overflow:hidden;'>&bull;</div>\n";
	$s = $s . "</div>\n";
	return $s;
}

function bottom_right(){
	$s = $s . "<div style='bottom:0;right:0;width:8;height:8;position:absolute;font-size:100px;font-family:arial;overflow:hidden;'>\n";
	$s = $s . "<div class='unselected' style='top:-39;left:-13;position:absolute;font-size:70px;font-family:arial;overflow:hidden;'>&bull;</div>\n";
	$s = $s . "</div>\n";
	return $s;
}

*/

class sqlString{

    public  $from         = '';
    public  $where        = '';
    public  $groupBy      = '';
    public  $having       = '';
    public  $orderBy      = '';
    public  $fields       = array();
    public  $Dselect      = '';
    public  $Dfrom        = '';
    public  $Dwhere       = '';
    public  $DgroupBy     = '';
    public  $Dhaving      = '';
    public  $DorderBy     = '';
    public  $Dfields      = array();
    public  $SQL          = '';

    function __construct($sql){

        $sql              = str_replace(chr(13), ' ', $sql);//----remove carrige returns
        $sql              = str_replace(chr(10), ' ', $sql);//----remove line feeds

        $select_string    = $sql;
        $from_string      = stristr($sql, ' from ');
        $where_string     = stristr($sql, ' where ');
        $groupBy_string   = stristr($sql, ' group by ');
        $having_string    = stristr($sql, ' having ');
        $orderBy_string   = stristr($sql, ' order by ');
        
        $from             = str_replace($where_string,   '', $from_string);
        $from             = str_replace($groupBy_string, '', $from);
        $from             = str_replace($having_string,  '', $from);
        $from             = str_replace($orderBy_string, '', $from);
        
        $where            = str_replace($groupBy_string, '', $where_string);
        $where            = str_replace($having_string,  '', $where);
        $where            = str_replace($orderBy_string, '', $where);
        
        $groupBy          = str_replace($having_string,  '', $groupBy_string);
        $groupBy          = str_replace($orderBy_string, '', $groupBy);
        
        $having           = str_replace($orderBy_string, '', $having_string);
        
        $orderBy          = $orderBy_string;
        $this->from       = $from;
        $this->where      = $where;
        $this->groupBy    = $groupBy;
        $this->having     = $having;
        $this->orderBy    = $orderBy;

        $this->Dfrom      = $this->from;
        $this->Dwhere     = $this->where;
        $this->DgroupBy   = $this->groupBy;
        $this->Dhaving    = $this->having;
        $this->DorderBy   = $this->orderBy;

    	$this->buildSQL();
      }

    public function restoreDefault($pString){

    	if($pString == 'f'){$this->from      = $this->Dfrom;}
    	if($pString == 'w'){$this->where     = $this->Dwhere;}
    	if($pString == 'g'){$this->groupBy   = $this->DgroupBy;}
    	if($pString == 'h'){$this->having    = $this->Dhaving;}
    	if($pString == 'o'){$this->orderBy   = $this->DorderBy;}
    	$this->buildSQL();

    }

    public function setFrom($pString){

    	$this->from          = $pString; 
    	$this->buildSQL();

    }

    public function setWhere($pString){

    	$this->where         = $pString; 
    	$this->buildSQL();

    }

    public function getWhere(){
    	return $this->where; 
    }

    public function setGroupBy($pString){

    	$this->groupBy       = $pString; 
    	$this->buildSQL();

    }

    public function setHaving($pString){

    	$this->having        = $pString; 
    	$this->buildSQL();

    }

    public function setOrderBy($pString){

    	$this->orderBy       = $pString; 
    	$this->buildSQL();

    }

    public function addField($pString){

    	$this->fields[]      = $pString; 
    	$this->buildSQL();

    }

    public function removeField($pFieldOrderNumber){
    	
    	$newList              = array();
    	for($i = 0 ; $i < count($this->fields) ; $i++){
    		if($i != $pFieldOrderNumber){
    			$newList[]    = $this->fields[$i];
    		}
    	}
    	$this->fields         = $newList;
    }

    public function removeAllFields(){
    	
		while (count($this->fields)> 0){
			$this->removeField(0);
		}
    }

    private function buildSQL(){

    	$this->SQL           = 'SELECT '; 
    	for($i = 0 ; $i < count($this->fields) ; $i++){
    		if($i == 0){
	    		$this->SQL   = $this->SQL . ' ' . $this->fields[$i];
    		}else{
	    		$this->SQL   = $this->SQL . ', ' . $this->fields[$i];
    		}
    	}
    	$this->SQL           = $this->SQL . ' ' . $this->from;
    	$this->SQL           = $this->SQL . ' ' . $this->where;
    	$this->SQL           = $this->SQL . ' ' . $this->groupBy;
    	$this->SQL           = $this->SQL . ' ' . $this->having;
    	$this->SQL           = $this->SQL . ' ' . $this->orderBy;
    }

}

function displayCondition($pHashArray, $pSQLString){

	$string = replaceHashVariablesWithValues($pHashArray, $pSQLString);
	if($string==''){return true;}
	$t      = nuRunQuery($string);
	$r      = db_fetch_row($t);
	return $r[0] == '1';

}


function getFormValue($pFormID, $pFieldName){
	$answer = array();
	$t = nuRunQuery("SELECT sva_value FROM zzsys_variable WHERE sva_id = '$pFormID' AND sva_name = '$pFieldName'");
	while($r = db_fetch_row($t)){
		$answer[] = $r[0];
	}
	return $answer;
}

function replaceVariablesInString($pTT, $pString, $pID){

	$_GET['c'] = (isset($_GET['c']) ? $_GET['c'] : '');
    $pString = str_replace("#ses#"                   ,$_SESSION['nu_session']      ,$pString);
    $pString = str_replace("#session_id#"            ,$_SESSION['nu_session']      ,$pString);
	$pString = str_replace("#access_level#"          ,$_SESSION['nu_access_level'] ,$pString);
	$pString = str_replace("#zzsys_user_id#"         ,$_SESSION['nu_user_id']      ,$pString);
	$pString = str_replace("#zzsys_user_group_name#" ,$_SESSION['nu_user_group']   ,$pString);
	$pString = str_replace("#clone#"                 ,$_GET['c']                   ,$pString);
    $pString = str_replace("#dir#"                   ,$_GET['dir']                 ,$pString);
    $pString = str_replace("#formID#"                ,$_GET['f']                   ,$pString);
    $pString = str_replace("#browse_filter#"         ,$_GET['BF']                  ,$pString); //--Default Filter for Browse Screen (subform property)
	$pString = str_replace("#id#"                    ,$pID                         ,$pString);
	$pString = str_replace("#recordID#"              ,$pID                         ,$pString);
	$pString = str_replace("#TT#"                    ,$pTT                         ,$pString);
	$pString = str_replace("#browseTable#"           ,$pTT                         ,$pString);
	return $pString;

}



function recordToHashArray($pTable, $pPrimaryKey, $pID){ //-- put session values from zzsys_session into an array

	$t = nuRunQuery("SELECT * FROM `$pTable` WHERE `$pPrimaryKey` = '$pID'");
	$r = db_fetch_array($t);
	$a = array();
	$f = false;
	$r['#NOTHING#'] = ''; //-- add just in case there was nothing added
	while(list($key, $value) = each($r)){
		if($f){ //-- jump every second one
//			$a['#'.str_replace($replace   ,'', $key).'#'] = $value;  //-- replaced with below by sc 2012-10-18
			$a['#'.$key.'#'] = $value;
		}
		$f = !$f;
	}
//-- add session variables
	$a['#zzsys_session_id#']      = (isset($_SESSION['nu_session']) ? $_SESSION['nu_session'] : '');
	$a['#access_level#']          = (isset($_SESSION['nu_access_level']) ? $_SESSION['nu_access_level'] : '');
	$a['#zzsys_user_id#']         = (isset($_SESSION['nu_user_id']) ? $_SESSION['nu_user_id'] : '');
	$a['#zzsys_user_group_name#'] = (isset($_SESSION['nu_user_group']) ? $_SESSION['nu_user_group'] : '');
	$a['#session_id#']            = (isset($_SESSION['#nu_session#']) ? $_SESSION['#nu_session#'] : '');
	$a['#ses#']                   = (isset($_SESSION['#nu_session#']) ? $_SESSION['#nu_session#'] : '');
	$a['#session_parameter#']     = (isset($a['#parameter#']) ? $a['#parameter#'] : '');

	return $a;
}


function replaceHashVariablesWithValues($pArray, $pString){ //-- replace hash variables in "hashString" with values in array

	$pArray['#NOTHING#'] = ''; //-- add just in case there was nothing added
	while(list($key, $value) = each($pArray)){
		$pString = str_replace($key, $value, $pString);
	}
	return $pString;
}


function arrayToHashArray($pArray){ //-- put current $_POST variables into a hashArray

	$a = array();
	while(list($key, $value) = each($pArray)){
		$a['#'.$key.'#'] = $value;
	}
	$a['#NOTHING#'] = ''; //-- add just in case there was nothing added
	return $a;
}


function postVariablesToHashArray(){ //-- put current $_POST variables into a hashArray

	$a = array();
	while(list($key, $value) = each($_POST)){
		$a['#'.$key.'#'] = $value;
	}
	$a['#NOTHING#'] = ''; //-- add just in case there was nothing added
	return $a;
}


function sysVariablesToHashArray($pFormID){ //-- put current records in zzsys_variables into a hashArray

	$a = array();
	$t = nuRunQuery("SELECT * FROM zzsys_variable WHERE sva_id = '$pFormID'");
	while($r = db_fetch_object($t)){
		$a['#'.$r->sva_name.'#'] = $r->sva_value;
	}
	$a['#NOTHING#'] = ''; //-- add just in case there was nothing added
	return $a;
}

function fixDQ($pString) {
	return str_replace('"','&quot;',$pString);
}

function joinHashArrays($pArray, $pArrayToAdd){ //--join one hash array to another

	reset ($pArray);
	while(list($key, $value) = each($pArrayToAdd)){
		$pArray[$key] = $value;
	}
	return $pArray;

}

function getArrayFromString($string,$delimiter){

	$array = array();
   	for($i=0;$i<strlen($string);$i++){
		$startOfArray=strpos($string,$delimiter,$i); //---find first instance of $delimiter
		if($startOfArray===false){
          break;
        }
		$i=$startOfArray;
		$endOfArray=strpos($string,$delimiter,$i+1); //---find second instance of $delimiter
		if($endOfArray===false){
          break;
        }
		$array[]=substr($string, $startOfArray+1, $endOfArray-$startOfArray-1);
		$i=$endOfArray;
	}
	return $array;

}

function formFields($formID){
//---returns row of zzsysform as an object (eg. $r->sfo_title)
	$t = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$formID'");
	return db_fetch_object($t);
}

function objectFields($objectID){
//---returns row of zzsysobject as an object (eg. $r->sob_all_title)
	$t = nuRunQuery("SELECT * FROM zzsys_object WHERE zzsys_object_id = '$objectID'");
	return db_fetch_object($t);
}

function listtest($s){
	$t = nuRunQuery($s);
	$r = db_fetch_row($t);
}

function addEscapes($pValue){

	$bs     = '\\';
	$pValue = str_replace($bs,$bs.$bs,$pValue);
	$pValue = str_replace("'","\'",$pValue);
	return $pValue;
	
}

function setnuList($pID, $pExpire, $pName, $pValues){

	$now   = date('Y-m-d H:i:s');
	$dq    = '"';
	$ses    = $_GET['ses'];

	$s     = "DELETE FROM zzsys_variable WHERE sva_id = '$pID' AND sva_name = '$pName'";
	nuRunQuery($s);

	for($i=0; $i< count($pValues);$i++){
		$id    = uniqid('1');
		$fixed = addEscapes($pValues[$i]);
		$s     = "INSERT INTO zzsys_variable (zzsys_variable_id, sva_id, sva_session_id, sva_expiry_date, sva_name, sva_value, sva_table) ";
		$s     = $s . "VALUES ('$id', '$pID', '$ses', '$pExpire', '$pName', '$fixed', '1')";
		nuRunQuery($s);
	}

}

function setnuVariable($pID, $pExpire, $pName, $pValue){

	$id     = uniqid('1');
	$now    = date('Y-m-d H:i:s');
	$dq     = '"';
	$pValue = addEscapes($pValue);
	$ses    = $_GET['ses'];
	if($ses == ''){
		$ses    = $_POST['ses'];  //--done with ajax
	}
	
	$s      = "DELETE FROM zzsys_variable WHERE sva_id = '$pID' AND sva_name = '$pName'";
	nuRunQuery($s);

	$s      = "INSERT INTO zzsys_variable (zzsys_variable_id, sva_id, sva_session_id, sva_expiry_date, sva_name, sva_value, sys_added)  ";
	$s      = $s . "VALUES ('$id', '$pID', '$ses', '$pExpire', '$pName', '$pValue', '$now')";
	nuRunQuery($s);
}

function getnuVariable($pName, $pID){

	$s   = "SELECT sva_value FROM zzsys_variable WHERE sva_name = '$pName' AND sva_id = '$pID'";
	$t   = nuRunQuery($s);
	$r   = db_fetch_object($t);

	if(count($r) > 1){
		return $r;                  //---return array
	}else{
		return $r->sva_value;       //---return 1 value
	}

}

function nuObjects(){

	$nuObject                = array();
	$nuObject[0]             = 'button';
	$nuObject[1]             = 'display';
	$nuObject[2]             = 'dropdown';
	$nuObject[3]             = 'graph';
	$nuObject[4]             = 'image';
	$nuObject[5]             = 'inarray';
	$nuObject[6]             = 'listbox';
	$nuObject[7]             = 'lookup';
	$nuObject[8]             = 'password';
	$nuObject[9]             = 'subform';
	$nuObject[10]            = 'text';
	$nuObject[11]            = 'textarea';
	$nuObject[12]            = 'words';

	return $nuObject;
}

function addCentury($pValue){

	if($pValue > 70){
		return '19'.$pValue;
	}
	return '20'.$pValue;

}

function setFormatArray(){

	$textFormat=textFormatsArray();

    $s   =      "var aType         = new Array();\n";
    $s   = $s . "var aFormat       = new Array();\n";
    $s   = $s . "var aDecimal      = new Array();\n";
    $s   = $s . "var aSeparator    = new Array();\n\n";

    for($i = 0 ; $i < count($textFormat) ; $i++){

		$type       = $textFormat[$i]->type;
		$format     = $textFormat[$i]->format;
		$decimal    = $textFormat[$i]->decimal;
		$separator  = $textFormat[$i]->separator;
		$s          = $s . "    aType[$i]        = ['$type'];\n";
		$s          = $s . "    aFormat[$i]      = ['$format'];\n";
		$s          = $s . "    aDecimal[$i]     = ['$decimal'];\n";
		$s          = $s . "    aSeparator[$i]   = ['$separator'];\n\n";

	}
    return $s;

}

function nuReformatField($pValue, $pFormat,$addSingleQuotes = true){
	return reformatField($pValue, $pFormat,$addSingleQuotes);
}

function reformatField($pValue, $pFormat,$addSingleQuotes = true){
// reformats value ready for insertion into database table
//originally formatted via rules in textFormatsArray()
	$FORMAT = textFormatsArray();
	$sq     = "";
	if($FORMAT[$pFormat]->type == 'date' AND $pValue == ''){return 'NULL';} //--save null to a date field
	if($addSingleQuotes){$sq = "'";}
	if($pFormat == '' OR $pValue == ''){return $sq . $pValue . $sq;} // not a text field or nothing to format
	if($pFormat == '6'){ // dd-mmm-yyyy
		return $sq . substr($pValue,-4)              . '-' . monthNumber(substr($pValue,3,3))  . '-' . substr($pValue,0,2) . $sq;
	}
	if($pFormat == '7'){ // dd-mm-yyyy
		return $sq . substr($pValue,-4)              . '-' . substr($pValue,3,2)               . '-' . substr($pValue,0,2) . $sq;
	}
	if($pFormat == '8'){ // mmm-dd-yyyy
		return $sq . substr($pValue,-4)              . '-' . monthNumber(substr($pValue,0,3))  . '-' . substr($pValue,4,2) . $sq;
	}
	if($pFormat == '9'){ // mm-dd-yyyy
		return $sq . substr($pValue,-4)              . '-' . substr($pValue,0,2)               . '-' . substr($pValue,3,2) . $sq;
	}
	if($pFormat == '10'){ // dd-mmm-yy
		return $sq . addCentury(substr($pValue,-2))  . '-' . monthNumber(substr($pValue,3,3))  . '-' . substr($pValue,0,2) . $sq;
	}
	if($pFormat == '11'){ // dd-mm-yy
		return $sq . addCentury(substr($pValue,-2))  . '-' . substr($pValue,3,2)               . '-' . substr($pValue,0,2) . $sq;
	}
	if($pFormat == '12'){ // mmm-dd-yy
		return $sq . addCentury(substr($pValue,-2))  . '-' . monthNumber(substr($pValue,0,3))  . '-' . substr($pValue,4,2) . $sq;
	}
	if($pFormat == '13'){ // mm-dd-yy
		return $sq . addCentury(substr($pValue,-2))  . '-' . substr($pValue,0,2)               . '-' . substr($pValue,3,2) . $sq;
	}

    if (in_array($pFormat, array('14','15','16','17','18','19'))){ //---number with commas
		return $sq . str_replace(',', '', $pValue) . $sq;
    }
    if (in_array($pFormat, array('20','21','22','23','24','25','26','27','28','29','30','31'))){ //---euro numbers with decimal as commas
		$euro = str_replace('.', '', $pValue);
		nuDebug($sq . str_replace(',', '.', $euro) . $sq);
		return $sq . str_replace(',', '.', $euro) . $sq;
    }
	return  $sq . $pValue . $sq;

}


function textFormatsArray(){

//-----number formats
	$format = array();
	$format[0]->type         = 'number';
	$format[0]->format       = '0';
	$format[0]->decimal      = '.';
	$format[0]->separator    = '';
	$format[0]->sample       = '10000';
	$format[0]->phpdate      = '';
	$format[0]->sql          = 'REPLACE(FORMAT(??,0), ",", "")';

	$format[1]->type         = 'number';
	$format[1]->format       = '1';
	$format[1]->decimal      = '.';
	$format[1]->separator    = '';
	$format[1]->sample       = '10000.0';
	$format[1]->phpdate      = '';
	$format[1]->sql          = 'REPLACE(FORMAT(??,1), ",", "")';

	$format[2]->type         = 'number';
	$format[2]->format       = '2';
	$format[2]->decimal      = '.';
	$format[2]->separator    = '';
	$format[2]->sample       = '10000.00';
	$format[2]->phpdate      = '';
	$format[2]->sql          = 'REPLACE(FORMAT(??,2), ",", "")';

	$format[3]->type         = 'number';
	$format[3]->format       = '3';
	$format[3]->decimal      = '.';
	$format[3]->separator    = '';
	$format[3]->sample       = '10000.000';
	$format[3]->phpdate      = '';
	$format[3]->sql          = 'REPLACE(FORMAT(??,3), ",", "")';

	$format[4]->type         = 'number';
	$format[4]->format       = '4';
	$format[4]->decimal      = '.';
	$format[4]->separator    = '';
	$format[4]->sample       = '10000.0000';
	$format[4]->phpdate      = '';
	$format[4]->sql          = 'REPLACE(FORMAT(??,4), ",", "")';

	$format[5]->type         = 'number';
	$format[5]->format       = '5';
	$format[5]->decimal      = '.';
	$format[5]->separator    = '';
	$format[5]->sample       = '10000.00000';
	$format[5]->phpdate      = '';
	$format[5]->sql          = 'REPLACE(FORMAT(??,5), ",", "")';

//-----date formats

	$format[6]->type         = 'date';
	$format[6]->format       = 'dd-mmm-yyyy';
	$format[6]->decimal      = '.';
	$format[6]->separator    = '';
	$format[6]->sample       = '13-Jan-2007';
	$format[6]->phpdate      = 'd-M-Y';
	$format[6]->sql          = 'DATE_FORMAT(??,"%d-%b-%Y")';

	$format[7]->type         = 'date';
	$format[7]->format       = 'dd-mm-yyyy';
	$format[7]->decimal      = '.';
	$format[7]->separator    = '';
	$format[7]->sample       = '13-01-2007';
	$format[7]->phpdate      = 'd-m-Y';
	$format[7]->sql          = 'DATE_FORMAT(??,"%d-%m-%Y")';

	$format[8]->type         = 'date';
	$format[8]->format       = 'mmm-dd-yyyy';
	$format[8]->decimal      = '.';
	$format[8]->separator    = '';
	$format[8]->sample       = 'Jan-13-2007';
	$format[8]->phpdate      = 'M-d-Y';
	$format[8]->sql          = 'DATE_FORMAT(??,"%b-%d-%Y")';

	$format[9]->type         = 'date';
	$format[9]->format       = 'mm-dd-yyyy';
	$format[9]->decimal      = '.';
	$format[9]->separator    = '';
	$format[9]->sample       = '01-13-2007';
	$format[9]->phpdate      = 'm-d-Y';
	$format[9]->sql          = 'DATE_FORMAT(??,"%m-%d-%Y")';

	$format[10]->type        = 'date';
	$format[10]->format      = 'dd-mmm-yy';
	$format[10]->decimal     = '.';
	$format[10]->separator   = '';
	$format[10]->sample      = '13-Jan-07';
	$format[10]->phpdate     = 'd-M-y';
	$format[10]->sql         = 'DATE_FORMAT(??,"%d-%b-%y")';

	$format[11]->type        = 'date';
	$format[11]->format      = 'dd-mm-yy';
	$format[11]->decimal     = '.';
	$format[11]->separator   = '';
	$format[11]->sample      = '13-01-07';
	$format[11]->phpdate     = 'd-m-y';
	$format[11]->sql         = 'DATE_FORMAT(??,"%d-%m-%y")';

	$format[12]->type        = 'date';
	$format[12]->format      = 'mmm-dd-yy';
	$format[12]->decimal     = '.';
	$format[12]->separator   = '';
	$format[12]->sample      = 'Jan-13-07';
	$format[12]->phpdate     = 'M-d-y';
	$format[12]->sql         = 'DATE_FORMAT(??,"%b-%d-%y")';

	$format[13]->type        = 'date';
	$format[13]->format      = 'mm-dd-yy';
	$format[13]->decimal     = '.';
	$format[13]->separator   = '';
	$format[13]->sample      = '01-13-07';
	$format[13]->phpdate     = 'm-d-y';
	$format[13]->sql         = 'DATE_FORMAT(??,"%m-%d-%y")';

//-----number formats

	$format[14]->type        = 'number';
	$format[14]->format      = '0';
	$format[14]->decimal     = '.';
	$format[14]->separator   = ',';
	$format[14]->sample      = '10,000';
	$format[14]->phpdate     = '';
	$format[14]->sql         = 'FORMAT(??,0)';

	$format[15]->type        = 'number';
	$format[15]->format      = '1';
	$format[15]->decimal     = '.';
	$format[15]->separator   = ',';
	$format[15]->sample      = '10,000.0';
	$format[15]->phpdate     = '';
	$format[15]->sql         = 'FORMAT(??,1)';

	$format[16]->type        = 'number';
	$format[16]->format      = '2';
	$format[16]->decimal     = '.';
	$format[16]->separator   = ',';
	$format[16]->sample      = '10,000.00';
	$format[16]->phpdate     = '';
	$format[16]->sql         = 'FORMAT(??,2)';

	$format[17]->type        = 'number';
	$format[17]->format      = '3';
	$format[17]->decimal     = '.';
	$format[17]->separator   = ',';
	$format[17]->sample      = '10,000.000';
	$format[17]->phpdate     = '';
	$format[17]->sql         = 'FORMAT(??,3)';

	$format[18]->type        = 'number';
	$format[18]->format      = '4';
	$format[18]->decimal     = '.';
	$format[18]->separator   = ',';
	$format[18]->sample      = '10,000.0000';
	$format[18]->phpdate     = '';
	$format[18]->sql         = 'FORMAT(??,4)';

	$format[19]->type        = 'number';
	$format[19]->format      = '5';
	$format[19]->decimal     = '.';
	$format[19]->separator   = ',';
	$format[19]->sample      = '10,000.00000';
	$format[19]->phpdate     = '';
	$format[19]->sql         = 'FORMAT(??,5)';

//-----euro number formats

	$format[20]->type        = 'number';
	$format[20]->format      = '0';
	$format[20]->decimal     = ',';
	$format[20]->separator   = '';
	$format[20]->sample      = '10000';
	$format[20]->phpdate     = '';
	$format[20]->sql         = 'FORMAT(??,0)';

	$format[21]->type        = 'number';
	$format[21]->format      = '1';
	$format[21]->decimal     = ',';
	$format[21]->separator   = '';
	$format[21]->sample      = '10000,0';
	$format[21]->phpdate     = '';
	$format[21]->sql         = 'FORMAT(??,1)';

	$format[22]->type        = 'number';
	$format[22]->format      = '2';
	$format[22]->decimal     = ',';
	$format[22]->separator   = '';
	$format[22]->sample      = '10000,00';
	$format[22]->phpdate     = '';
	$format[22]->sql         = 'FORMAT(??,2)';

	$format[23]->type        = 'number';
	$format[23]->format      = '3';
	$format[23]->decimal     = ',';
	$format[23]->separator   = '';
	$format[23]->sample      = '10000,000';
	$format[23]->phpdate     = '';
	$format[23]->sql         = 'FORMAT(??,3)';

	$format[24]->type        = 'number';
	$format[24]->format      = '4';
	$format[24]->decimal     = ',';
	$format[24]->separator   = '';
	$format[24]->sample      = '10000,0000';
	$format[24]->phpdate     = '';
	$format[24]->sql         = 'FORMAT(??,4)';

	$format[25]->type        = 'number';
	$format[25]->format      = '5';
	$format[25]->decimal     = ',';
	$format[25]->separator   = '';
	$format[25]->sample      = '10000,00000';
	$format[25]->phpdate     = '';
	$format[25]->sql         = 'FORMAT(??,5)';

	$format[26]->type        = 'number';
	$format[26]->format      = '0';
	$format[26]->decimal     = ',';
	$format[26]->separator   = '.';
	$format[26]->sample      = '10.000';
	$format[26]->phpdate     = '';
	$format[26]->sql         = 'FORMAT(??,0)';

	$format[27]->type        = 'number';
	$format[27]->format      = '1';
	$format[27]->decimal     = ',';
	$format[27]->separator   = '.';
	$format[27]->sample      = '10.000,0';
	$format[27]->phpdate     = '';
	$format[27]->sql         = 'FORMAT(??,1)';

	$format[28]->type        = 'number';
	$format[28]->format      = '2';
	$format[28]->decimal     = ',';
	$format[28]->separator   = '.';
	$format[28]->sample      = '10.000,00';
	$format[28]->phpdate     = '';
	$format[28]->sql         = 'FORMAT(??,2)';

	$format[29]->type        = 'number';
	$format[29]->format      = '3';
	$format[29]->decimal     = ',';
	$format[29]->separator   = '.';
	$format[29]->sample      = '10.000,000';
	$format[29]->phpdate     = '';
	$format[29]->sql         = 'FORMAT(??,3)';

	$format[30]->type        = 'number';
	$format[30]->format      = '4';
	$format[30]->decimal     = ',';
	$format[30]->separator   = '.';
	$format[30]->sample      = '10.000,0000';
	$format[30]->phpdate     = '';
	$format[30]->sql         = 'FORMAT(??,4)';

	$format[31]->type        = 'number';
	$format[31]->format      = '5';
	$format[31]->decimal     = ',';
	$format[31]->separator   = '.';
	$format[31]->sample      = '10.000,00000';
	$format[31]->phpdate     = '';
	$format[31]->sql         = 'FORMAT(??,5)';


	return $format;

}

//--auto colors for bar graphs
function setColourArray() {

    $colourarray = Array();
    $colourarray[0] = "aqua";
    $colourarray[1] = "red";
    $colourarray[2] = "blue";
    $colourarray[3] = "gold";
    $colourarray[4] = "green";
    $colourarray[5] = "orange";
    $colourarray[6] = "purple";
    $colourarray[7] = "pink";
    $colourarray[8] = "brown";
    $colourarray[9] = "goldenrod";
    $colourarray[10] = "khaki";
    $colourarray[11] = "lawngreen";
    $colourarray[12] = "orangered";
    $colourarray[13] = "magenta";
    $colourarray[14] = "lightblue";
    $colourarray[15] = "silver";
    $colourarray[16] = "tan";
    $colourarray[17] = "deeppink";
    $colourarray[18] = "eggplant";
    $colourarray[19] = "lime";
    $colourarray[20] = "peru";
    $colourarray[21] = "lightred";
    $colourarray[22] = "lightblue";
    return $colourarray;

}


//---convert dd-mm-yyyy format to d-m-Y
function convertToPhpDateFormat($format){

	$newFormat = str_replace('dd', 'd', $format);
	$newFormat = str_replace('mmm', 'M', $newFormat);
	$newFormat = str_replace('mm', 'm', $newFormat);
	$newFormat = str_replace('yyyy', 'Y', $newFormat);
	$newFormat = str_replace('yy', 'y', $newFormat);
    return $newFormat;

}



//---add days (or subtract) to a date. (returns format '2006-11-20')
function nuDateAddDays($Date,$Days){

    $d=substr($Date,-2);
    $m=substr($Date,5,2);
    $y=substr($Date,0,4);
    return date ("Y-m-d", mktime (0,0,0,$m,$d+$Days,$y));

}

//---formats a date with php date() format strings
function nuDateFormat($Date,$Format){
	$tokens = explode(' ', $Date);
	$Date = $tokens[0];
	if($Date=='NULL'){return '';}
    $d=substr($Date,-2);
    $m=substr($Date,5,2);
    $y=substr($Date,0,4);
    return date ($Format, mktime (0,0,0,$m,$d,$y));

}

function Today(){
    return date("Y-m-d");
}




/* ========================== REDUNDANT CODE ==========================
// DO NOT USE - use nuSendEmail in emaillib.php instead which uses the phpmailer library
	
//---send an email
function nu_mail($pTo, $pFrom, $pSubject, $pMessage){

//--using http://www.pear.php.net
    require_once('Mail.php');
    $headers['To'] = $pTo;
    if($pFrom == ''){
    	$headers['From'] = 'admin@pcp2000.com.au';    	
    }else{
    	$headers['From'] = $pFrom;    	
    }
    $headers['Subject'] = $pSubject;
    $headers['MIME-Version'] = '1.0';
    $headers['Content-type'] = 'text/html; charset=iso-8859-1';

    $params['host'] = '127.0.0.1';
    $mail_object = & Mail::factory('smtp', $params);
    return $mail_object->send($pTo, $headers, $pMessage);

}
 ========================== REDUNDANT CODE ========================== */
 
//----takes value returned by RunQuery as a parameter and returns all its fieldnames in an array
function TableFieldNames($t){
    $to=db_num_fields($t);

    for($i=0;$i<$to;$i++){
        $fn=db_field_name($t,$i);
        $FieldName[]=$fn;
    }
    return $FieldName;
}

//----spits a paragraph into lines of text and puts them in an array starting from 1 (not 0)
//---- 1st parameter is the paragraph, 2nd parameter is the maximum length of each string of text
function splitText($s,$l){

    $line[]='';
    while(strlen($s)<>0){
        $chop=ChopPosition($s,$l);
        $line[]=substr($s,0,$chop);
        $s=substr($s,$chop);
    }
    return $line;

}

//-------- finds position to chop off text (ready for the next line)
function ChopPosition($string,$maxLength){

    $S=substr($string,0,$maxLength+1);
//--find next carriage return
    if(strpos($S, "\n")===false){
    }else{
        return strpos($S, "\n")+1;
    }
//--find last space within maximum length
    if(strrpos($S, " ")===false OR strlen($S)<=$maxLength){
      return $maxLength;
    }else{
      return strrpos($S, " ")+1;
    }

}

function TT(){
//--create a unique name for a Temp Table
	return '___nu'.uniqid('1').'___';
}


function StringIsTrue($pstring){
	$t=nuRunQuery("SELECT $pstring",0);
	$r=db_fetch_row($t);
	return $r[0];
}

function GetHashVariables($SQL){
	$var[]='';
	for($i=0;strlen($SQL);$i++){
		$firsthash=strpos($SQL,'#',$i);
		if($firsthash===false){return $var;}
		$i=$firsthash;
		$secondhash=strpos($SQL,'#',$i+1);
		if($secondhash===false){return $var;}
		$var[]=substr($SQL, $firsthash, $secondhash-$firsthash+1);
		$i=$secondhash;
	}



}

function FromToday($years,$months,$days){;
	$d=date('d');
	$m=date('m');
	$y=date('y');
	$new= date("Y-m-d", mktime (0,0,0,$m+$months,$d+$days,$y+$years));
	return $new;
}

function Dlookup($f,$t,$c){
	$t=nuRunQuery("SELECT $f FROM $t WHERE $c");
	$r = db_fetch_row($t);
	return $r[0];
}


function onerecord($table,$id){
//---returns row as an object (eg. $r->Name)
	$t = nuRunQuery("Select * FROM $table WHERE $table"."ID = '$id'");
	$r = db_fetch_object($t);
	return $r;
}

function align($pAlign){
	if($pAlign == 'l'){return 'left';}
	if($pAlign == 'r'){return 'right';}
	if($pAlign == 'c'){return 'center';}
	return 'left';
}

function nuSetup(){
    
	static $setup;
	
	//check if setup has already be called
    if (empty($setup)) {
		//get setup info from db
		$rs 	= nuRunQuery("Select * From zzsys_setup");
		$setup 	= db_fetch_object($rs);
	}
	
	//setup garbage collect timeouts
	$gcLifetime  = 60 * $setup->set_time_out_minutes;
	ini_set("session.gc_maxlifetime", $gcLifetime);
		
	//return result		
    return $setup;
}



function tofile($text){
//----writes to a systrap for debuging
	$text=db_real_escape_string($text);
	nuRunQuery("Insert INTO zzsys_trap (tra_message, sys_added) VALUES ('$text',".date('"Y-m-d h:i:s"').")");
	return $text;
}

function nuDebug($text){
//----writes to a systrap for debuging
	return tofile($GLOBALS['nuEvent'].$text);
}

function jsdate($pdate){
//---------creates a javascript date string from a mysql date
	$newmth='01';
	$mth = $pdate[5].$pdate[6];
	if($mth=='01'){$newmth='Jan';}
	if($mth=='02'){$newmth='Feb';}
	if($mth=='03'){$newmth='Mar';}
	if($mth=='04'){$newmth='Apr';}
	if($mth=='05'){$newmth='May';}
	if($mth=='06'){$newmth='Jun';}
	if($mth=='07'){$newmth='Jul';}
	if($mth=='08'){$newmth='Aug';}
	if($mth=='09'){$newmth='Sep';}
	if($mth=='10'){$newmth='Oct';}
	if($mth=='11'){$newmth='Nov';}
	if($mth=='12'){$newmth='Dec';}
	return $pdate[8].$pdate[9]."-".$newmth."-".$pdate[0].$pdate[1].$pdate[2].$pdate[3];

}

function left($s,$places){
	return substr($s, 0, $places);
}

function right($s,$places){
	return substr($s, $places*-1);
}
function iif($condition,$true,$false){
//---------immediate if function
	if($condition){
		return $true;
	}else{
		return $false;
	}
}

function msql_date($pdate){
//---------creates a date string eg. '2004-10-25' from a formatted javascript date eg '25-Oct-2004', with single quotes that can go into mysql
	$pdate=iif(strlen($pdate)==10,'0'.$pdate,$pdate);
	$newmth='01';
	$mth = $pdate[3].$pdate[4].$pdate[5];
	if($mth=='Jan'){$newmth='01';}
	if($mth=='Feb'){$newmth='02';}
	if($mth=='Mar'){$newmth='03';}
	if($mth=='Apr'){$newmth='04';}
	if($mth=='May'){$newmth='05';}
	if($mth=='Jun'){$newmth='06';}
	if($mth=='Jul'){$newmth='07';}
	if($mth=='Aug'){$newmth='08';}
	if($mth=='Sep'){$newmth='09';}
	if($mth=='Oct'){$newmth='10';}
	if($mth=='Nov'){$newmth='11';}
	if($mth=='Dec'){$newmth='12';}
	return "'".$pdate[7].$pdate[8].$pdate[9].$pdate[10].$pdate[6].monthNumber($mth).$pdate[6].$pdate[0].$pdate[1]."'";
}


function mysql_date($pdate){
//---------creates a date string eg. '2004-10-25' from a formatted javascript date eg '25-Oct-2004', with single quotes that can go into mysql
	$pdate=iif(strlen($pdate)==10,'0'.$pdate,$pdate);
	$newmth='01';
	$mth = $pdate[3].$pdate[4].$pdate[5];
	if($mth=='Jan'){$newmth='01';}
	if($mth=='Feb'){$newmth='02';}
	if($mth=='Mar'){$newmth='03';}
	if($mth=='Apr'){$newmth='04';}
	if($mth=='May'){$newmth='05';}
	if($mth=='Jun'){$newmth='06';}
	if($mth=='Jul'){$newmth='07';}
	if($mth=='Aug'){$newmth='08';}
	if($mth=='Sep'){$newmth='09';}
	if($mth=='Oct'){$newmth='10';}
	if($mth=='Nov'){$newmth='11';}
	if($mth=='Dec'){$newmth='12';}
	return "'".$pdate[7].$pdate[8].$pdate[9].$pdate[10].$pdate[6].monthNumber($mth).$pdate[6].$pdate[0].$pdate[1]."'";
}

function monthNumber($pMonth){

	if($pMonth=='Jan'){return '01';}
	if($pMonth=='Feb'){return '02';}
	if($pMonth=='Mar'){return '03';}
	if($pMonth=='Apr'){return '04';}
	if($pMonth=='May'){return '05';}
	if($pMonth=='Jun'){return '06';}
	if($pMonth=='Jul'){return '07';}
	if($pMonth=='Aug'){return '08';}
	if($pMonth=='Sep'){return '09';}
	if($pMonth=='Oct'){return '10';}
	if($pMonth=='Nov'){return '11';}
	if($pMonth=='Dec'){return '12';}
	return '';
}

function msql_date_nq($pdate){
//---------creates a date string eg. 2004-10-25 from a formatted javascript date eg '25-Oct-2004', with no quotes
	$pdate=iif(strlen($pdate)==10,'0'.$pdate,$pdate);
	$newmth='01';
	$mth = $pdate[3].$pdate[4].$pdate[5];
	if($mth=='Jan'){$newmth='01';}
	if($mth=='Feb'){$newmth='02';}
	if($mth=='Mar'){$newmth='03';}
	if($mth=='Apr'){$newmth='04';}
	if($mth=='May'){$newmth='05';}
	if($mth=='Jun'){$newmth='06';}
	if($mth=='Jul'){$newmth='07';}
	if($mth=='Aug'){$newmth='08';}
	if($mth=='Sep'){$newmth='09';}
	if($mth=='Oct'){$newmth='10';}
	if($mth=='Nov'){$newmth='11';}
	if($mth=='Dec'){$newmth='12';}
	return $pdate[7].$pdate[8].$pdate[9].$pdate[10].$pdate[6].$newmth.$pdate[6].$pdate[0].$pdate[1];
}

function mysql_date_nq($pdate){
//---------creates a date string eg. 2004-10-25 from a formatted javascript date eg '25-Oct-2004', with no quotes
	$pdate=iif(strlen($pdate)==10,'0'.$pdate,$pdate);
	$newmth='01';
	$mth = $pdate[3].$pdate[4].$pdate[5];
	if($mth=='Jan'){$newmth='01';}
	if($mth=='Feb'){$newmth='02';}
	if($mth=='Mar'){$newmth='03';}
	if($mth=='Apr'){$newmth='04';}
	if($mth=='May'){$newmth='05';}
	if($mth=='Jun'){$newmth='06';}
	if($mth=='Jul'){$newmth='07';}
	if($mth=='Aug'){$newmth='08';}
	if($mth=='Sep'){$newmth='09';}
	if($mth=='Oct'){$newmth='10';}
	if($mth=='Nov'){$newmth='11';}
	if($mth=='Dec'){$newmth='12';}
	return $pdate[7].$pdate[8].$pdate[9].$pdate[10].$pdate[6].$newmth.$pdate[6].$pdate[0].$pdate[1];
}

function nz($pValue,$pIfNull){

	if($pValue == ""){$pValue = $pIfNull;}
	Return $pValue;
}

function getLib() {

        $result = "";
        $sql = "SELECT slb_code FROM zzsys_library";
        $rs  = nuRunQuery($sql);
        while ($obj = db_fetch_object($rs)) {
		$result .= "\n".$obj->slb_code;
        }
        return $result;
}

//added by Nick 21/07/09
//takes a string, returns the first integer found within the string, as an integer/number
//if the string has no numbers, return 0
function parseInt($string) {
	if(preg_match('/(\d+)/', $string, $array)) {
		return $array[1];
	} else {
		return 0;
	}
}


function ColorToHex($pColor){

    $vColor    = strtoupper($pColor);
   
    if($vColor =='ALICEBLUE'){return 'F0F8FF';}
    if($vColor == 'ANTIQUEWHITE'){return 'FAEBD7';}
    if($vColor == 'AQUA'){return '00FFFF';}
    if($vColor == 'AQUAMARINE'){return '7FFFD4';}
    if($vColor == 'AZURE'){return 'F0FFFF';}
    if($vColor == 'BEIGE'){return 'F5F5DC';}
    if($vColor == 'BISQUE'){return 'FFE4C4';}
    if($vColor == 'BLACK'){return '000000';}
    if($vColor == 'BLANCHEDALMOND'){return 'FFEBCD';}
    if($vColor == 'BLUE'){return '0000FF';}
    if($vColor == 'BLUEVIOLET'){return '8A2BE2';}
    if($vColor == 'BROWN'){return 'A52A2A';}
    if($vColor == 'BURLYWOOD'){return 'DEB887';}
    if($vColor == 'CADETBLUE'){return '5F9EA0';}
    if($vColor == 'CHARTREUSE'){return '7FFF00';}
    if($vColor == 'CHOCOLATE'){return 'D2691E';}
    if($vColor == 'CORAL'){return 'FF7F50';}
    if($vColor == 'CORNFLOWERBLUE'){return '6495ED';}
    if($vColor == 'CORNSILK'){return 'FFF8DC';}
    if($vColor == 'CRIMSON'){return 'DC143C';}
    if($vColor == 'CYAN'){return '00FFFF';}
    if($vColor == 'DARKBLUE'){return '00008B';}
    if($vColor == 'DARKCYAN'){return '008B8B';}
    if($vColor == 'DARKGOLDENROD'){return 'B8860B';}
    if($vColor == 'DARKGRAY'){return 'A9A9A9';}
    if($vColor == 'DARKGREY'){return 'A9A9A9';}
    if($vColor == 'DARKGREEN'){return '006400';}
    if($vColor == 'DARKKHAKI'){return 'BDB76B';}
    if($vColor == 'DARKMAGENTA'){return '8B008B';}
    if($vColor == 'DARKOLIVEGREEN'){return '556B2F';}
    if($vColor == 'DARKORANGE'){return 'FF8C00';}
    if($vColor == 'DARKORCHID'){return '9932CC';}
    if($vColor == 'DARKRED'){return '8B0000';}
    if($vColor == 'DARKSALMON'){return 'E9967A';}
    if($vColor == 'DARKSEAGREEN'){return '8FBC8F';}
    if($vColor == 'DARKSLATEBLUE'){return '483D8B';}
    if($vColor == 'DARKSLATEGRAY'){return '2F4F4F';}
    if($vColor == 'DARKSLATEGREY'){return '2F4F4F';}
    if($vColor == 'DARKTURQUOISE'){return '00CED1';}
    if($vColor == 'DARKVIOLET'){return '9400D3';}
    if($vColor == 'DEEPPINK'){return 'FF1493';}
    if($vColor == 'DEEPSKYBLUE'){return '00BFFF';}
    if($vColor == 'DIMGRAY'){return '696969';}
    if($vColor == 'DIMGREY'){return '696969';}
    if($vColor == 'DODGERBLUE'){return '1E90FF';}
    if($vColor == 'FIREBRICK'){return 'B22222';}
    if($vColor == 'FLORALWHITE'){return 'FFFAF0';}
    if($vColor == 'FORESTGREEN'){return '228B22';}
    if($vColor == 'FUCHSIA'){return 'FF00FF';}
    if($vColor == 'GAINSBORO'){return 'DCDCDC';}
    if($vColor == 'GHOSTWHITE'){return 'F8F8FF';}
    if($vColor == 'GOLD'){return 'FFD700';}
    if($vColor == 'GOLDENROD'){return 'DAA520';}
    if($vColor == 'GRAY'){return '808080';}
    if($vColor == 'GREY'){return '808080';}
    if($vColor == 'GREEN'){return '008000';}
    if($vColor == 'GREENYELLOW'){return 'ADFF2F';}
    if($vColor == 'HONEYDEW'){return 'F0FFF0';}
    if($vColor == 'HOTPINK'){return 'FF69B4';}
    if($vColor == 'INDIANRED'){return 'CD5C5C';}
    if($vColor == 'INDIGO'){return '4B0082';}
    if($vColor == 'IVORY'){return 'FFFFF0';}
    if($vColor == 'KHAKI'){return 'F0E68C';}
    if($vColor == 'LAVENDER'){return 'E6E6FA';}
    if($vColor == 'LAVENDERBLUSH'){return 'FFF0F5';}
    if($vColor == 'LAWNGREEN'){return '7CFC00';}
    if($vColor == 'LEMONCHIFFON'){return 'FFFACD';}
    if($vColor == 'LIGHTBLUE'){return 'ADD8E6';}
    if($vColor == 'LIGHTCORAL'){return 'F08080';}
    if($vColor == 'LIGHTCYAN'){return 'E0FFFF';}
    if($vColor == 'LIGHTGOLDENRODYELLOW'){return 'FAFAD2';}
    if($vColor == 'LIGHTGRAY'){return 'D3D3D3';}
    if($vColor == 'LIGHTGREY'){return 'D3D3D3';}
    if($vColor == 'LIGHTGREEN'){return '90EE90';}
    if($vColor == 'LIGHTPINK'){return 'FFB6C1';}
    if($vColor == 'LIGHTSALMON'){return 'FFA07A';}
    if($vColor == 'LIGHTSEAGREEN'){return '20B2AA';}
    if($vColor == 'LIGHTSKYBLUE'){return '87CEFA';}
    if($vColor == 'LIGHTSLATEGRAY'){return '778899';}
    if($vColor == 'LIGHTSLATEGREY'){return '778899';}
    if($vColor == 'LIGHTSTEELBLUE'){return 'B0C4DE';}
    if($vColor == 'LIGHTYELLOW'){return 'FFFFE0';}
    if($vColor == 'LIME'){return '00FF00';}
    if($vColor == 'LIMEGREEN'){return '32CD32';}
    if($vColor == 'LINEN'){return 'FAF0E6';}
    if($vColor == 'MAGENTA'){return 'FF00FF';}
    if($vColor == 'MAROON'){return '800000';}
    if($vColor == 'MEDIUMAQUAMARINE'){return '66CDAA';}
    if($vColor == 'MEDIUMBLUE'){return '0000CD';}
    if($vColor == 'MEDIUMORCHID'){return 'BA55D3';}
    if($vColor == 'MEDIUMPURPLE'){return '9370D8';}
    if($vColor == 'MEDIUMSEAGREEN'){return '3CB371';}
    if($vColor == 'MEDIUMSLATEBLUE'){return '7B68EE';}
    if($vColor == 'MEDIUMSPRINGGREEN'){return '00FA9A';}
    if($vColor == 'MEDIUMTURQUOISE'){return '48D1CC';}
    if($vColor == 'MEDIUMVIOLETRED'){return 'C71585';}
    if($vColor == 'MIDNIGHTBLUE'){return '191970';}
    if($vColor == 'MINTCREAM'){return 'F5FFFA';}
    if($vColor == 'MISTYROSE'){return 'FFE4E1';}
    if($vColor == 'MOCCASIN'){return 'FFE4B5';}
    if($vColor == 'NAVAJOWHITE'){return 'FFDEAD';}
    if($vColor == 'NAVY'){return '000080';}
    if($vColor == 'OLDLACE'){return 'FDF5E6';}
    if($vColor == 'OLIVE'){return '808000';}
    if($vColor == 'OLIVEDRAB'){return '6B8E23';}
    if($vColor == 'ORANGE'){return 'FFA500';}
    if($vColor == 'ORANGERED'){return 'FF4500';}
    if($vColor == 'ORCHID'){return 'DA70D6';}
    if($vColor == 'PALEGOLDENROD'){return 'EEE8AA';}
    if($vColor == 'PALEGREEN'){return '98FB98';}
    if($vColor == 'PALETURQUOISE'){return 'AFEEEE';}
    if($vColor == 'PALEVIOLETRED'){return 'D87093';}
    if($vColor == 'PAPAYAWHIP'){return 'FFEFD5';}
    if($vColor == 'PEACHPUFF'){return 'FFDAB9';}
    if($vColor == 'PERU'){return 'CD853F';}
    if($vColor == 'PINK'){return 'FFC0CB';}
    if($vColor == 'PLUM'){return 'DDA0DD';}
    if($vColor == 'POWDERBLUE'){return 'B0E0E6';}
    if($vColor == 'PURPLE'){return '800080';}
    if($vColor == 'RED'){return 'FF0000';}
    if($vColor == 'ROSYBROWN'){return 'BC8F8F';}
    if($vColor == 'ROYALBLUE'){return '4169E1';}
    if($vColor == 'SADDLEBROWN'){return '8B4513';}
    if($vColor == 'SALMON'){return 'FA8072';}
    if($vColor == 'SANDYBROWN'){return 'F4A460';}
    if($vColor == 'SEAGREEN'){return '2E8B57';}
    if($vColor == 'SEASHELL'){return 'FFF5EE';}
    if($vColor == 'SIENNA'){return 'A0522D';}
    if($vColor == 'SILVER'){return 'C0C0C0';}
    if($vColor == 'SKYBLUE'){return '87CEEB';}
    if($vColor == 'SLATEBLUE'){return '6A5ACD';}
    if($vColor == 'SLATEGRAY'){return '708090';}
    if($vColor == 'SLATEGREY'){return '708090';}
    if($vColor == 'SNOW'){return 'FFFAFA';}
    if($vColor == 'SPRINGGREEN'){return '00FF7F';}
    if($vColor == 'STEELBLUE'){return '4682B4';}
    if($vColor == 'TAN'){return 'D2B48C';}
    if($vColor == 'TEAL'){return '008080';}
    if($vColor == 'THISTLE'){return 'D8BFD8';}
    if($vColor == 'TOMATO'){return 'FF6347';}
    if($vColor == 'TURQUOISE'){return '40E0D0';}
    if($vColor == 'VIOLET'){return 'EE82EE';}
    if($vColor == 'WHEAT'){return 'F5DEB3';}
    if($vColor == 'WHITE'){return 'FFFFFF';}
    if($vColor == 'WHITESMOKE'){return 'F5F5F5';}
    if($vColor == 'YELLOW'){return 'FFFF00';}
    if($vColor == 'YELLOWGREEN'){return '9ACD32';}
    return $vColor;
}

// In case you are using an older version of PHP which lacks json_encode function
if(!function_exists('json_encode')) {
	function json_encode( $data ) { //from http://www.php.net/manual/en/function.json-encode.php#100835, for pre-PHP 5.1.6 and lower
		if( is_array($data) || is_object($data) ) { 
			$islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) ); 
			
			if( $islist ) { 
				$json = '[' . implode(',', array_map('json_encode', $data) ) . ']'; 
			} else { 
				$items = Array(); 
				foreach( $data as $key => $value ) { 
					$items[] = json_encode("$key") . ':' . json_encode($value); 
				} 
				$json = '{' . implode(',', $items) . '}'; 
			} 
		} elseif( is_string($data) ) { 
			# Escape non-printable or Non-ASCII characters. 
			$string = '"' . addslashes($data). '"';  //addcslashes($data, "\"\\\n\r\t\f/" . chr(8)) . '"'; 
			$json    = ''; 
			$len    = strlen($string); 
			# Convert UTF-8 to Hexadecimal Codepoints. 
			for( $i = 0; $i < $len; $i++ ) { 
				
				$char = $string[$i]; 
				$c1 = ord($char); 
				
				# Single byte; 
				if( $c1 <128 ) { 
					$json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1); 
					continue; 
				} 
				
				# Double byte 
				$c2 = ord($string[++$i]); 
				if ( ($c1 & 32) === 0 ) { 
					$json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128); 
					continue; 
				} 
				
				# Triple 
				$c3 = ord($string[++$i]); 
				if( ($c1 & 16) === 0 ) { 
					$json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128)); 
					continue; 
				} 
					
				# Quadruple 
				$c4 = ord($string[++$i]); 
				if( ($c1 & 8 ) === 0 ) { 
					$u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1; 
				
					$w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3); 
					$w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128); 
					$json .= sprintf("\\u%04x\\u%04x", $w1, $w2); 
				} 
			} 
		} else { 
			# int, floats, bools, null 
			$json = strtolower(var_export( $data, true )); 
		} 
		return $json; 
	}
}


function setWindowNavigation($pSingleWindow){
	
	
	
	$S                            = $_GET['historySession'];
	$I                            = $_GET['historyIndex'];
	$L                            = $_GET['historyLocation'];
	if($S == ''){$S = uniqid('1');}
	if($I == ''){$I = 0;}

	$_SESSION["nuHistory$S"][$I]  = $L;
	if($pSingleWindow == '1'){
		$boo                      = 'true';
	}else{
		$boo                      = 'false';
	}
	$s                            = "var nuFirstClick      = true;\n";
	$s                           .= "var nuHistorySession  = '$S';\n";
	$s                           .= "var nuHistoryIndex    = '$I';\n";
	$s                           .= "var nuHistoryLocation = '$L';\n";
	$s                           .= "var nuHistoryArray    = Array();\n\n";
	for($c = 0 ; $c <= $I ; $c ++){
		$s                       .= "nuHistoryArray[$c]    = '" . $_SESSION["nuHistory$S"][$c] . "';\n";
	}
	$s                           .= "\nfunction nuSingleWindow(){return $boo;}\n\n";

	return $s;

}

function breadCrumbHTML($singleWindow, $pFormName){

	$S                            = $_GET['historySession'];

	if($singleWindow != 1 or $_GET['historyIndex'] == '' or $_GET['historyIndex'] == '0'){return '';}

	$his         = '';
	for($i       = 1 ; $i <= $_GET['historyIndex'] ; $i ++){
		$explode = explode('|||',$_SESSION["nuHistory$S"][$i]);
		$title   = base64_decode(str_replace(' ','+',$explode[0]));
		$expTitle= explode('|||',$title);
		$his    .= "   <span id='nuCrumb$i' class='nuBreadCrumb' style='text-decoration:underline;display:inline;cursor:pointer' title='$expTitle[0]' onclick='gotoNuHistory($i)'>$expTitle[1]</span><span id='nuCrumbSpacer' class='nuBreadCrumb'> &nbsp;&#9658;&nbsp;</span>\n";
	}
	$his    .= "   <span id='nuCrumb' class='nuBreadCrumb' style='display:inline' title='" . nuTranslate('You are here') . "'>&nbsp;$pFormName</span>\n";
//	return $his;
	return "\n<!-- Bread Crumb -->\n<div id='nuBreadCrumb' style='position:absolute' class='nuBreadCrumb'>\n$his</div>\n<!-- -->\n";

}


function addHistory(){

	return '&historySession='  . $_GET['historySession'] . '&historyIndex=' . $_GET['historyIndex'] . '&historyLocation=' . $_GET['historyLocation'];

}


function addPreviousHistory(){

	$S        = $_GET['historySession'];
	$I        = $_GET['historyIndex'] - 1;
	$L        = $_SESSION["nuHistory$S"][$I];  //-- previous session

	return '&historySession='  . $_GET['historySession'] . '&historyIndex=' . $I . '&historyLocation=' . $L;

}

function nuID($prefix = '1'){

    while($id == uniqid($prefix)){}
    return uniqid($prefix);

}


function setLangArray(){

	$s = "\n\nvar nu_lang = new Array();\n";
	$s .= "nu_lang['SMTP Email Information Not Setup'] = '" . nuTranslateArray('SMTP Email Information Not Setup') . "';\n";
	$s .= "nu_lang['Please see your System Administrator'] = '" . nuTranslateArray('Please see your<BR>System Administrator') . "';\n";
	$s .= "nu_lang['HOME'] = '" . nuTranslateArray('HOME') . "';\n";
	$s .= "nu_lang['Yes'] = '" . nuTranslateArray('Yes') . "';\n";
	$s .= "nu_lang['No'] = '" . nuTranslateArray('No') . "';\n";
	$s .= "nu_lang['To'] = '" . nuTranslateArray('To') . "';\n";
	$s .= "nu_lang['From'] = '" . nuTranslateArray('From') . "';\n";
	$s .= "nu_lang['Reply To'] = '" . nuTranslateArray('ReplyTo') . "';\n";
	$s .= "nu_lang['Subject'] = '" . nuTranslateArray('Subject') . "';\n";
	$s .= "nu_lang['Message'] = '" . nuTranslateArray('Message') . "';\n";
	$s .= "nu_lang['Attached Report'] = '" . nuTranslateArray('Attached Report') . "';\n";
	$s .= "nu_lang['Filetype'] = '" . nuTranslateArray('Filetype') . "';\n";
	$s .= "nu_lang['Report ID'] = '" . nuTranslateArray('Report ID') . "';\n";
	$s .= "nu_lang['Send Read Receipt'] = '" . nuTranslateArray('Send Read Receipt') . "';\n";
	$s .= "nu_lang['Please Wait'] = '" . nuTranslateArray('Please Wait...') . "';\n";
	$s .= "nu_lang['The Email Is Sending'] = '" . nuTranslateArray('The Email Is Sending') . "';\n";
	$s .= "nu_lang['Email Successfully Sent'] = '" . nuTranslateArray('Email Successfully Sent') . "';\n";
	$s .= "nu_lang['Error'] = '" . nuTranslateArray('Error!') . "';\n";
	$s .= "nu_lang['Send Email'] = '" . nuTranslateArray('Send Email') . "';\n";
	$s .= "nu_lang['Cancel'] = '" . nuTranslateArray('Cancel') . "';\n";
	$s .= "nu_lang['Ok'] = '" . nuTranslateArray('Ok') . "';\n";
	$s .= "nu_lang['Please input an email address to send to'] = '" . nuTranslateArray('Please input an email address to send to') . "';\n";
	$s .= "nu_lang['Please input valid email address(es) to send to'] = '" . nuTranslateArray('Please input valid email address(es) to send to') . "';\n";
	$s .= "nu_lang['Please input an email address to send from'] = '" . nuTranslateArray('Please input an email address to send from') . "';\n";
	$s .= "nu_lang['Please input a valid email address to send from'] = '" . nuTranslateArray('Please input a valid email address to send from') . "';\n";
	$s .= "nu_lang['Please input a filename'] = '" . nuTranslateArray('Please input a filename') . "';\n\n\n";
	return $s;



}



?>
