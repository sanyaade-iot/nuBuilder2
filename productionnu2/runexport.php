<?php
/*
** File:           runexport.php
** Author:         nuSoftware
** Created:        2007/04/26
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

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    session_cache_limiter("public");
}
session_start();

header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename=file.csv');
header('Cache-Control: no-cache');

$ses                             = $_GET['ses'];
$form_ses                        = $_GET['form_ses'];
$report                          = $_GET['r'];
$dir                             = $_GET['dir'];

if (strpos($dir,"..") !== false)
	die;

require_once("../$dir/database.php");
require_once('common.php');

if(activityPasswordNeeded($report)){
	if(!continueSession()){
		print nuTranslate('You have been logged out');
		return;
	}
}



$setup                                      = nuSetup();
//eval($setup->set_php_code);
$T                                          = nuRunQuery("SELECT * FROM zzsys_activity WHERE sat_all_code = '$report'");
$A                                          = db_fetch_object($T);
$dataTable                                  = TT();
$formValue                                  = getSelectionFormVariables($form_ses);

//----------allow for custom code----------------------------------------------







//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                       = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sysVariables                       = sysVariablesToHashArray($form_ses);                            //--values in sysVariables from the calling lookup page
	$sesVariables['#dataTable#']        = $dataTable;
	$arrayOfHashVariables               = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
	$newFormArray                       = arrayToHashArray($formValue);
	$arrayOfHashVariables               = joinHashArrays($arrayOfHashVariables, $newFormArray);             //--join the arrays together

	$newFormArray                       = arrayToHashArray($_GET);
	$arrayOfHashVariables               = joinHashArrays($arrayOfHashVariables, $newFormArray);             //--join the arrays together

	$GLOBALS['nuEvent'] = '(nuBuilder Export Code) : ';
	$GLOBALS['nuEvent'] = "(nuBuilder Export Code) of " . $A->sat_all_description . " : ";
	eval(replaceHashVariablesWithValues($arrayOfHashVariables, $A->sat_export_data_code));
	$GLOBALS['nuEvent'] = '';



	for($i = 0 ; $i < count($GLOBALS['table']) ; $i++){
		nuRunQuery('DROP TABLE IF EXISTS ' . $GLOBALS['table'][$i]);
	}
	$GLOBALS['table']                   = array();








//eval($A->sat_export_data_code);

$ascii                                      = $A->sat_export_delimiter;

if ($A->sat_export_use_quotes == '0') {
	$dq	= '';
} else {
	$dq	= '"';

}

$t                                          = nuRunQuery("SELECT * FROM $dataTable");
$Field                                      = array();
$Field                                      = tableFieldNamesToArray($t);

if($A->sat_export_header != '0'){
	
	for ($i = 0 ; $i < count($Field) ; $i++) {
	    if($i > 0){print chr($ascii);}
	    print $Field[$i];
	}
	print "\r\n";

}

$t                                          = nuRunQuery("SELECT * FROM $dataTable");
while($r = db_fetch_array($t)){
    for($f = 0 ; $f < count($Field) ; $f++){
       if($f > 0){print chr($ascii);}
//       $theFieldValue                       = str_replace(chr(13).chr(10), "<br/>", $r[$Field[$f]]);
//       $theFieldValue                       = str_replace(chr(13), "<br/>", $theFieldValue);
//       $theFieldValue                       = str_replace(chr(10), "<br/>", $theFieldValue);
       $theFieldValue                       = str_replace('"', '', $r[$Field[$f]]);
       print $dq.$theFieldValue.$dq;
    }
    print "\r\n";
}
nuRunQuery("DROP TABLE $dataTable");
?>
