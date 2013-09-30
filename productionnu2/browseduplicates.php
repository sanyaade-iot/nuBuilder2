<?php
/*
** File:           browseduplicates.php
** Author:         nuSoftware
** Created:        2010/01/13
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

	$dir          = $_POST['dir'];
	$form_ses     = $_POST['form_ses'];
	$ses          = $_POST['ses'];
	$prefix       = $_POST['p'];
	$o            = $_POST['o'];
	$r            = $_POST['r'];
	$n            = $_POST['n'];

	if (strpos($dir,"..") !== false)
		die;
	
	require_once("../$dir/database.php");
	require_once('common.php');

	$object       = objectFields($r);
	$lookupForm   = formFields($object->sob_lookup_zzsysform_id);
	$TT           = TT();
	$browseTable  = $TT;
	$updateField  = array();

//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                    = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sesVariables['#TT#']            = $TT;
	$sesVariables['#browseTable#']   = $TT;
	$sesVariables['#formSessionID#'] = $form_ses;
	$sesVariables['#rowPrefix#']     = $prefix;
	$sysVariables                    = sysVariablesToHashArray($form_ses);                            //--values in sysVariables from the calling lookup page
	$arrayOfHashVariables            = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
	$nuHashVariables                 = $arrayOfHashVariables;   //--added by sc 23-07-2009
	//----------allow for custom code----------------------------------------------
	$GLOBALS['nuEvent'] = "(nuBuilder Before Browse) of " . $lookupForm->sfo_name . " : ";
	eval(replaceHashVariablesWithValues($arrayOfHashVariables, $lookupForm->sfo_custom_code_run_before_browse));
	$GLOBALS['nuEvent'] = '';

	$lookIn                          =  $object->sob_lookup_code_field;
	$newID                           = $n;
	$fieldNames                      = array();
	$t                               = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$object->sob_lookup_zzsysform_id'");
	$form                            = db_fetch_object($t);
	$old_sql_string                  = $form->sfo_sql;
	$new_sql_string                  = replaceHashVariablesWithValues($arrayOfHashVariables, $old_sql_string);
	$SQL                             = new sqlString($new_sql_string);
	
	if($SQL->where == ''){
		$SQL->setWhere("WHERE $lookIn = '$newID'");
	}else{
		$SQL->setWhere("$SQL->where AND ($lookIn = '$newID')");		
	}

	$SQL->removeAllFields();
	$SQL->addField(' COUNT(*) ');
	$T                               = nuRunQuery($SQL->SQL);
	$R                               = db_fetch_row($T);

	print $R[0];

?>