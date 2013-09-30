<?php
/*
** File:           browseprint.php
** Author:         nuSoftware
** Created:        2011/01/28
** Last modified:  2012/08/30
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

// ========================== Standard Includes ====================================
session_start( );
$dir                             = $_GET['dir'];
$ses                             = $_GET['ses'];
$f                               = $_GET['f'];
if (strpos($dir,"..") !== false) {die();}
require_once("../$dir/database.php");
require_once('common.php');


$formFields                      = formFields($f);
$sesVariables                    = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
if($formFields->sfo_print_button    == '1'  and displayCondition($sesVariables, $formFields->sfo_print_button_display_condition)){
}else{
	print  nuTranslate('You do not have access');
	die();
}

		
		
if($formFields->sfo_print_button != '1'){
	print  nuTranslate('You do not have access');
	die();
}


// ========================== Standard Includes ====================================

// SELECT Browse Table Data from zzsys_trap
$id = $_GET['id'];
$BP_data = nurunquery("SELECT tra_message FROM zzsys_trap WHERE zzsys_trap_id = $id");
if (!$row = db_fetch_object($BP_data)){ 
	echo "Error: Unable to Browse Data to display."; 
	die();
}
eval($row->tra_message);

// SQL and Temporary-Table data used to build the report. 
$BP_tt 			= TT();
$browseTable   	= $BP_tt;
$BP_ttold 		= $TRAP_tmp;
$BP_b4			= str_replace( $BP_ttold , $BP_tt , hex_str($TRAP_b4sql));
$BP_query		= str_replace( $BP_ttold , $BP_tt , hex_str($TRAP_sql));
$BP_cols		= explode("~", hex_str($TRAP_col));
$BP_cnt			= (int)$TRAP_cnt;

// Table formatting and column headings
$BP_width 		= array();
$BP_align 		= array();
$BP_title 		= array();
$BP_format 		= array();
$TblWdt			= 0;
for($i=0; $i<$BP_cnt; $i++) {
	$data = explode(",", $BP_cols[$i]);
	$BP_width[$i] = (int)$data[0];
	$BP_align[$i] = $data[1];
	$BP_title[$i] = $data[2];
	$BP_format[$i] = $data[3];
	$TblWdt += $BP_width[$i]; 
}
if ($TblWdt > 968) {$TblWdt = 968;}
	
// HTML Head and CSS
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n\n";
echo "<html>\n";
echo "<head><title>Browse Print</title>\n";	
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n\n";
echo "<!-- Include nuBuilder Styles. The nuBuilder style '.browseprint' will effect the body tag of this document --> \n";
echo makeCSS();
echo "<!-- Include nuBuilder Styles. The nuBuilder style '.browseprint' will effect the body tag of this document --> \n\n";
echo "<style type='text/css'>\n";
echo "body { background:white; color:black; }\n";
echo "table { border-collapse:collapse; table-layout:fixed; width:" . $TblWdt . "px; white-space:nowrap; }\n";
echo "table, th, td { border:1px solid Gainsboro; }\n";
echo "td { padding:2px; white-space:nowrap; overflow:hidden; height:20px; font-size:10pt; font-family:tahoma; font-weight:normal; background-color:White; }\n";
echo "th { padding:2px; white-space:nowrap; overflow:hidden; height:22px; font-size:10pt; font-family:tahoma; font-weight:bold; background-color:Silver; }\n";

echo "\n /* *************** FIREFOX ignores COL and COLGROUP alignment ******************** */ \n";
for($i=0; $i<$BP_cnt; $i++) {
	echo firefox_align($BP_width[$i], align($BP_align[$i]), ($i+1)); 
}
echo "/* *************** FIREFOX ignores COL and COLGROUP alignment ******************** */ \n\n";

echo "</style>\n\n";
echo "</head>\n\n";
echo "<body class=\"browseprint\">\n\n";	
	
// Set ColumnGroups to align and size the table columns	
echo "<table>\n\n";
echo "\t<colgroup>\n";
for($i=0; $i<$BP_cnt; $i++) {
	echo "\t\t<col width=" . $BP_width[$i] . " align=" . align($BP_align[$i]) . ">\n"; 
}
echo "\t</colgroup>\n\n";

// Output the table header row	
echo "\t<tr>";
for($i=0; $i<$BP_cnt; $i++) {
	echo "<th>" . $BP_title[$i] . "</th>"; 
}
echo "</tr>\n\n";

// Run the SQL and loop through results, building the report
eval($BP_b4);
$data = nuRunQuery($BP_query);
while($row = db_fetch_row($data)) {
   echo "\t<tr>";
   for($i=0; $i<$BP_cnt; $i++) {
	  if (Trim($row[$i]) == "") { 
         echo "<td>&nbsp;</td>"; 
	  }
	  else { 
	     echo "<td>" . formatTextValue($row[$i], $BP_format[$i]) . "</td>"; 
	  }
   }
   echo "</tr>\n";  
}
echo "\n</table>\n\n";

// Close off the HTML
echo "</body>\n";
echo "</html>\n";	

// DROP the temporary table used to build the report
nuRunQuery("DROP TABLE IF EXISTS $BP_tt");

// Function to return style sheet elements to align columns in FireFox
function firefox_align($width,$align,$col) {
	if ($col == 1) {
		$str = "tr>th, tr>td";
	}
	else {
		$str = "";
		$str .= trim(str_repeat("th+", $col),"+");
		$str .= ",";
		$str .= trim(str_repeat("td+", $col),"+");
	}
	return $str . " {text-align:" . $align . "; width:" . $width . "px;}\n";
}
?>

