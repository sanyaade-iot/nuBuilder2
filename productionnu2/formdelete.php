<?php
/*
** File:           formdelete.php
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

	$ses                          = $_GET['ses'];
	$recordID                     = $_GET['r'];
	$sysformID                    = $_GET['f'];
	$dir                          = $_GET['dir'];
	$newwin                       = $_GET['newwin'];
	$_GET['fly']                  = (isset($_GET['fly']) ? $_GET['fly'] : '');
	
	if (strpos($dir,"..") !== false)
		die;
	
	require_once("../$dir/database.php");
	require_once('common.php');

	$recordID = db_real_escape_string($recordID);
	
    if(passwordNeeded($sysformID)){
		if(!continueSession()){
    		print nuTranslate('You have been logged out');
    		return;
    	}
    }

	$setup                         = nuSetup();

//----------create an array of hash variables that can be used in any "hashString" 
	$sVariables                             = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
//----------allow for custom code----------------------------------------------
//	eval(replaceHashVariablesWithValues($sVariables, $setup->set_php_code)); //--replace hash variables then run code


	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv='Content-Type' content='text/html'/>\n";
	print "<title></title>\n";

    jsinclude('common.js');
	
	print "<!-- Form Functions -->\n";
	print "<script type='text/javascript'>\n";
	print "/* <![CDATA[ */\n";

	print setWindowNavigation($setup->set_single_window);

	print "function reload(pthis){//  reload form.php\n";
		
	if($_GET['fly'] == '2'){
		$notSingle = ' && false';
	}else{
		$notSingle = '';
	}
		
	if($_POST['del_ok'] == '1'){

		print "    if('1' == '$newwin'){\n";
		print "        parent.opener.document.forms[0].submit();\n";
		print "        window.close();\n";
		print "    }else{\n";
		print "        var encodedloc           = '" . $_GET['historyLocation'] . "';\n";
		print "        var loc1                 = encodedloc.split('|||')[1];\n";
		print "        var loc                  = Base64.decode(loc1);\n";
		print "        document.forms[0].action = loc;\n";
		print "        document.forms[0].submit();\n";
		print "    }\n";
		
		$theform = formFields($sysformID);
		
		// DELETE Subform items (children) BEFORE deleting the Form item (parent) - Foreign Key compliant  
		$t = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$sysformID' and sob_all_type = 'subform'");
		while($r = db_fetch_object($t)){
			if(sob_subform_zzsysform_id == ''){  //-- not a browse form
				nuRunQuery("DELETE FROM $r->sob_subform_table WHERE $r->sob_subform_foreign_key = '$recordID'");
			}
		}

		// DELETE Form item (parent)
		nuRunQuery("DELETE FROM $theform->sfo_table WHERE $theform->sfo_primary_key = '$recordID'");

//----------create an array of hash variables that can be used in any "hashString" 
		$arrayOfHashVariables1         = postVariablesToHashArray();//--values of this record
		$arrayOfHashVariables1['#id#'] = $theRecordID;                                                  //--this record's id
		$arrayOfHashVariables          = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
		$arrayOfHashVariables          = joinHashArrays($arrayOfHashVariables, $arrayOfHashVariables1);      //--join the arrays together
		$nuHashVariables               = $arrayOfHashVariables;   //--added by sc 23-07-2009
//----------allow for custom code----------------------------------------------
		$GLOBALS['nuEvent'] = "(nuBuilder After Delete) of " . $theform->sfo_name . " : ";
		eval(replaceHashVariablesWithValues($arrayOfHashVariables, $theform->sfo_custom_code_run_after_delete));
		$GLOBALS['nuEvent'] = '';
	}else{
		$newwin = $_GET['newwin'];  //-- was this opened in a new window
		$fly    = $_GET['fly'];     //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform
		print "   document.forms[0].action = 'form.php?x=1&r=$recordID&dir=$dir&fly=$fly&ses=$ses&f=$sysformID&newwin=$newwin" . addHistory() . "';\n";
		print "   document.forms[0].submit();\n";
	}
	print "}\n";
	print "/* ]]> */ \n";
	print "</script>\n";
	print "<!-- End Of Form Functions -->\n";
	
	print "</head>\n";
	print "<body onload='reload()'>\n";
	print "<form name='theform' action='' method='post'>\n";

?>
