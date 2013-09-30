<?php
/*
** File:           formupdate.php
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

session_start();

$f          = $_GET['f'];
$r          = $_GET['r'];
$s          = $_GET['s'];
$dir        = $_GET['dir'];
$ses        = $_GET['ses'];
$changed    = $_POST;
$isNewRec   = $r == '-1';

if (strpos($dir,"..") !== false){die;}

require_once('../' . $dir . '/database.php');
require_once('common.php');

$r                            = db_real_escape_string($r);
$f                            = db_real_escape_string($f);
$setup                        = nuSetup();
$sysformID                    = $_GET['f'];
$recordID                     = $_GET['r'];
$newwin                       = $_GET['newwin'];  //-- was this opened in a new window
$fieldProperties              = array();
$recordFields                 = array();
$formFields                   = array();
$formValues                   = array();
$form                         = formFields($sysformID);
$dq                           = '"';
$bs                           = '\\';
$recordFields                 = FormObjectArrayList($sysformID, $form->sfo_table);   //--added  by sc 05/11/09



//=================run code BEFORE save=========================

	$GLOBALS['sys_box_net']                  = $form->sys_box_net;

//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                            = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sysVariables                            = postVariablesToHashArray();                                    //--values in $_POST
	$arrayOfHashVariables                    = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
    $arrayOfHashVariables['#formID#']        = $sysformID;
    $arrayOfHashVariables['#newID#']         = $recordID;
	$arrayOfHashVariables['#sys_box_net#']   = $GLOBALS['sys_box_net'];                                        //--box.net folder id for this form
	$nuHashVariables                         = $arrayOfHashVariables;                                         //--added by sc 23-07-2009
	
//----------allow for custom code----------------------------------------------

    $code                               = replaceHashVariablesWithValues($arrayOfHashVariables, $form->sfo_custom_code_run_before_save);
	$GLOBALS['nuEvent'] = "(nuBuilder Before Save) of " . $form->sfo_name . " : ";
	eval($code);
	$GLOBALS['nuEvent'] = "";
	
	if($errorMessage != ''){

		$fly  = $_GET['fly'];  //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform

        print "<html>\n";
		print "<head>\n";
		print "<meta http-equiv='Content-Type' content='text/html;'/>\n";
		print "<title></title>\n";
        jsinclude('common.js');
		print "<!-- Form Functions -->\n";
		print "<script type='text/javascript'>\n";
		print "/* <![CDATA[ */\n";
		print "function reload(pthis){//  reload form.php\n";
		print "   parent.document.forms[0].action = 'form.php?x=1&r=$recordID&dir=$dir&fly=$fly&ses=$ses&f=$sysformID&newwin=$newwin" . addHistory() . "';\n";
		print "   parent.document.forms[0].submit();\n";
		print "}\n";
		print "/* ]]> */ \n";
		print "</script>\n";
		print "<!-- End Of Form Functions line 84-->\n";
		print "</head>\n";
        print "<body onload='alert($dq$errorMessage$dq);reload();'>\n";
        print "<form name='theform' action='' method='post'></form></body></html>\n";

		return;
	}


	
//=================END OF run code BEFORE save=========================
		
$recordID                     = dbGetUniqueID($recordID, $form->sfo_table, $form->sfo_primary_key);

reset($_POST);


//  get formatting properties for this form's fields
$t                            = nuRunQuery("SELECT sob_all_name, sob_text_format FROM zzsys_object WHERE sob_all_type = 'text' AND sob_zzsys_form_id = '$sysformID'");
while ($r                     = db_fetch_object($t)){
	$fieldProperties[$r->sob_all_name] = $r->sob_text_format;
}
while(list($key, $value)      = each($_POST)){
	if(in_array ($key, $recordFields) and ($changed['z___'.$key] == '' or $isNewRec)){ //--valid fld name, has been changed or its a new record
		$formFields[]       = $key;
		$formValues[]       = "'" . mysql_real_escape_string(reformatField($value,$fieldProperties[$key],false)) . "'";	}
}
reset($_POST);
//  if the following fields are in the table they will be updated
if(in_array ('sys_added', $recordFields) and $isNewRec){
	$formFields[]           = 'sys_added';
	$formValues[]           = "'" . date('Y-m-d H:i:s') . "'";
}
if(in_array ('sys_changed', $recordFields)){
	$formFields[]           = 'sys_changed';
	$formValues[]           = "'" . date('Y-m-d H:i:s') . "'";
}
if(in_array ('sys_user_id', $recordFields)){
	$formFields[]           = 'sys_user_id';
	$formValues[]           = "'" . $_SESSION['nu_user_id'] . "'";
}
if(in_array ($form->sfo_table.'_sys_added', $recordFields) and $isNewRec){
	$formFields[]           = $form->sfo_table.'_sys_added';
	$formValues[]           = "'" . date('Y-m-d H:i:s') . "'";
}
if(in_array ($form->sfo_table.'_sys_changed', $recordFields)){
	$formFields[]           = $form->sfo_table.'_sys_changed';
	$formValues[]           = "'" . date('Y-m-d H:i:s') . "'";
}
if(in_array ($form->sfo_table.'_sys_user_id', $recordFields)){
	$formFields[]           = $form->sfo_table.'_sys_user_id';
	$formValues[]           = "'" . $_SESSION['nu_user_id'] . "'";
}
$insertFields               = '';
$insertValues               = '';
$updateString               = '';

for($i = 0 ; $i < count($formFields) ; $i++){

	if($updateString        == ''){
		$updateString       = "$formFields[$i] = $formValues[$i]";
	}else{
		$updateString       = "$updateString, $formFields[$i] = $formValues[$i]";
	}

}
if(count($formFields) > 0){ //--dont update if there has been nothing changed
	$s                      = "UPDATE $form->sfo_table SET $updateString WHERE $form->sfo_primary_key = '$recordID'";
	nuRunQuery($s);
}

nuUploadFiles($form->sfo_table, $form->sfo_primary_key, $recordID);
nuRemoveUploadedFiles($form->sfo_table, $form->sfo_primary_key, $recordID);   //-- SC 2010-08-05

reset($_POST);

for($i = 0 ; $i < $_POST['TheSubforms'] ; $i++){
	$SF->Name                   = $_POST['SubformNumber'.$i];
	$recordFields               = array();
	$SF->Table                  = $_POST['table'.$SF->Name];
	$t                          = nuRunQuery("SELECT * FROM $SF->Table WHERE FALSE");
	while ($f                   = db_fetch_field($t)){
		$recordFields[]         = $f->name;
	}

	$SF->ID                     = $_POST['subformid'.$SF->Name];
	$SF->Rows                   = $_POST['rows'.$SF->Name];
	$SF->Columns                = $_POST['columns'.$SF->Name];
	$SF->ForeignKey             = $_POST['foreignkey'.$SF->Name];
	$SF->PrimaryKey             = $_POST['primarykey'.$SF->Name];
	$SF->ReadOnly               = $_POST['readonly'.$SF->Name];
	$SF->ColumnName             = array();
	$SF->ColumnName             = FormObjectArrayList($SF->ID, $SF->Table);         //--added  by sc 05/11/09

	if($SF->ReadOnly !=1 ){ //--will not update readonly subforms
		updateSubform($_POST, $SF, $recordID);
	}
}
//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                            = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sysVariables                            = postVariablesToHashArray();                                    //--values in $_POST
	$arrayOfHashVariables                    = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together
    $arrayOfHashVariables['#newID#']         = $recordID;
    $arrayOfHashVariables['#formID#']        = $sysformID;
	$arrayOfHashVariables['#sys_box_net#']   = $GLOBALS['sys_box_net'];                                        //--box.net folder id for this form
	$nuHashVariables                         = $arrayOfHashVariables;   //--added by sc 23-07-2009

//----------allow for custom code----------------------------------------------

    $code                               = replaceHashVariablesWithValues($arrayOfHashVariables, $form->sfo_custom_code_run_after_save);

	$GLOBALS['nuEvent'] = "(nuBuilder After Save) of " . $form->sfo_name . " : ";
	eval($code);
	$GLOBALS['nuEvent'] = "";

	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv='Content-Type' content='text/html;'/>\n";
	print "<title></title>\n";
    jsinclude('common.js');
	print "<!-- Form Functions -->\n";
	print "<script type='text/javascript'>\n";
	print "/* <![CDATA[ */\n";
	print setWindowNavigation($setup->set_single_window);

	$refreshAfterSave    = $_POST['refresh_after_save'];   //-- refresh the BROWSE form (0 or 1)
	$closeAfterSave      = $_POST['close_after_save'];     //-- close the EDIT form  

//===================================================
// $closeAfterSave
// 0  = save and reload page
// 1  = close page after save
// 2  = save and reload page with a blank record
//===================================================
// $fly
// 1  = means its being added on the fly from a Lookup Browse Form 
// 2  = means its being added/edited on the fly from a Browse Subform
//===================================================

$history           = $_GET['historyIndex'];
$breadcrumb        = addHistory();
$fly               = $_GET['fly'];
if($closeAfterSave == '2'){  //-- display a blank record on reload (r = -1)
	$recordID      = '-1';
}

if($newwin == '1'){  
//================ separate window is open ==================================
	print "function reload(pthis){//  separate window form.php\n";
	if($_GET['fly'] == '1'){
		print "   parent.opener.getRecordFromIframeList('$recordID'); \n";   //-- close window and close iframe and populate lookup
		print "   window.close(); \n";
	}else{
		print "   try{\n";
		print "      parent.opener.document.forms[0].submit();\n";
		print "   }catch(err){}\n";
		if($closeAfterSave == '1'){       
			print "      window.close();\n";
		}else{
			print "      document.forms[0].action = 'form.php?x=1&r=$recordID&dir=$dir&fly=$fly&ses=$ses&f=$sysformID&newwin=$newwin$breadcrumb';\n";
			print "      document.forms[0].submit();\n";
		}
	}
	print "}\n";

}else{
//================ in single window (using breadcrumbs) ======================
	print "function reload(pthis){//  single window form.php\n";
	if($closeAfterSave == '1'){       
		print "   gotoNuHistory($history);\n";
	}else{
		print "   try{\n";
		print "      document.forms[0].action = 'form.php?x=1&r=$recordID&dir=$dir&fly=$fly&ses=$ses&f=$sysformID&newwin=$newwin$breadcrumb';\n";
		print "      document.forms[0].submit();\n";
		print "   }catch(err){}\n";
	}
	print "}\n";
}


	print "/* ]]> */ \n";
	print "</script>\n";
	print "</head>\n";
	print "<body onload='reload()'>\n";
	print "<form name='theform' action='' method='post'>\n";
	print "</form></body></html>\n";
	
	if($_POST['nuMoved'] != ''){
		$o = explode('|',$_POST['nuMoved']);  //-- eg '|cus_Total,136,566|cus_mobile,288,46|cus_postcode,416,174'
		for($i = 1 ; $i < count($o) ; $i++){
			$O = explode(',',$o[$i]);
			$n = $O[0];
			$t = $O[1];
			$l = $O[2];
			$f = $_GET['f'];
			
			nuRunQuery("UPDATE zzsys_object SET sob_all_top = '$t', sob_all_left = '$l', sob_subform_top = '$t', sob_subform_left = '$l' WHERE sob_all_name = '$n' AND sob_zzsys_form_id = '$f'");
		}
	}

return;


function updateSubform($P, $SF, $foreignKey){

	$bs                           = '\\';
	$dq                           = '"';
	$insertFields                 = '';
	$insertValues                 = '';
	
//  get subform's insert sql statement
	$t                            = nuRunQuery("SELECT sob_subform_table, sob_subform_primary_key FROM zzsys_object WHERE zzsys_object_id = '$SF->ID'");
	$subform                      = db_fetch_object($t);
//  get formatting properties for this form's fields
	$t                            = nuRunQuery("SELECT sob_all_name, sob_text_format FROM zzsys_object WHERE sob_all_type = 'text' AND sob_zzsys_form_id = '$SF->ID'");
	while ($r                     = db_fetch_object($t)){
		$fieldProperties[$r->sob_all_name] = $r->sob_text_format;
	}

	for($i = 0 ; $i < $SF->Rows ; $i++){

		$PRE                  = $SF->Name . right('000'.$i,4);
		$checkBox             = 'row'     . $PRE;
		$primaryKey           = db_real_escape_string($P[$PRE . $SF->PrimaryKey]);
		$isNewRec             = $primaryKey == '';

		if($P[$checkBox] != 'on' and $isNewRec){
			$primaryKey       = dbGetUniqueID($P[$PRE . $SF->PrimaryKey], $subform->sob_subform_table, $subform->sob_subform_primary_key);
		}

		if($P[$checkBox] == 'on'){
			if($primaryKey != ''){  //-- delete record
				nuRunQuery("DELETE FROM $subform->sob_subform_table WHERE $subform->sob_subform_primary_key = '$primaryKey'");
			}
		}else{                      //-- update record
			for($I = 0 ; $I < count($SF->ColumnName) ; $I++){
				if($P['z___'.$PRE.$SF->ColumnName[$I]] == '' or $isNewRec){   //---field needs to be included in update query
					$fieldTitle           = $SF->ColumnName[$I];
					$fieldValue           = $P["$PRE$fieldTitle"];
//					$fieldValue           = "'" . str_replace("'","\'",str_replace($bs,$bs.$bs,reformatField($fieldValue,$fieldProperties[$fieldTitle],false))) . "'";
					$fieldValue           = "'" . mysql_real_escape_string(reformatField($fieldValue,$fieldProperties[$fieldTitle],false)) . "'";

					if($insertFields  == ''){
						$insertFields = "$SF->ForeignKey = $dq$foreignKey$dq, $fieldTitle = $fieldValue";
					}else{
						$insertFields = "$insertFields, $fieldTitle = $fieldValue";
					}
				}
			}
			if($insertFields  != ''){ //-- only update if something was changed or added
				$s                    = "UPDATE $subform->sob_subform_table SET #fields# WHERE $subform->sob_subform_primary_key = '$primaryKey'";
				$runSQL               = str_replace('#fields#', $insertFields, $s);
				nuRunQuery($runSQL);
				
				nuUploadFiles($subform->sob_subform_table, $subform->sob_subform_primary_key, $primaryKey, $PRE);           //-- SC 2010-07-15
				nuRemoveUploadedFiles($subform->sob_subform_table, $subform->sob_subform_primary_key, $primaryKey, $PRE);   //-- SC 2010-08-05

				$insertFields             = '';
				$insertValues             = '';
			}
		}
	}
	
}


function FormObjectArrayList($pFormID, $pTableName){   //--added by sc 05/11/09

//-- this makes an array of valid fieldnames to be used when updating a form or subform

	$o                          = array();
	$recordFields               = array();
	$formObjects                = array();
	$validObjects               = array('dropdown','display','inarray','lookup','text','textarea');  //-- the field types that may need to be updated

	$T                          = nuRunQuery("SELECT * FROM $pTableName WHERE FALSE");

	while ($R                   = db_fetch_field($T)){
		$recordFields[]         = $R->name;
	}

	$t                          = nuRunQuery("SELECT sob_all_name, sob_all_type FROM zzsys_object WHERE sob_zzsys_form_id = '$pFormID'");
	while($r                    = db_fetch_object($t)){

	if(in_array($r->sob_all_type, $validObjects) AND in_array($r->sob_all_name, $recordFields)){  //--- if a valid fieldtype and valid fieldname (a fieldname  that's in this table)
			$formObjects[]      = $r->sob_all_name;
		}
	}

	$res = nuRunQuery("SHOW COLUMNS FROM $pTableName LIKE 'sys_%'");
	$sys_fields = array();
	while ($obj = db_fetch_object($res))
		$sys_fields[] = $obj->Field;
	
	if (in_array('sys_added', $sys_fields))
		$formObjects[] = 'sys_added';
	if (in_array('sys_changed', $sys_fields))
		$formObjects[] = 'sys_changed';
	if (in_array('sys_user_id', $sys_fields))
		$formObjects[] = 'sys_user_id';
		
	return $formObjects;

}


function nuUploadFiles($pTableName, $pPrimaryKey, $pID, $pPrefix = ''){ //-- SC 2010-07-15

//-- this makes an array of fields in this table

	$recordFields                     = array();
	$prefixLength                     = strlen($pPrefix);
	$T                                = nuRunQuery("SELECT * FROM $pTableName WHERE FALSE");
	while ($R                         = db_fetch_field($T)){
		$recordFields[]               = $R->name;
	}
	reset($_FILES);

	while(list($key, $value)          = each($_FILES)){

		$fieldWithoutPrefix           = substr($key, $prefixLength);

		if(in_array($fieldWithoutPrefix, $recordFields)){              //-- there is a field of the same name in the table

			if(substr($key,0,$prefixLength) == $pPrefix){              //-- only do stuff for this row

				if($_FILES[$key]['tmp_name'] != '' and $_POST['delete_file_' . $key] <> 'on'){  //-- a file has been selected to upload - and is not to be deleted (ticked)
				
					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_size');
					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_type');
					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_name');

					$filename             = $_FILES[$key]['tmp_name'];

					$handle               = fopen($filename, "rb");
					$contents             = fread($handle, filesize($filename));
					$contents             = addslashes($contents);
					fclose($handle);
					
					$filesize             = $_FILES[$key]['size'];
					$filetype             = $_FILES[$key]['type'];
					$filename             = $_FILES[$key]['name'];

					$FS                   = $fieldWithoutPrefix . '_file_size';
					$FT                   = $fieldWithoutPrefix . '_file_type';
					$FN                   = $fieldWithoutPrefix . '_file_name';

					nuRunQuery("UPDATe `$pTableName` SET `$fieldWithoutPrefix` = '$contents', `$FS` = '$filesize', `$FT` = '$filetype', `$FN` = '$filename' WHERE `$pPrimaryKey` = '$pID'");
					
				}
			}
		}
	
	}

}

//-----------------------------------------------

function nuRemoveUploadedFiles($pTableName, $pPrimaryKey, $pID, $pPrefix = ''){ //-- SC 2010-08-05

//-- this makes an array of fields in this table

	$recordFields                     = array();
	$prefixLength                     = strlen($pPrefix);
	$T                                = nuRunQuery("SELECT * FROM $pTableName WHERE FALSE");
	while ($R                         = db_fetch_field($T)){
		$recordFields[]               = $R->name;
	}
	reset($_FILES);

	while(list($key, $value)          = each($_FILES)){

		$fieldWithoutPrefix           = substr($key, $prefixLength);

		if(in_array($fieldWithoutPrefix, $recordFields)){              //-- there is a field of the same name in the table

			if(substr($key,0,$prefixLength) == $pPrefix){              //-- only do stuff for this row

				if($_POST['delete_file_' . $key] == 'on'){             //-- is to be deleted (ticked)
				
					$FS                   = $fieldWithoutPrefix . '_file_size';
					$FT                   = $fieldWithoutPrefix . '_file_type';
					$FN                   = $fieldWithoutPrefix . '_file_name';

					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_size');
					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_type');
					addFileFields($fieldWithoutPrefix, $recordFields, $pTableName, '_file_name');
					
					nuRunQuery("UPDAtE `$pTableName` SET `$fieldWithoutPrefix` = '', `$FS` = '', `$FT` = '', `$FN` = '' WHERE `$pPrimaryKey` = '$pID'");
					
				}
			}
		}
	
	}

}


//-------------------------------------------------



function addFileFields($pFieldWithoutPrefix, $pRecordFields, $pTableName, $pExtraFieldName){

	if(!in_array($pFieldWithoutPrefix . $pExtraFieldName, $pRecordFields)){     //-- there is not a field to hold the file info
		nuRunQuery("ALTER TABLE `$pTableName` ADD `$pFieldWithoutPrefix$pExtraFieldName` VARCHAR(255) NOT NULL AFTER `$pFieldWithoutPrefix` ");
	}

}



?>
