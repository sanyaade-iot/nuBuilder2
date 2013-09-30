<?php
        session_start();
?>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html'/>
<title></title>
<script type='text/javascript' src='jquery.js' language='javascript'></script>
<script type='text/javascript' src='common.js' language='javascript'></script>
<!-- Form Functions -->
<script type='text/javascript'>
/* <![CDATA[ */


//-------------check form fields-----------------
function checkField(pName, pFormat, pTitle){
//  var nuScreen = window.parent.frames[0];
  var nuScreen = window.parent;
  var nuObject = nuScreen.document.getElementById(pName);

  if(nuObject){

    if(pFormat != ''){ //-- check formatted value and set to blank if wrong
                nuObject.accept = pFormat;
                nuFormat(nuObject);
        }
        if(nuObject.value == ''){
        alert(pTitle + ' cannot be left blank');
        return false;
    }
  }else{
    alert("Cannot find field titled '" + pTitle + "'");
        return false;
  }
  return true;
}



//-------------check subform fields-----------------
function checkSubform(pNames, pFormats, pTitles, pSubformName,  pRows){

//          var nuScreen = window.parent.frames[0];
          var nuScreen = window.parent;
          var PRE      = pSubformName;
          var SUF      = '';

            for(var i = 0 ; i < pRows ; i++){

                SUF = padWithZeros(i, 4);
                        for(I = 0 ; I < pNames.length ; I ++){

                    nuCBox   = nuScreen.document.getElementById('row' + PRE + SUF);
                            if(nuCBox.checked != true){
                                        nuObject = nuScreen.document.getElementById(PRE + SUF + pNames[I]);

                                        if(nuObject){

                                                if(pFormats[I] != ''){ //-- check formatted value and set to blank if wrong
                                                        nuObject.accept = pFormats[I];
                                                        nuFormat(nuObject);
                                                }
                                                if(nuObject.value == ''){
                                                        alert(pTitles[I] + ' cannot be left blank (on row ' + (i+1) + ')');
                                                        return [nuObject.id,I];
                                                }
                                        }else{
                                                alert("Cannot find field titled '" + pTitle + "'");
                                                return [nuObject.id,I];
                                        }
                                }
                        }
                }
                return null;
}


function padWithZeros(number, length) {

    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }

    return str;

}




<?php
/*
** File:           formduplicate.php
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

$f         = $_GET['f'];
$r         = $_GET['r'];
$dir       = $_GET['dir'];
$ses       = $_GET['ses'];
$form_ses  = $_GET['form_ses'];
$debug     = $_GET['debug'];

require_once('../' . $dir . '/database.php');
require_once('common.php');

$formID                     = $f;
$recordID                   = $r;
$sessionID                  = $ses;
$value                      = array();
$form                       = formFields($formID);
$t                          = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$f'");
$dq                         = '"';



print "\n";
print "\n";
print setFormatArray();  //---defines array for formatting text objects
print "\n";
print "\n";

print "function checkDuplicate(){\n";
while($r               = db_fetch_array($t)){
        if($r['sob_'.$r['sob_all_type'].'_no_duplicates'] == '1'){ // eg 'sob_dropdown_no_duplicates'
                $field         = $r['sob_all_name'];
                $value         = getFormValue($form_ses, $r['sob_all_name']);
                $newvalue      = $value[0];
                if($r['sob_'.$r['sob_all_type'].'_format']!=''){
                        $newvalue = reformatField($value[0], $r['sob_'.$r['sob_all_type'].'_format'],false);
                }
                if(isDuplicate($sessionID, $form, $recordID, $field, $newvalue)){

                        $T         = nuRunQuery("SELECT sob_all_title FROM zzsys_object WHERE zzsys_object_id = '".$r['zzsys_object_id']."'");
                        $R         = db_fetch_object($T);
                        $S         = "'there is already a record with a $R->sob_all_title of $dq".str_replace('"','',$value[0])."$dq'";
                        print "   alert($S);\n";
                        print "   runBeforeCancel();\n";
                        print "   return;\n";
                }
        }
}

buildValidation($formID);
$newwin = $_GET['newwin'];  //-- was this opened in a new window
$fly    = $_GET['fly'];     //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform

print "   parent.document.getElementById('beenedited').value = '0';\n";
print "   parent.document.forms[0].action = 'formupdate.php?x=1&r=$recordID&dir=$dir&fly=$fly&ses=$ses&f=$formID&newwin=$newwin" . addHistory() . "';\n";
print "   parent.document.forms[0].submit();\n";
print "}\n\n";
print "/* ]]> */ \n\n";
print "</script>";
print "<!-- End Of Form Functions -->\n\n";

print "</head>\n";
print "<body onload='checkDuplicate()'>\n";
print "<form>\n";
print "</form>\n";
print "</body>\n";
print "</html>\n";



function isDuplicate($session, $form, $recordID, $field, $value){

		$t          = nuRunQuery("SHOW COLUMNS FROM $form->sfo_table WHERE `field` = '$field'");
		$r          = db_fetch_object($t);

		$value      = str_replace("'","\\'",$value);

		if(substr($r->Type,0,4) ==  'char'){
			$len    = substr($r->Type,5, -1);  //-- get the "50" part of char(50)
			$value  = substr($value, 0, $len);
		}
		
        $s          = "SELECT count(*) FROM $form->sfo_table WHERE $field = '$value' AND $form->sfo_primary_key != '$recordID'";  //-- get matching records that aren't this one
        $t          = nuRunQuery($s);
        $r          = db_fetch_row($t);
        if($r[0] == 0){
            return false;  //-- no other duplicates
        }else{
			return true;   //-- there is a duplicate
		}
}


function buildValidation($pFormID){

        $formObjects           = array();
        $t                     = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$pFormID'");
        while($r               = db_fetch_array($t)){
                $blanks            = $r['sob_' . $r['sob_all_type'] . '_no_blanks'];
                $format            = $r['sob_' . $r['sob_all_type'] . '_format'];
                if($blanks     == '1'){ //---no blanks
                        $formObjects[$r['sob_all_name']] = '';
                }
                if($format     != ''){ //---format
                        $formObjects[$r['sob_all_name']] = $format;
                }
        }

        $mainCheckList = FormBlankObjectArrayList($pFormID);
        print "\n\n";

        $t                     = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$pFormID'");
        while($r               = db_fetch_array($t)){
                $oname             = $r['sob_all_name'];
                $otype             = $r['sob_all_type'];
                $blanks            = $r['sob_' . $otype . '_no_blanks'];
                $format            = $r['sob_' . $otype . '_format'];
                $title             = str_replace("'", '', $r['sob_all_title']);

//              if($blanks == '1' or $format != ''){ //---no blanks
                if($blanks == '1' ){ //---no blanks
                        print "   if(!checkField('$oname', '$format', '$title')){runBeforeCancel('$oname','$otype');return;};\n";
                }
        }

        $T                     = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$pFormID' AND sob_all_type = 'subform'");
        while($R               = db_fetch_object($T)){


                        /* QUICK FIX!!! Sometimes foreign key is blank... */
                if ($R->sob_subform_foreign_key)
                {
                        //$Tsf                            = nuRunQuery("SELECT count(*) FROM $R->sob_subform_table WHERE $R->sob_subform_foreign_key = '" . $_GET['r'] . "'");
                        //$Rsf                            = db_fetch_row($Tsf);
                        $subformRows                    = 0;//$Rsf[0];
                }
                else
                        $subformRows                    = 0;
                        /* END QUICK FIX */


                print "\n\n";
                print "   nameA                = Array();\n";
                print "   formatA              = Array();\n";
                print "   titleA               = Array();\n";
                print "   typeA                            = Array();\n\n";

                $I                     = 0;
                $t                     = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$R->zzsys_object_id'");
                while($r               = db_fetch_array($t)){

                        $oname             = $r['sob_all_name'];
                        $otype             = $r['sob_all_type'];
                        $blanks            = $r['sob_' . $otype . '_no_blanks'];
                        $format            = $r['sob_' . $otype . '_format'];
                        $title             = str_replace("'", '', $r['sob_all_title']);
                        $SFrows            = $subformRows + $R->sob_subform_blank_rows;
                        if($blanks == '1'){ //---no blanks

                                print "   nameA[$I]             = '$oname';\n";
                                print "   formatA[$I]           = '$format';\n";
                                print "   titleA[$I]            = '$title';\n";
                                print "   typeA[$I]             = '$otype'\n\n";
                                $I++;
                        }
                }
                print "\n";
                //print "   if(!checkSubform(nameA, formatA, titleA, '$R->sob_all_name',  '$SFrows')){alert(9);runBeforeCancel();return;};\n";

                //print "var returnVals = checkSubform(nameA, formatA, titleA, '$R->sob_all_name',  '$SFrows');\n";
                //print "if(returnVals != null){runBeforeCancel(returnVals[0],typeA[returnVals[1]]);return;}\n";

        }

        print "\n\n";

}



function FormBlankObjectArrayList($pFormID){   //--added by sc 27/10/09

        $formObjects           = array();
        $t                     = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$pFormID'");
        while($r               = db_fetch_array($t)){
                $blanks            = $r['sob_' . $r['sob_all_type'] . '_no_blanks'];
                $format            = $r['sob_' . $r['sob_all_type'] . '_format'];
                if($blanks     == '1'){ //---no blanks
                        $formObjects[$r['sob_all_name']] = '';
                }
                if($format     != ''){ //---format
                        $formObjects[$r['sob_all_name']] = $format;
                }
        }

        return $formObjects;

}

function SubFormObjectArrayList($pSubFormID, $pSubFormName){   //--added by sc 21/10/09

//--- make sure  subform id and name haven't been changed
        $t               = nuRunQuery("SELECT zzsys_object_id FROM zzsys_object WHERE zzsys_object_id = '$pSubFormID' AND  sob_all_name = '$pSubFormName'");
        $r               = db_fetch_object($t);
        return $r->zzsys_object_id;

}



?>
