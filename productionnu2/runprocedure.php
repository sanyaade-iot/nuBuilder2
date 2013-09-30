<?php
/*
** File:           runprocedure.php
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

session_start( );

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

$formValue = getSelectionFormVariables($form_ses);
$setup                                  = nuSetup();

$T                                      = nuRunQuery("SELECT * FROM zzsys_activity WHERE sat_all_code = '$report'");
$A                                      = db_fetch_object($T);

//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                       = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sysVariables                       = sysVariablesToHashArray($form_ses);                            //--values in sysVariables from the calling lookup page
	$arrayOfHashVariables               = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
	$newFormArray                       = arrayToHashArray($formValue);
	$arrayOfHashVariables               = joinHashArrays($arrayOfHashVariables, $newFormArray);             //--join the arrays together

	$newFormArray                       = arrayToHashArray($_GET);
	$arrayOfHashVariables               = joinHashArrays($arrayOfHashVariables, $newFormArray);             //--join the arrays together

	$GLOBALS['nuEvent'] = "(nuBuilder Procedure Code) of " . $A->sat_all_description . " : ";
	eval(replaceHashVariablesWithValues($arrayOfHashVariables, $A->sat_procedure_code));
	$GLOBALS['nuEvent'] = '';
	for($i = 0 ; $i < count($GLOBALS['table']) ; $i++){
		nuRunQuery('DROP TABLE IF EXISTS ' . $GLOBALS['table'][$i]);
	}
	$GLOBALS['table']                   = array();
	
?>