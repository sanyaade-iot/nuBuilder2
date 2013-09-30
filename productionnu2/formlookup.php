<?php
/*
** File:           formlookup.php
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
	$lookupField  = array();

//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                    = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sesVariables['#TT#']            = $TT;
	$sesVariables['#browseTable#']   = $TT;
	$sesVariables['#formSessionID#'] = $form_ses;
	$sesVariables['#rowPrefix#']     = $prefix;
	$sysVariables                    = sysVariablesToHashArray($form_ses);                            //--values in sysVariables from the calling lookup page
	$arrayOfHashVariables            = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
	$nuHashVariables                 = $arrayOfHashVariables;   //--added by sc 23-07-2009
	$GLOBALS['nuEvent'] = "(nuBuilder Before Browse) of " . $lookupForm->sfo_name . " : ";
	eval(replaceHashVariablesWithValues($arrayOfHashVariables, $lookupForm->sfo_custom_code_run_before_browse));
	$GLOBALS['nuEvent'] = '';

	if(	$o  == 'id'){  //id or code
		$lookIn   =  $object->sob_lookup_id_field;
	}else{
		$lookIn   =  $object->sob_lookup_code_field;
	}
	$newID        = $n;
	$fieldNames   = array();

	$t            = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$object->sob_lookup_zzsysform_id'");
	$form         = db_fetch_object($t);

	$uncheckBox   = '';
	if(hasDeleteBox($object->sob_zzsys_form_id)){
		$uncheckBox   = 'row' . $prefix;
    }

	$old_sql_string                = $form->sfo_sql;
	$new_sql_string                = replaceHashVariablesWithValues($arrayOfHashVariables, $old_sql_string);
	
	$SQL               = new sqlString($new_sql_string);
	
	if($SQL->where == ''){
		$SQL->setWhere("WHERE $lookIn = '$newID'");
	}else{
		$SQL->setWhere("$SQL->where AND ($lookIn = '$newID')");		
	}
	
	$SQL->removeAllFields();
	$SQL->addField($object->sob_lookup_id_field);
	$fieldNames[]                          = $prefix.$object->sob_all_name;
	$SQL->addField($object->sob_lookup_code_field);
	$fieldNames[]                          = 'code'.$prefix.$object->sob_all_name;

	if($object->sob_lookup_no_description != '1' and $object->sob_lookup_description_field != ''){
		$fieldNames[]                      = 'description'.$prefix.$object->sob_all_name;
		$SQL->addField($object->sob_lookup_description_field);
	}
	
	$t                                     = nuRunQuery("SELECT * FROM zzsys_lookup WHERE slo_zzsys_object_id = '$object->zzsys_object_id'");
	while($result                          = db_fetch_object($t)){
		$SQL->addField($result->zzsys_slo_table_field_name);
		$fieldNames[]                      = $prefix.$result->zzsys_slo_page_field_name;
	}

	$sql1                                  = 'old    : '.$old_sql_string;
	$sql2                                  = 'new    : '.$new_sql_string;
	$sql3                                  = 'newest : '.$SQL->SQL;
	$T                                     = nuRunQuery($SQL->SQL);
	$R                                     = db_fetch_row($T);

	$arrayOfHashVariables['#selectedID#']  = $R[0];                                                                   //--ID of selected record

	$GLOBALS['nuEvent'] = "(nuBuilder After Browse) of " . $lookupForm->sfo_name . " : ";
	eval(replaceHashVariablesWithValues($arrayOfHashVariables, $lookupForm->sfo_custom_code_run_after_browse));
	$GLOBALS['nuEvent'] = '';

	
	for($i = 0; $i < count($fieldNames) ; $i++){
		$lookupField[$fieldNames[$i]]      = $R[$i];   //-- added sc 30-12-09
	}
	
	$s = "<?xml version='1.0' encoding='utf-8'?>";
	$s .= "<lookup>";
	if($object->sob_lookup_javascript != ""){
		$s .= "<javascript>".rawurlencode($object->sob_lookup_javascript)."</javascript>";
	}
	if($uncheckBox != ""){
		$s .= "<subformrow>".rawurlencode($uncheckBox)."</subformrow>"; // if the lookup is in a subform, this will be the id of the delete checkbox to uncheck
	}
	
	while(list($key, $value)= each($lookupField)){
		$s .= "<attribute><key>".rawurlencode($key)."</key><value>".rawurlencode($value)."</value></attribute>";
		update_zzsys_variables($key, $value);
	}
	while(list($key, $value)= each($updateField)){
		$s .= "<update><key>".rawurlencode($key)."</key><value>".rawurlencode($value)."</value></update>";
		update_zzsys_variables($key, $value);
	}
	$s .= "</lookup>";
	
	header("Content-type: text/xml"); // tell the browser to interpret it as xml
	
	print $s;

function hasDeleteBox($pParentID){
	
	$t = nuRunQuery("SELECT * FROM zzsys_object WHERE zzsys_object_id = '$pParentID'");
	$r = db_fetch_object($t);
	return $r->sob_subform_delete_box == '1';

}


function update_zzsys_variables($pField, $pValue){

	$form_ses             = $_POST['form_ses'];
	setnuVariable($_POST['form_ses'], nuDateAddDays(Today(),2), $pField, $pValue);

}


function addJSFunction($pCode){
	//do nothing -can't use javascript here
}



?>