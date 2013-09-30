<?php
/*
** File:           form.php
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
$GLOBALS['QueryList']            = array();
$GLOBALS['nuRunQuery']           = 0;
$GLOBALS['formValues'][]         = 'xxx';
$GLOBALS['deleteTT']             = array();
$GLOBALS['insertSV']             = array();

$dir                             = $_GET['dir'];
$ses                             = $_GET['ses'];
$f                               = $_GET['f'];
$r                               = $_GET['r'];
$c                               = (isset($_GET['c']) ? $_GET['c'] : '');
$delete                          = (isset($_GET['delete']) ? $_GET['delete'] : '');
$_GET['fly']                     = (isset($_GET['fly']) ? $_GET['fly'] : '');

if (strpos($dir,"..") !== false){die;}

require_once("../$dir/database.php");
require_once('common.php');

if ($f == "run")
        $actualFormID = db_fetch_object(nuRunQuery("SELECT sat_all_zzsys_form_id FROM zzsys_activity WHERE zzsys_activity_id = '$r'"))->sat_all_zzsys_form_id;
else
        $actualFormID = $f;

if(passwordNeeded($actualFormID)){
	if(!continueSession()){
		print "<a href='../$dir'>" . nuTranslate('You have been logged out') . "</a>";
		return;
	}
	if(!accessableForm()){
		print nuTranslate('You do not have access');
		return;
	}
}

$setup                           = $GLOBALS['nuSetup'];

if($f == 'index' AND $_SESSION['nu_access_level'] != 'globeadmin'){
	$inString                    = "'x'";
	$s                           = "SELECT zzsys_object_id FROM zzsys_object ";
	$s                          .= "INNER JOIN zzsys_access_level_object ON zzsys_object_id = sao_zzsys_object_id ";
	$s                          .= "INNER JOIN zzsys_access_level ON sao_zzsys_access_level_id = zzsys_access_level_id ";
	$s                          .= "WHERE sob_zzsys_form_id = 'index' ";
	$s                          .= "AND sal_name = '".$_SESSION['nu_access_level']."' ";
	$ttt                         = nuRunQuery($s);

	while($rrr                   = db_fetch_row($ttt)){
		$inString                = "$inString, '$rrr[0]'";
	}
	$inString                    = " AND zzsys_object_id IN($inString) ";
}

$runActivity                     = false;

if($f                            == 'run'){//---run a report, procedure or export
	$ttt                         = nuRunQuery("SELECT sat_all_zzsys_form_id, sat_all_description FROM zzsys_activity WHERE zzsys_activity_id = '$r'");
	$rrr                         = db_fetch_row($ttt);
	$f                           = $rrr[0];
	$runActivity                 = true;
}
$tempObjectTable                 = TT();
nuRunQuery("CREATE TABLE $tempObjectTable SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$f'");
nuRunQuery("ALTER TABLE $tempObjectTable ADD INDEX (sob_all_column_number)");
nuRunQuery("ALTER TABLE $tempObjectTable ADD INDEX (sob_all_order_number)");
nuRunQuery("ALTER TABLE $tempObjectTable ADD INDEX (sob_all_tab_number)");
nuRunQuery("ALTER TABLE $tempObjectTable ADD INDEX (sob_all_tab_title)");



$nuForm                          = new Form();
$nuForm->loadAfterConstruct($f, $r, $c, $delete, $runActivity, $dir, $ses, $tempObjectTable, $session);


function addJSFunction($pCode){

	global $nuForm;
	$nuForm->appendJSFunction($pCode);

}



//---get form tab details FROM zzsys_object

$s                               = "SELECT sob_all_tab_title FROM zzsys_object ";
$s                              .= "WHERE sob_zzsys_form_id = '$f' $inString ";
$s                              .= "GROUP BY sob_all_tab_title ";
$s                              .= "ORDER BY sob_all_tab_number";
$t                               = nuRunQuery($s);
while($r                         = db_fetch_row($t)){
	$nuForm->formTabs[]          = $r[0];
	$nuForm->formTabNames[$r[0]] = count($nuForm->formTabs);
}

$nuForm->access_level                         = $_SESSION['nu_access_level'];
$nuForm->session_id                           = $_SESSION['nu_session'];
$nuForm->zzsys_user_id                        = $_SESSION['nu_user_id'];
$nuForm->zzsys_user_group_name                = $_SESSION['nu_user_group'];
$nuForm->inString                             = $inString;

$nuForm->setSessionVariables();

$nuForm->execute();
nuRunQuery("DROP TABLE $nuForm->objectTableName");

class Form{

    public  $form                    = array();
    public  $setup                   = array();
    public  $formTabs                = array();
    public  $formTabNames            = array();
    public  $recordValues            = array();
    public  $arrayOfHashVariables    = array();       //---this array holds all the values from this record plus values that might be in a display or lookup.
    private $formObjects             = array();
    public  $textObjects             = array();
    public  $subformNames            = array();
    public  $subformTabs             = array();
    private $jsFunctions             = array();
    public  $inarrayFunctions        = array();
    public  $globalValue             = array();
    public  $customDirectory         = '';
    public  $session                 = '';            //---id that remains the same throughout login time
    public  $pageHeader              = '';
    public  $actionButtonsHTML       = '';            //---HTML that goes at the top of the form for buttons like Save,Clone etc.
    public  $CRLF                    = "\n";
    public  $TAB                     = '    ';
    public  $formID                  = '';            //---Primary Key of zzsys_form Table
    public  $recordID                = '';            //---Primary Key of displayed record
    private $subformID               = '';            //---Primary Key of current Subform record
    public  $formsessionID           = '';            //---Form Session ID (unique ID for this instance of this form)
    private $styleSheet              = '';
    private $startTime               = 0;
    private $tabHTML                 = '';
    private $logon                   = '';
    private $delete                  = '';
    public  $cloning                 = '';            //---Whether this will be a new record cloned from $formID's record
    public  $access_level            = '';
    public  $zzsys_user_id           = '';
    public  $zzsys_user_login_id     = '';
    public  $inString                = '';
    public  $objectTableName         = '';
    public  $runActivity             = false;

    function loadAfterConstruct($theFormID, $theRecordID, $clone, $delete, $runActivity, $dir, $ses, $tempObjectTable, $session){

		$this->access_level          = $_SESSION['nu_access_level'];
		$this->session_id            = $_SESSION['nu_session'];
		$this->zzsys_user_id         = $_SESSION['nu_user_id'];
		$this->zzsys_user_group_name = $_SESSION['nu_user_group'];
		

		$this->startTime             = microtime(true);
		$this->objectTableName       = $tempObjectTable;
		$this->customDirectory       = $dir;
		$this->session               = $ses;
		$this->formsessionID         = uniqid('1');
		nuRunQuery("DELETE FROM zzsys_variable WHERE sva_id = '$this->formsessionID'");
    	
		$this->formID                = $theFormID;                                //---Primary Key of zzsys_form Table
		$this->form                  = formFields($theFormID);
		$this->recordID              = $theRecordID;                              //---ID of displayed record (-1 means a new record)
        setnuVariable($this->formsessionID, nuDateAddDays(Today(),2), 'recordID', $this->recordID);
        setnuVariable($this->formsessionID, nuDateAddDays(Today(),2), 'id', $this->recordID);

		$this->cloning               = $clone;                                    //---Whether this will be a new record cloned from $formID's record
		$this->delete                = $delete;
		$this->setup                 = $GLOBALS['nuSetup'];
//----------create an array of hash variables that can be used in any "hashString" 
		if($this->form->sfo_report_selection != '1'){
			$T                              = nuRunQuery("SELECT `".$this->form->sfo_table."`.* FROM `".$this->form->sfo_table."` WHERE ".$this->form->sfo_primary_key." = '$this->recordID'");
			$this->recordValues             = db_fetch_array($T);
			$this->arrayOfHashVariables     = recordToHashArray($this->form->sfo_table, $this->form->sfo_primary_key, $this->recordID);//--values of this record
		}
		$this->arrayOfHashVariables['#recordID#']      = $theRecordID;                        //--this record's id
		$this->arrayOfHashVariables['#id#']            = $theRecordID;                        //--this record's id
		$this->arrayOfHashVariables['#dir#']           = $dir;                                //--starting directory
		$this->arrayOfHashVariables['#clone#']         = $clone;                              //--if it is a clone
		$this->arrayOfHashVariables['#formSessionID#'] = $this->formsessionID;                //--form session id
		$this->arrayOfHashVariables['#formID#']        = $this->formID;                       //--form id
		$this->arrayOfHashVariables['#sys_box_net#']   = $this->recordValues['sys_box_net'];  //--box.net widget id for this record
		$sVariables                                    = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
		$this->arrayOfHashVariables                    = joinHashArrays($this->arrayOfHashVariables, $sVariables);      //--join the arrays together
		$nuHashVariables                               = $this->arrayOfHashVariables;   //--added by sc 23-07-2009
//----------allow for custom code----------------------------------------------
        //--replace hash variables then run code
        $runCode                                   = replaceHashVariablesWithValues($this->arrayOfHashVariables, $this->form->sfo_custom_code_run_before_open);
		$GLOBALS['nuEvent'] = "(nuBuilder Before Open) of " . $this->form->sfo_name . " : ";
		eval($runCode);
        if($newRecordID!=''){
            $this->recordID = $newRecordID; 
        }
//-----------custom code end---------------------------------------------------
		$this->runActivity           = $runActivity;
        //----defaultJSfunctions needs $formTabs populated first
        $this->defaultJSfunctions();
        $this->createActionButtonsHTML();
    }

	private function pageHeader($tabList){

        $TAB                 = $this->TAB;
        $CRLF                = $this->CRLF;
		$nuMenu              = "<div id='selectedMenu'   style='visability:hidden' class='selected'></div>";
		$nuMenu             .= "<div id='unSelectedMenu' style='visability:hidden' class='unselected'></div>";
		$nuMenu             .= "<div style='position:absolute;margin:0px;' class='unselected nuMenu'>$CRLF";
		if(count($tabList) > 1){
		$nuMenu             .= "<ul style='padding:0px;margin:0px;' >$CRLF";
			for($i=0;$i<count($tabList);$i++){
				$nuMenu     .= "   <li id='nuMenu$i' onclick='showTab($i)' style='cursor:pointer;list-style-type:none;text-align:center;' >".$tabList[$i]."</li>$CRLF";
			}
			$nuMenu         .= "</ul>$CRLF";
		}
		$nuMenu             .= "</div>$CRLF";
        $this->pageHeader  = "$CRLF<!-- start menu-->$CRLF$nuMenu$CRLF<!-- end menu-->";
	}


    private function buildTab($tabNumber, $tabList){

        $TAB                = $this->TAB;
        $CRLF               = $this->CRLF;
        $subformString      = '';

        $tabString          = "$CRLF<!-- Tab for ".$tabList[$tabNumber]." -->$CRLF$CRLF";//--close main div

		//--create main div
        if($tabNumber       ==0){
            $v              = 'visible';
        }else{
            $v              = 'hidden';
        }

        $tabString         .= "<div id='MidDiv$tabNumber'  style='visibility:hidden;overflow:hidden;position:absolute;' class='nuForm selected'><br>$CRLF";

		$getColumns         = nuRunQuery("SELECT sob_all_column_number FROM $this->objectTableName WHERE sob_zzsys_form_id = '$this->formID' AND sob_all_tab_title = '".$tabList[$tabNumber]."' $this->inString GROUP BY sob_all_column_number ORDER BY sob_all_column_number");
		while($tc           = db_fetch_row($getColumns)){
			$tabColumns[]   = $tc[0];
		}

        $tabString         .= "$CRLF$TAB<table class='selected'>$CRLF$TAB$TAB<tr>$CRLF";
		for($c=0;$c<count($tabColumns);$c++){
	        $tabString     .= "$TAB$TAB$TAB<td class='selected'>$CRLF$TAB$TAB$TAB$TAB<table class='selected'><tr><td class='selected'></td></tr>$CRLF$TAB$TAB$CRLF";

	        //---create column objects
	        $t              = nuRunQuery("SELECT * FROM $this->objectTableName WHERE sob_zzsys_form_id = '$this->formID' AND sob_all_column_number = '".$tabColumns[$c]."' AND  sob_all_tab_title = '".$tabList[$tabNumber]."' $this->inString ORDER BY sob_all_column_number, sob_all_order_number");
	        while($object   = db_fetch_object($t)){
	            $this->buildObject($object);
	        }

	        for($i=0;$i<=count($this->formObjects);$i++){
	        	if($this->formObjects[$i]->objectType == 'subform'){
		        	$subformString .= $this->formObjects[$i]->objectHtml;
	        	}else{
		        	$tabString     .= $this->formObjects[$i]->objectHtml;
	        	}
			}

	        $tabString     .="$CRLF$TAB$TAB$TAB$TAB</table>$CRLF$TAB$TAB$TAB</td>$CRLF";
			unset ($this->formObjects);
		}
        $tabString         .= "$TAB$TAB</tr>$CRLF$TAB</table>$CRLF";
        $tabString         .= $subformString; //---put subforms outside tables
        $tabString         .= "</div>$CRLF";//--close middle div

        return $tabString;  //--html that displays tabs

    }

    public function createActionButtonsHTML(){


    	$dq         = '"';
		if($this->form->zzsys_form_id == 'index'){
			$s      =      "$this->CRLF<div id='logo' style='cursor:pointer;overflow:hidden;position:absolute;top:0px; left:0px;  width:992px;  height:70px'  >$this->CRLF";
			$s     .= "<img src='formimage.php?dir=$this->customDirectory&iid=" . $this->setup->set_index_image_path . "' onclick='SaveThis(0);closeAllDo();setTimeout(\"nuRefresh()\",1000);;'></div>$this->CRLF";
			$vis    = 'hidden';
		}else{
			$s      =      "$this->CRLF<div id='logo' style='cursor:pointer;overflow:hidden;position:absolute;top:0px; left:10px;  width:150px;  height:70px'  >$this->CRLF";
			$s     .= "</div>$this->CRLF";
			$vis    = 'visible';
		}
		$s       .= "$this->CRLF<div id='actionButtons' class='nuAction' style='position:absolute;visibility:$vis;'>$this->CRLF";
		if($this->runActivity){
			$s   .= $this->createActivityButtonHTML();
		}else{
			if($this->form->zzsys_form_id != 'index'){
		        if($this->delete == '1'){
					$deleteYes   = nuTranslate('Yes');
					$deleteNo    = nuTranslate('No');
					$sure        = nuTranslate('Are You Sure?');
					
					$s   .= "$sure <select name='del_ok' id='del_ok'><option selected value='0'>$deleteNo</option><option value='1'>$deleteYes</option></select>$this->CRLF";
		        }else{
					if($this->form->sfo_save_button    == '1' and !hideFormButton('save') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_save_button_display_condition)){
						$title = iif($this->form->sfo_save_title == '',nuTranslate('Save'),$this->form->sfo_save_title);
						$s    .= "<input type='button' id='nuActionSave' accesskey='s' class='actionButton' value='$title' onclick='SaveThis(0)'>$this->CRLF";
					}
					if($this->form->sfo_close_button    == '1' and !hideFormButton('close') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_close_button_display_condition)){
						$title = iif($this->form->sfo_close_title == '',nuTranslate('Save & Close'),$this->form->sfo_close_title);
						$s    .= "<input type='button' id='nuActionSaveNClose' accesskey='s' class='actionButton' value='$title' onclick='SaveThis(1)'>$this->CRLF";
					}
					if($this->form->sfo_clone_button   == '1' and $this->recordID <> '-1' and $this->cloning <> '1' and !hideFormButton('clone') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_clone_button_display_condition)){
						$title = iif($this->form->sfo_clone_title == '',nuTranslate('Clone'),$this->form->sfo_clone_title);
						$s    .= "<input type='button' id='nuActionClone' class='actionButton' value='$title' onclick='CloneThis()'>$this->CRLF";
					}
					$t       = nuRunQuery("SELECT * FROM zzsys_form_action WHERE sfa_zzsys_form_id = '".$this->form->zzsys_form_id."'");
					while($r = db_fetch_object($t)){
						if(displayCondition($this->arrayOfHashVariables, $r->sfa_button_display_condition)){
							$s   .= "<input type='button' id='nuCustomAction".str_replace(' ' , '' , $r->sfa_button_title)."' class='actionButton' value='$r->sfa_button_title' onclick='$r->sfa_button_javascript'>$this->CRLF";
						}
					}
		        }

				if($this->delete == '1' or ($this->form->sfo_delete_button  == '1' and $this->recordID <> '-1' and $this->cloning <> '1' and !hideFormButton('delete') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_delete_button_display_condition))){
					$title = iif($this->form->sfo_delete_title == '',nuTranslate('Delete'),$this->form->sfo_delete_title);
					$s    .= "<input type='button' id='nuActionDelete' class='actionButton' value='$title' onclick='DeleteThis()'>$this->CRLF";
				}
			}
		}
		$s       .= "</div>$this->CRLF";
		$this->actionButtonsHTML = $s;

	}




// begin added by SG
public function createActivityButtonHTML(){

		$t                       = nuRunQuery("SELECT * FROM zzsys_activity WHERE zzsys_activity_id = '$this->recordID'");
        $r                       = db_fetch_object($t);
        $dq                      = '"';
        $s                       = '';

//if report
	if($r->sat_all_type == 'report'){


		if ($r->sat_report_display_type == 0 || $r->sat_report_display_type == 2) { 
		
			//print	html	
			$action = "printIt($dq$r->sat_all_code$dq)";
			$s     .= $this->showActivityBtn('Print to Screen', $action);
			//email	html
			if ("" != $this->setup->set_email_default_from) {
				$from = $this->setup->set_email_default_from;
			} else {
				$from = "";
			}
			$action = "emailIt($dq$r->sat_all_code$dq,$dq$dq,$dq$from$dq,$dq$dq,$dq$dq,$dq$dq,true,".$dq."HTML"."$dq)";
			$s     .= $this->showActivityBtn('Email', $action);
		}
 
		if ($r->sat_report_display_type == 1 || $r->sat_report_display_type == 2) {

			//print pdf
			$action = "pdfIt($dq$r->sat_all_code$dq)";
			$s     .= $this->showActivityBtn('Print to PDF', $action);       	

			//PDF html	
			$action = "emailIt($dq$r->sat_all_code$dq,$dq$dq,$dq$from$dq,$dq$dq,$dq$dq,$dq$dq,true,".$dq."PDF"."$dq)";
			$s     .= $this->showActivityBtn('Email PDF', $action);
		}

        }//end if report

//if procedure
        if($r->sat_all_type == 'procedure'){

			$action = "runIt($dq$r->sat_all_code$dq)";
			$s     .= $this->showActivityBtn('Run', $action);       	
        }

//if export
        if($r->sat_all_type == 'export'){

			$action = "exportIt($dq$r->sat_all_code$dq)";
			$s     .= $this->showActivityBtn('Export', $action);       	
		}
        
	//return result (string)
	return $s;
}

private function showActivityBtn($value, $action) {

	$dq     = '"';
	$result = "<input type='button' id='nuActionButton".str_replace(' ' , '' , $value)."' class='actionButton' value='$value' onclick='$action'>$this->CRLF";
	return $result;

}


    private function displayJavaScript(){

        print "$this->CRLF$this->CRLF<!-- Form Functions -->$this->CRLF";
		print makeCSS();
        print "<script type='text/javascript'>$this->CRLF";
        print "/* <![CDATA[ */$this->CRLF";
        print "isEditScreen = true;\n";
        print "var nuLastTab = 0;\n";
		print setLangArray();
        for($i=0;$i<count($this->jsFunctions);$i++){
            print $this->jsFunctions[$i];
            print "$this->CRLF$this->CRLF";
        }
        print "/* ]]> */ $this->CRLF";
        print "</script>$this->CRLF<!-- End Of Form Functions -->$this->CRLF$this->CRLF";

    }

	public function setSessionVariables(){

        $C   = $this->CRLF;
        $s   =      "function customDirectory(){ $C";
        $s   .= "   return '$this->customDirectory';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function session_id(){ //-- id that remains the same until logout$C";
        $s   .= "   return '$this->session';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function form_session_id(){ //--just for this instance of this form$C";
        $s   .= "   return '$this->formsessionID';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function access_level(){ $C";
        $s   .= "   return '$this->access_level';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function zzsys_user_id(){ $C";
        $s   .= "   return '$this->zzsys_user_id';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function zzsys_user_group_name(){ $C";
        $s   .= "   return '$this->zzsys_user_group_name';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function sd(){ $C";
        $s   .= "   return '$this->customDirectory';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s    =  "function isNuSubform(){ $C";
        $s   .=  "   return false; $C";
        $s   .=  "}$C";
        $this->appendJSfunction($s);

		
        $s   =      "function web_root_path(){ $C";
        $s   .= "   return '".$this->setup->set_web_root_path."';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

		
        $s    = "function closeAllDo() { $C";
        $s   .= "        nuEraseCookie('nuC'); $C";
        $s   .= "        setTimeout(\"nuCreateCookie('nuC',1,1)\",3000); $C";
        $s   .= "} $C$C";

        $this->appendJSfunction($s);
		
	}

    private function defaultJSfunctions(){

        $C   = $this->CRLF;
		if($this->form->sfo_javascript != ''){
	        $this->appendJSfunction("$C//---- start of custom javascript ----$C$C".$this->form->sfo_javascript."$C$C//---- end of custom javascript ----");
		}
		$this->checkBlanks();

        $s   =  "var nuSFRow  = new String();  	//---row prefix of last subform row that received focus$C$C";
        $s   .= "function nuSetRow(pRow){       //---set  prefix of last subform row that received focus$C";
        $s   .= "      SFrowColor(pRow, nuGetRow()); 		//--- Change background of subform row $C";
        $s   .= "      nuSFRow = pRow; $C";
        $s   .= "}$C";
        $s   .= "function nuGetRow(){       	//---get  prefix of last subform row that received focus$C";
        $s   .= "      return nuSFRow; $C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function nuRefresh(){//---refresh index page$C";
        $s   .= "      window.onbeforeunload = null; $C";
        $s   .= "      window.onunload = null; $C";
        $s   .= "      history.go(); $C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function sfVisibile(pTabNo){//---show subforms$C";
        $s   .= "      var subformCount = document.getElementById('theform').TheSubforms.value; $C";
        $s   .= "      var subformName  = ''; $C";
        $s   .= "      for(ii=0;ii<subformCount;ii++){ $C";
        $s   .= "          if(document.getElementById('theform')['SubformNumber'+ii].accept == pTabNo){ $C";
        $s   .= "              subformName = document.getElementById('theform')['SubformNumber'+ii].value$C";
        $s   .= "              document.getElementById('sf_title'+subformName).style.visibility = 'visible';$C";
        $s   .= "              document.getElementById(subformName).style.visibility = 'visible';$C";
        $s   .= "          }$C";
        $s   .= "      }$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s    = "function SFrowColor(cRow,pRow){ $C";
        $s   .= "   if (pRow.length > 4){ $C";  																// Check if previous SubForm Row was Selected
        $s   .= "      var pSubform = pRow.substring(0,pRow.length-4); $C"; 									// Get SubForm name from Row
        $s   .= "      var pRid = document.getElementById('lastRow_'  +  pSubform).value; $C"; 					// Retrieve the ID of the previously selected row
        $s   .= "      var pCol = document.getElementById('lastColor_' + pSubform).value; $C"; 					// Retrieve the Color of the previously selected row
        $s   .= "      if (pRid != ''){ $C";
        $s   .= "	      document.getElementById(pRid).style.backgroundColor = pCol; $C";						// Reset previous Row Color
        $s   .= "         } $C";
        $s   .= "      } $C";
        $s   .= "   if (cRow.length > 4){ $C";  																// Check if current SubForm Row is Selected
        $s   .= "      var cSubform = cRow.substring(0,cRow.length-4); $C"; 									// Get SubForm name from Row
        $s   .= "      var sCol = document.getElementById('rowColor_' + cSubform).value.replace(/\s+/g,''); $C";// Get the value of the Select Color
        $s   .= "      var cRid    = 'rowdiv_' + cRow; $C"; 													// current row ID
        $s   .= "      var cCol    = document.getElementById(cRid).style.backgroundColor; $C"; 					// current Color
        $s   .= "      if (sCol != ''){ $C";  																	// Only continue if a Select Color is defined
        $s   .= "         document.getElementById('lastRow_'  + cSubform).value = cRid; $C"; 					// Save ID
        $s   .= "         document.getElementById('lastColor_' + cSubform).value = cCol; $C";					// Save Current Color
        $s   .= "         document.getElementById(cRid).style.backgroundColor = sCol; $C"; 						// Change Color
        $s   .= "      } $C";
        $s   .= "   } $C";
        $s   .= "} $C";
        $this->appendJSfunction($s);

        $s   =      "function sfInvisibile(pTabNo){//---show subforms$C";
        $s   .= "      var subformCount = document.getElementById('theform').TheSubforms.value; $C";
        $s   .= "      var subformName  = ''; $C";
        $s   .= "      for(ii=0;ii<subformCount;ii++){ $C";
        $s   .= "          if(document.getElementById('theform')['SubformNumber'+ii].accept == pTabNo){ $C";
        $s   .= "              subformName = document.getElementById('theform')['SubformNumber'+ii].value$C";
        $s   .= "              document.getElementById('sf_title'+subformName).style.visibility = 'hidden';$C";
        $s   .= "              document.getElementById(subformName).style.visibility = 'hidden';$C";
        $s   .= "          }$C";
        $s   .= "      }$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        if($this->form->sfo_access_without_login != '1'){
            $s   =      "self.setInterval('checknuC()', 1000); $C$C";
        }

		if($this->form->zzsys_form_id == 'index'){$s = '';}
        $s   .= "function checknuC(){ $C";
        $s   .= "   if(nuSingleWindow()){return;} $C";
        $s   .= "   if(nuReadCookie('nuC') == null){ $C";
		if($this->form->zzsys_form_id == 'index'){
            $s   .= "      pop = window.open('formlogin.php', '_parent');$C";
        }else{
            $s   .= "      pop = window.open('', '_parent');$C";
            $s   .= "      pop.close();$C";
        }
        $s   .= "   }$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function MIN(pthis){//---mouse over menu$C";
        $s   .= "      document.getElementById(pthis.id).style.color='" . $this->setup->set_hover_color . "';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function MOUT(pthis){//---mouse out menu$C";
        $s   .= "   document.getElementById(pthis.id).style.color='';$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function getCal(theID){//---open calendar$C";

		$s	.= "   calendarBuild(theID); ";	

        $s   .= "}$C";
        $this->appendJSfunction($s);

        $s   =      "function getImage(pID){ $C"; 
        $s   = $s . "   return 'formimage.php?dir='+customDirectory()+'&iid='+pID; $C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function reformat(pthis){ $C";
        $s   .= "   if(aType[txtFormat[pthis.id]]=='date'){;$C";
        $s   .= "      reformat(pthis);$C";
        $s   .= "   }$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);


        $s   =      "function nuSaveThis(pclose){//---save record$C";
        $s   .= "   SaveThis(pclose); $C";
        $s   .= "//===================================================$C";
        $s   .= "// 0  = save and reload page$C";
        $s   .= "// 1  = close page after save$C";
        $s   .= "// 2  =  save and reload page with a blank record$C";
        $s   .= "//===================================================$C";
        $s   .= "}$C";
        $this->appendJSfunction($s);
		
		
        $s   =      "function SaveThis(pclose){//---save record$C";
        $s   .= "   document.getElementById('theform').close_after_save.value = pclose;$C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
        $s   .= "   if(window.nuBeforeSave){;$C";
        $s   .= "      if(!nuBeforeSave()){;$C";
        $s   .= "         return;$C";
        $s   .= "      };$C";
        $s   .= "   };$C";

		$newwin = $_GET['newwin'];  //-- was this opened in a new window
		$fly  = $_GET['fly'];  //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform
        if($this->cloning == '1'){
	        $s   .= "   document.getElementById('nuFrame').src = 'formduplicate.php?x=1&r=-1&dir=$this->customDirectory&fly=$fly&ses=$this->session&f=$this->formID&newwin=$newwin&form_ses=$this->formsessionID" . addHistory() . "';$C";
        }else{
 	        $s   .= "   document.getElementById('nuFrame').src = 'formduplicate.php?x=1&r=$this->recordID&dir=$this->customDirectory&fly=$fly&ses=$this->session&f=$this->formID&newwin=$newwin&form_ses=$this->formsessionID" . addHistory() . "';$C";
        }
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function CloneThis(pthis){//---save record$C";
        $s   .= "   if(window.nuBeforeClone){;$C";
        $s   .= "      if(!nuBeforeClone()){;$C";
        $s   .= "         return;$C";
        $s   .= "      };$C";
        $s   .= "   };$C";
        if($this->cloning == '1'){
        	$recordID = '-1';
		}else{
        	$recordID = $this->recordID;
		}
        $s   .= "   document.forms[0].action = 'form.php?x=1&c=1&r=$recordID&dir=$this->customDirectory&ses=$this->session&f=$this->formID&newwin=$newwin" . addHistory() . "';$C";
        $s   .= "   document.forms[0].submit();$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function CheckMenu(pItem){//---load form$C";
        $s   .= "   if(window.nuClickMenu){;$C";
        $s   .= "      if(!nuClickMenu(pItem)){;$C";
        $s   .= "         return;$C";
        $s   .= "      };$C";
        $s   .= "   };$C";
        $s   .= "   showTab(pItem);$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s    = "function nuSystemForm(){//---part of nuBuilder structure $C";
        $s   .= "   return '1' == '{$this->form->sys_setup}' && 'index' != '{$this->form->zzsys_form_id}';$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);


        $s    =  "function nuArrangeObjects(){//---arrange object that won't sit in a table $C";
		$aot  = nuRunQuery("SELECT * FROM $this->objectTableName  ORDER BY sob_all_tab_number, sob_all_column_number, sob_all_order_number"); 
		while($aor = db_fetch_object($aot)){
			if($aor->sob_all_top != '' and $aor->sob_all_left != ''){
				$s   .= "    nuMoveObject('$aor->sob_all_name', '$aor->sob_all_top', '$aor->sob_all_left') $C";
			}
		}
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function LoadThis(){//---load form$C";
		if($_GET['tab'] == ''){
			$tab = '0';
		}else{
			$tab = $_GET['tab'];
		}
		$s   .= "   showTab($tab);$C";
		$s   .= "   nuArrangeObjects();$C";
        $s   .= "   if(window.nuLoadThis){;$C";
        $s   .= "      nuLoadThis();$C";
        $s   .= "   };$C";
//--start cursor
	$t   = nuRunQuery("SELECT sob_all_name FROM zzsys_object WHERE sob_all_start_cursor = '1' AND sob_zzsys_form_id = '" . $this->form->zzsys_form_id . "'");
	$r   = db_fetch_object($t);
	if($r->sob_all_name != ''){
		$s   .= "   document.forms[0]['$r->sob_all_name'].focus();$C";
	}
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function DeleteThis(pthis){//---delete record$C";
        $s   .= "   if(window.nuBeforeDelete){;$C";
        $s   .= "      if(!nuBeforeDelete()){;$C";
        $s   .= "         return;$C";
        $s   .= "      };$C";
        $s   .= "   };$C";

		$fly  = $_GET['fly'];  //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform
		$newwin = $_GET['newwin'];

        if($this->delete == '1'){
	        $s   .= "   document.forms[0].action = 'formdelete.php?x=1&r=$recordID&dir=$this->customDirectory&fly=$fly&ses=$this->session&f=$this->formID&newwin=$newwin" . addHistory() . "';$C";
        }else{
	        $s   .= "   document.forms[0].action = 'form.php?x=1&delete=1&r=$recordID&dir=$this->customDirectory&fly=$fly&ses=$this->session&f=$this->formID&newwin=$newwin" . addHistory() . "';$C";
        }
        $s   .= "   document.forms[0].submit();$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function hoverMenu(){ $C";
        $s   .= "   return 'green';$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function selectedMenu(){ $C";
        $s   .= "   return 'C0C0C0';$C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

//Added by SG
   	$s    =      "function emailIt(pReportID,pTo,pReplyto,pSubject,pMessage,pFilename,pResponse,pType){			$C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
    $s   .= "   nuEraseCookie('emailREPORT');                                                                           $C";
// =========== CHECK SMTP Settings exist in zzsys_setup Table	
	if (!empty($this->setup->set_smtp_from_address)) {
		$s   .= "   pFrom = '{$this->setup->set_smtp_from_address}';													$C";
		$s   .= "  	emailFormBuild(pReportID,pTo,pReplyto,pSubject,pMessage,pFilename,pResponse,pType,pFrom);	$C";
	} else {
		$s   .= "  	emailNotSet();			$C";
	}
// =========== END CHECK SMTP Settings exist in zzsys_setup Table	
	$s   .= "}                                                                                                          $C";
        $this->appendJSfunction($s);

// BEGIN - 2009/05/29 - Michael
	$php_url = getPHPurl();
	$s = <<<EOJS
			function emailSendIt(pReportID, pTo, pReplyto, pSubject, pMessage, pFilename, pAlert, pType, pResponse, pFrom)
			{
					// Make sure the error message is hidden.
				document.getElementById("error_row").style.display = 'none';
					// Erase the cookie and make sure these is an email address.
				nuEraseCookie("emailREPORT");
				if (pTo == '' || pTo == null)
					alert("Please provide an email address");
				else
				{
						// Generate the URL to email the report.
						// NOTE: The subject, message and filename need to be escaped
						//       otherwise everything after the first '&' in those values
						//       will be dropped due to the way GET variables are interpreted.
					var report_url	= "{$php_url}";
					var url 	= report_url+"reportemail.php" +
									"?x=1" +
									"&dir={$this->customDirectory}" +
									"&ses={$this->session}" +
									"&form_ses={$this->formsessionID}" +
									"&r=" + pReportID +
									"&to=" + pTo +
									"&replyto=" + pReplyto +
									"&from=" + pFrom +
									"&subject=" + escape(pSubject) +
									"&message=" + escape(pMessage) +
									"&filename=" + escape(pFilename) +
									"&report_url=" + report_url +
									"&reporttype=" + pType +
									"&receipt=" + pResponse;
						// Run that URL.
					nuMailJax(url);
						// Check if we need to be alerted about whether the email was
						// successfully sent out.
					if (pAlert == true || pAlert == 'true')
						startEmailTimeOut();
				} // else
			} // func
EOJS;
	$this->appendJSfunction($s);


	$s = <<<EOJS
			function emailSendResponse()
			{
					// Get the cookie and delete it.
				cookieValue = nuReadCookie("emailREPORT");
				nuEraseCookie("emailREPORT");
					// Check the cookie value.
				switch (cookieValue)
				{
					// The cookie has this value if everything was successful.
				case "{$this->formsessionID}":
					emailSuccess();
					return true;
					// The report could not be generated...
					// NOTE: This case won't run if there was an error in the report
					//       itself, only if the report URL could not be read from or
					//       the report document could not be saved to the filesystem.
				case "report_error":
					emailFailure("There was an error generating the report");
					return false;
					// The email was not sent successfully...
					// NOTE: This will mostly like only occur if the report could not
					//       be attached or there is no SMTP server listening on port 25.
					//       Postfix will just add the message to a queue before it trys
					//       to send the message, so invalid hostnames and users will
					//       not trigger this error.
				case "email_error":
					emailFailure("There was an error sending the email");
					return false;
					// This case will run if for some reason reportemail.php died
					// before sendResponse() was called. In other words, no cookie.
				default:
					emailFailure("There was an error sending the email");
					return false;
				} // switch
			} // function
EOJS;
        $this->appendJSfunction($s);

		
        $s   =      "function pdfIt(pReportID){ $C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
        $s   .= "   var ExtraParameters = '';$C";
        $s   .= "   for(Ano = 0 ; Ano < pdfIt.arguments.length ; Ano++){;$C";
        $s   .= "      ExtraParameters  = ExtraParameters + '&printIt' + Ano + '=' + pdfIt.arguments[Ano];$C";
        $s   .= "   };$C";
        $s   .= "   var url='" . $this->setup->set_php_url  . "runpdf.php?x=1&dir=$this->customDirectory&ses=$this->session&form_ses=$this->formsessionID&r='+pReportID+ExtraParameters; $C";
        $s   .= "   window.open (url,'_blank','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes'); $C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function printIt(pReportID){ $C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
        $s   .= "   var ExtraParameters = '';$C";
        $s   .= "   for(Ano = 0 ; Ano < printIt.arguments.length ; Ano++){;$C";
        $s   .= "      ExtraParameters  = ExtraParameters + '&printIt' + Ano + '=' + printIt.arguments[Ano];$C";
        $s   .= "   };$C";
        $s   .= "   var url='" . $this->setup->set_php_url  . "runreport.php?x=1&dir=$this->customDirectory&ses=$this->session&form_ses=$this->formsessionID&r='+pReportID+ExtraParameters; $C";
        $s   .= "   window.open (url,'_blank','toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=yes'); $C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function exportIt(pReportID){ $C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
        $s   .= "   var ExtraParameters = '';$C";
        $s   .= "   for(Ano = 0 ; Ano < exportIt.arguments.length ; Ano++){;$C";
        $s   .= "      ExtraParameters  = ExtraParameters + '&exportIt' + Ano + '=' + exportIt.arguments[Ano];$C";
        $s   .= "   };$C";
        $s   .= "   var url='" . $this->setup->set_php_url  . "runexport.php?x=1&dir=$this->customDirectory&ses=$this->session&form_ses=$this->formsessionID&r='+pReportID+ExtraParameters; $C";
        $s   .= "   window.open (url,'_blank','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes'); $C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =  "function runIt(pReportID){ $C";
        $s   .= "   var vMessage = '';$C";
        $s   .= "   vMessage     = noblanks();$C";
        $s   .= "   if(vMessage != ''){;$C";
        $s   .= "      alert(vMessage);$C";
        $s   .= "      return;$C";
        $s   .= "   }$C";
        $s   .= "   var ExtraParameters = '';$C";
        $s   .= "   for(Ano = 0 ; Ano < runIt.arguments.length ; Ano++){;$C";
        $s   .= "      ExtraParameters  = ExtraParameters + '&runIt' + Ano + '=' + runIt.arguments[Ano];$C";
        $s   .= "   };$C";
        $s   .= "   var url             = '" . $this->setup->set_php_url  . "runprocedure.php?x=1&dir=$this->customDirectory&ses=$this->session&form_ses=$this->formsessionID&r='+pReportID+ExtraParameters; $C";
        $s   .= "   window.open (url,'_blank','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes'); $C";
        $s   .= "};$C";
        $this->appendJSfunction($s);

        $s   =      "function edit_lookup(pFormID, pLookup){ $C";
		$s   = $s . "   if(nuControlKey && document.getElementById(pLookup).value != ''){ $C";
        $s   = $s . "      openForm(pFormID, document.getElementById(pLookup).value);$C";
        $s   = $s . "   }$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

		
		if($this->setup->set_single_window == '1' and $this->formID == 'index'){  //-- dont question leaving page
			$s= "function CheckIfSaved(e) { $C";
			$s.="try{if(parent.opener.document.forms[0].name=='thebrowse'){parent.opener.nuFirstClick=true;}}catch(err){}";
			$s.="} $C";
		}else{
			$s= "function CheckIfSaved(e) { $C";
			$s.="try{if(parent.opener.document.forms[0].name=='thebrowse'){parent.opener.nuFirstClick=true;}}catch(err){}";
			$s.="	//taken from the examle at: https://developer.mozilla.org/en/DOM/window.onbeforeunload $C";
			$s.="	var e = e || window.event; $C";
			if(!$this->runActivity){
				if($this->formID == 'index'){
			$s.="	var str = 'Are you sure?'; $C";
			$s.="	//alert('confirm should occur: '+str); $C";
			$s.="	// For IE and Firefox $C";
			$s.="	if (e) { $C";
			$s.="		e.returnValue = str; $C";
			$s.="	} $C";
			$s.="	// For Safari $C";
			$s.="	return str; $C";
				}else{
			$s.="	var str = 'This record has NOT been saved.. (Click CANCEL to save this record)'; $C";
			$s.="	//alert('been edited: ' + document.getElementById('beenedited').value); $C";
			$s.="	if(document.getElementById('beenedited').value=='1'){ $C";
			$s.="		//alert('confirm should occur: '+str); $C";
			$s.="		// For IE and Firefox $C";
			$s.="		if (e) { $C";
			$s.="			e.returnValue = str; $C";
			$s.="		} $C";
			$s.="		// For Safari $C";
			$s.="		return str; $C";
			$s.="	} $C";
				}
			}
			$s.="} $C";
		}
        $this->appendJSfunction($s);
		
        $s   =      "function untick(pBox, pThis){ $C";
        $s   .= "      if (arguments.length == 1){\n";
        $s   .= "         try{document.getElementById('row'+pBox).checked = false;}catch(err){}\n";
        $s   .= "      }else{\n";
        $s   .= "         var bLen       = pBox.length;                         //-- length of string needed\n";
        $s   .= "         var subfName   = pThis.id.substr(pBox, bLen - 4);     //-- name of subform \n";
        $s   .= "         var bStart     = pThis.id.indexOf(subfName);          //-- start of new checkbox name\n";
        $s   .= "         var newBox     = pThis.id.substr(bStart, bLen);       //-- new checkbox name\n";
        $s   .= "         try{document.getElementById('row'+newBox).checked = false;}catch(err){}\n";
        $s   .= "      };\n";
        $s   .= "};$C";
        $this->appendJSfunction($s);

		$s    = setWindowNavigation($this->setup->set_single_window);
        $this->appendJSfunction($s);  //-- add js for window nav.

    }
    
	private function checkBlanks(){
		$dq                        = '"';
		$a                         = array();
        $t                         = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$this->formID' ORDER BY IF(sob_all_type = 'subform', 1,0)");
        while($r                   = db_fetch_array($t)){
       		$fieldName             = $r['sob_all_name'];
       		$fieldTitle            = $r['sob_all_title'];
			$r['sob_'.$r['sob_all_type'].'_no_blanks'] = (isset($r['sob_'.$r['sob_all_type'].'_no_blanks']) ? $r['sob_'.$r['sob_all_type'].'_no_blanks'] : '');
			$r['sob_'.$r['sob_all_type'].'_no_duplicates'] = (isset($r['sob_'.$r['sob_all_type'].'_no_duplicates']) ? $r['sob_'.$r['sob_all_type'].'_no_duplicates'] : '');
       		if($r['sob_'.$r['sob_all_type'].'_no_blanks'] == '1' OR $r['sob_'.$r['sob_all_type'].'_no_duplicates'] == '1'){
				$a[]           = "   if(document.getElementById('$fieldName').value == ''){runBeforeCancel('$fieldName','{$r['sob_all_type']}');return $dq'$fieldTitle' cannot be left blank$dq;}$this->CRLF";
       		}
        	if($r['sob_all_type']  == 'subform'){
				$t1ID                     = $r['zzsys_object_id'];
		        $t1                       = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '$t1ID'");
		        while($r1                 = db_fetch_array($t1)){

	        		if($r1['sob_'.$r1['sob_all_type'].'_no_blanks'] == '1'){
		        		$fieldName1   = $r1['sob_all_name'];
		        		$fieldTitle1  = $r1['sob_all_title'];
		        		$a[]          = "                                                                                                             $this->CRLF";
		        		$a[]          = "   for(i = 0 ; i < document.getElementById('rows$fieldName').value ; i++){                                   $this->CRLF";
		        		$a[]          = "      rno           = '000'+i;                                                                               $this->CRLF";
		        		$a[]          = "      rno           = rno.substr(rno.length-4);                                                              $this->CRLF";
		        		$a[]          = "      checkBox      = 'row$fieldName'+rno;                                                                   $this->CRLF";
		        		$a[]          = "      checkField    = '$fieldName'+rno+'$fieldName1';                                                        $this->CRLF";
		        		$a[]          = "      if(document.getElementById(checkBox)){  //-- has checkbox                                              $this->CRLF";
		        		$a[]          = "         if(!document.getElementById(checkBox).checked  && document.getElementById(checkField).value == ''){ $this->CRLF";
		        		$a[]          = "            runBeforeCancel(checkField, '{$r1['sob_all_type']}');                                            $this->CRLF";
		        		$a[]          = "            return $dq'$fieldTitle1' on line $dq+String(i-0+1)+$dq cannot be left blank$dq;                  $this->CRLF";
		        		$a[]          = "         }                                                                                                   $this->CRLF";
		        		$a[]          = "      }                                                                                                      $this->CRLF";
		        		$a[]          = "   }                                                                                                         $this->CRLF$this->CRLF";
					}
		        }
        	}
        }

        $s                    =      "$this->CRLF"."function noblanks(){     $this->CRLF";
        if(count($a)>0){
	   		$s                    .= "   var rno          = '' $this->CRLF";
	   		$s                    .= "   var checkBox     = '' $this->CRLF";
	   		$s                    .= "   var checkField   = '' $this->CRLF$this->CRLF";
	        for($i = 0 ; $i < count($a) ; $i++){
		        $s                .= $a[$i];
	        }
        }
        $s                    .= "   return '';            $this->CRLF";
        $s                    .= "}                        $this->CRLF";

        $this->appendJSfunction($s);
	}

    private function appendshowTabfunction(){

        $C   = $this->CRLF;
        $s   =      "function showTab(ptabno){//---change visible tab$C";
        if(count($this->formTabs)> 1){ // only have tabs if there is more than one

	        $s   .= "   nuLastTab = ptabno;$C";
	        $s   .= "   var menuitem$C";
	        $s   .= "   for (i=0;i<".count($this->formTabs).";i++){ $C";
	        $s   .= "      document.getElementById('MidDiv'+i).style.visibility = 'hidden'; $C";
	        $s   .= "      $('#nuMenu'+i).css('background-color', $('#unSelectedMenu').css('background-color')); $C";
	        $s   .= "      $('#nuMenu'+i).css('color', $('#unSelectedMenu').css('color')); $C";
	        $s   .= "   }$C";
	        $s   .= "   document.getElementById('MidDiv'+ptabno).style.visibility = 'visible'; $C";
	        $s   .= "   $('#nuMenu'+ptabno).css('background-color', $('#MidDiv0').css('background-color')); $C";
	        $s   .= "   $('#nuMenu'+ptabno).css('color', $('#MidDiv0').css('color')); $C";
        }else{

	        $s   .= "   document.getElementById('MidDiv0').style.visibility = 'visible';$C";
        	
        }
        $s   .= "}$C";

        $this->appendJSfunction($s);
    }

    public function appendJSfunction($pValue){
        $this->jsFunctions[]=$pValue;
    }

    private function displayHeader(){

        global $dir;
        print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">$this->CRLF";
        print "<html>$this->CRLF";
        print "<head>$this->CRLF";
        $forceRefresh = uniqid('1');
        print "<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>$this->CRLF";
        print "<title>".$this->form->sfo_title."</title>$this->CRLF";
        jsinclude('jquery.js');
        jsinclude('jquery-ui/jquery-ui-1.8.16.custom.min.js');
        jsinclude('common.js');
        jsinclude('nuCalendar.js');
        jsinclude('nuEmailForm.js');
        cssinclude('css/core.css');
        
        if ($this->access_level == 'globeadmin') {
            cssinclude('css/nueditor.css');
            jsinclude('js/nueditor.js');
        }
        
        //-- the following loop is used to add extra javascript libraries  - like jquery.js
        $nuFileT = nuRunQuery("SELECT sli_option FROM zzsys_list WHERE sli_name = 'javascriptFile'");
        while($nuFileR = db_fetch_object($nuFileT)){
            jsinclude($nuFileR->sli_option);
        }

        $this->displayJavaScript();

        $stylesheetLocation = "../$this->customDirectory/style.css";
        print "</head>$this->CRLF";
        if($this->form->zzsys_form_id == 'index'){
            $dq                       = '"';
            print "<body onload='nuCreateCookie(\"nuC\",1,1);LoadThis()' onunload=$dq nuEraseCookie('nuC');$dq onkeydown='keyDownEvent(event)' onkeyup='keyUpEvent(event)' >$this->CRLF";
        }else{
            print "<body onload='LoadThis()' onkeydown='keyDownEvent(event)' onkeyup='keyUpEvent(event)' >$this->CRLF";
        }
        print "<script type='text/javascript'>window.onbeforeunload = function(e){CheckIfSaved(e);};</script>$this->CRLF";
        print "<form name='theform' id='theform' action='' method='post' enctype='multipart/form-data'>$this->CRLF";
    }

    private function displayFooter(){


		$deleteT       =   implode(',', $GLOBALS['deleteTT']);
		$insertS       =   implode(',', $GLOBALS['insertSV']);
		if($insertS != ''){
			nuRunQuery("INSERT INTO zzsys_variable (zzsys_variable_id, sva_id, sva_expiry_date, sva_session_id, sva_name, sva_value, sys_added)  VALUES $insertS;");
		}
		nuRunQuery("DROP TABLE IF EXISTS $deleteT;");

	
	
		$this->hiddenFields();
        print  "$this->TAB<div><input type='hidden' name='TheSubforms' value='".count($this->subformNames)."'>$this->CRLF";
    	for($i = 0 ; $i < count($this->subformNames) ; $i++){
    		$sfno=$i+1;
	        print  "$this->TAB<input type='hidden' name='SubformNumber$i' value='".$this->subformNames[$i]."' accept='".$this->subformTabs[$i]."'>$this->CRLF";
		}
        print  "</div>$this->CRLF";
		print $this->actionButtonsHTML;
		print breadCrumbHTML($this->setup->set_single_window, $this->form->sfo_title);
		print $this->pageHeader;
		print $this->formValuesStaus();
        print  "$this->CRLF</form>$this->CRLF</body>$this->CRLF</html>$this->CRLF$this->CRLF";
        $build = microtime(true)- $this->startTime;
        print  "<!--built in $build seconds, " . $GLOBALS['nuRunQuery'] . " queries, thread id ".db_thread_id().", local time ".date("r")."-->";

    }

    
    
    private function formValuesStaus(){

	    $s = "\n\n<div>";
	    
	    for($i = 0 ; $i < count($GLOBALS['formValues']) ; $i++){
		$s .= "<input type='hidden' name='z___" . $GLOBALS['formValues'][$i] . "' id='z___" . $GLOBALS['formValues'][$i] . "' value='1'>\n";
	    }
	    return $s.'</div>';
    }
    private function displayBody(){

		$CRLF   = $this->CRLF;
		$dbc    = " ondblclick=\"nuControlKey=true;openBrowse('object', '$this->formID', '', '$this->session', '')\"";

        print "$CRLF<div id='BorderDivB'   class='nuStatus unselected' style='position:absolute;'>$CRLF$CRLF";
        print "<table cellspacing='0px' cellpadding='0px' style='width:100%;'>$CRLF<tr>$CRLF";
		
		if($GLOBALS['nuSetup']->set_version == ''){
			$nuVersion = "";
		}else{
			$nuVersion = "title='" . $GLOBALS['nuSetup']->set_version . "' ";
		}
		
		if($this->zzsys_user_id == 'globeadmin' and $this->form->sys_setup != '1'){
			print "<td class='unselected' style='width:30%;text-align:left'><span id='pagetitle' ondblclick=\"nuControlKey=true;openForm('form', '$this->formID')\">"                               . $this->form->sfo_title  .                              "</span></td>$CRLF";
			print "<td class='unselected' style='width:30%;text-align:center'  ><span id='loggedin' $nuVersion ondblclick=\"nuControlKey=true;openBrowse('object', '$this->formID', '', '$this->session', '')\">" . $this->setup->set_title . " :: Powered by nuBuilder" .    "</span></td>$CRLF";
		}else{
			print "<td class='unselected' style='width:30%;text-align:left'><span id='pagetitle'>".$this->form->sfo_title."</span></td>$this->CRLF";
			print "<td class='unselected' style='width:30%;text-align:center'  ><span id='loggedin' $nuVersion>".$this->setup->set_title." :: Powered by nuBuilder</span></td>$CRLF";
		}
		$t = nuRunQuery("SELECT * FROM zzsys_user WHERE zzsys_user_id = '$this->zzsys_user_id'");
		$r = db_fetch_object($t);
        print "<td class='unselected' style='text-align:right;border-style:none;width:30%'>";
		if($r->sus_login_name == ''){$r->sus_login_name = 'globeadmin';}
		
		if($this->form->sfo_help != ''){
			$help = "title='help' onclick=\"openHelp('".$this->form->zzsys_form_id."')\"";
			print "<span $help >Logged in as $r->sus_login_name&nbsp;|&nbsp;" . nuTranslate('Help') . "</span>";
		}else{
			$help = "";
			print "<span $help >($r->sus_login_name)&nbsp;</span>";
		}
		if($_GET['f'] == 'index'){
			$closeall = " onclick='closeAllDo()'";
			$logout   = " onclick=\"closeAllDo();window.onbeforeunload = null;window.onunload = null;window.open('formlogout.php?x=1&dir=" . $_GET['dir'] . "','_self')\"";
			print "\n&nbsp;&nbsp;|&nbsp;&nbsp;<span class='unselected' style='cursor:pointer;' $logout >" . nuTranslate('Logout') . "&nbsp;</span>";
		}
        print "</td>$CRLF</tr>$CRLF</table>$CRLF$CRLF";

		
		//--login
        print "</div>$CRLF";
        print  $this->tabHTML;
    }

    private function hiddenFields(){
        print  "$this->CRLF<div>";
        print  "$this->TAB<input type='hidden' name='EMAIL_ADDRESS'          id='EMAIL_ADDRESS'          value=''>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='EMAIL_MESSAGE'          id='EMAIL_MESSAGE'          value=''>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='session_id'             id='session_id'             value='$this->session'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='formsessionID'          id='formsessionID'          value='$this->formsessionID'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='nuMoved'                id='nuMoved'                value=''>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='beenedited'             id='beenedited'             value='0'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='recordID'               id='recordID'               value='$this->recordID'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='clone'                  id='clone'                  value='$this->cloning'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='close_after_save'       id='close_after_save'       value='0'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='number_of_tabs'         id='number_of_tabs'         value='".count($this->formTabs)."'>$this->CRLF";
//        print  "$this->TAB<input type='hidden' name='refresh_after_save'     id='refresh_after_save'     value='".$this->form->sfo_refresh_after_save."'>$this->CRLF";
        print  "$this->TAB<input type='hidden' name='customDirectory'        id='customDirectory'        value='$this->customDirectory'>$this->CRLF";
		$t = nuRunQuery("SELECT * FROM zzsys_activity WHERE zzsys_activity_id = '$this->recordID'");
        $r = db_fetch_object($t);
        print  "$this->TAB<input type='hidden' name='nuActivityCode'         id='nuActivityCode'         value='$r->sat_all_code'>$this->CRLF";
        print  "$this->TAB<iframe src='' id='nuFrame' style='display:none'></iframe></div>$this->CRLF";


    }

    private function buildFormTabs(){

		for($i=0;$i<count($this->formTabs);$i++){
            $this->tabHTML .= $this->buildTab($i, $this->formTabs);
        }
        $this->pageHeader($this->formTabs);

    }
    
    
    private function buildATab($pUnselectedColor, $pSelectedColor, $pTitle, $pLeft, $pTabNumber){

      $w       = strlen($pTitle)* 11;

        if(count($this->formTabs)> 1){ // only have tabs if there is more than one
		  $s =      $this->TAB."<div id='TabNo$pTabNumber' class='selected' style='top:4px;left:".$pLeft."px;width:".$w."px;height:50px;position:absolute;overflow:hidden;text-align:center'>\n";
		  $s .= $this->TAB."   <div style='background:blue;top:0px;left:0px;width:5px;height:5px;position:absolute;overflow:hidden;'>\n";
		  $s .= $this->TAB."      <div class='nubullet' style='background:$pUnselectedColor;top:-23px;left:-3px;position:absolute;font-size:50px;font-family:arial;color:$pSelectedColor;overflow:hidden;'>&bull;\n";
		  $s .= $this->TAB."      </div>\n";
		  $s .= $this->TAB."   </div>\n";
		  $s .= $this->TAB."   <div style='background:blue;top:0px;right:-1px;width:7px;height:7px;position:absolute;overflow:hidden;'>\n";
		  $s .= $this->TAB."      <div class='nubullet' style='cursor:pointer;background:$pUnselectedColor;top:-23px;left:-8px;position:absolute;font-size:50px;font-family:arial;color:$pSelectedColor;overflow:hidden;'>&bull;\n";
		  $s .= $this->TAB."      </div>\n";
		  $s .= $this->TAB."   </div>&nbsp;$pTitle&nbsp;\n";
		  $s .= $this->TAB."</div>\n";
		  $s .= $this->TAB."<div id='HiddenTabNo$pTabNumber' class='tab' onclick='showTab($pTabNumber)' onmouseover='MIN(this)' onmouseout='MOUT(this)'style='background:$pUnselectedColor;top:4px;left:".$pLeft."px;width:".$w."px;height:20px;position:absolute;overflow:hidden;text-align:center'>\n";
		  $s .= $this->TAB."&nbsp;$pTitle&nbsp;\n";
		  $s .= $this->TAB."</div>\n";

        }

      return $s;

    
    }

    private function buildObject($o){

        $formObject                    = new formObject($this, $this->recordID);
		$formObject->parentType        = 'form';
		$formObject->customDirectory   = $this->customDirectory;
		$formObject->session           = $this->session;
		$formObject->activity          = $this->runActivity;
		
		$formObject->formsessionID     = $this->formsessionID;
        $formObject->setObjectProperties($o);
        
        //---decide whether to show or hide this object
        $formObject->displayThisObject = displayCondition($this->arrayOfHashVariables, $o->sob_all_display_condition);

    	//--dont add read only fields to the list or displays (because they canted be edited by the user
     	if($formObject->displayThisObject){                                                                                                           //--isn't displayed
    		if($formObject->objectProperty['sob_' . $formObject->objectProperty['sob_all_type'] . '_read_only'] != '1'){                          //--isn't read only
    			if($formObject->objectProperty['sob_all_type'] == 'text' and $formObject->objectProperty['sob_text_password'] != '1'){//--isn't a password field
    				$GLOBALS['formValues'][]               = $formObject->objectProperty['sob_all_name'];
    			}
    			if($formObject->objectProperty['sob_all_type'] == 'textarea'){
    				$GLOBALS['formValues'][]               = $formObject->objectProperty['sob_all_name'];
    			}
    			if($formObject->objectProperty['sob_all_type'] == 'file'){
    				$GLOBALS['formValues'][]               = $formObject->objectProperty['sob_all_name'];
    			}
    			if($formObject->objectProperty['sob_all_type'] == 'dropdown'){
    				$GLOBALS['formValues'][]               = $formObject->objectProperty['sob_all_name'];
    			}
    			if($formObject->objectProperty['sob_all_type'] == 'inarray'){
    				$GLOBALS['formValues'][]               = $formObject->objectProperty['sob_all_name'];
    			}
    		}
    	}

        //---get the value and format it
		if($o->sob_all_type == 'file'){
			$value = $this->recordValues[$o->sob_all_name . '_file_name'];
		}else{
			$value = $this->recordValues[$o->sob_all_name];
		}
        $formObject->objectValue               = $formObject->setValue($this->recordID, $this->cloning, $value);
        //---create the html string that will display it
		$formObject->objectHtml        = $formObject->buildObjectHTML($this->CRLF, $this->TAB.$this->TAB.$this->TAB.$this->TAB.$this->TAB,'');
		$this->formObjects[]           = $formObject;
//		nuRunQuery("DROP TABLE IF EXISTS $formObject->TT");

    }

    public function execute(){

        $this->appendshowTabfunction();
        $this->buildFormTabs();
        $this->appendJSfunction(setFormatArray());
        $this->displayHeader();
        $this->displayBody();
        $this->displayFooter();

    }

}


class formObject{


	public  $objectProperty      = array();
	public  $lookupArray         = array();
	public  $setup               = array();
	public  $objectValue         = '';
	public  $objectHTML          = '';
	public  $subformObjects      = array();
	public  $recordValues        = array();
	public  $displayThisObject   = true;
	public  $parentType          = '';
	private $parentForm          = null;
	public  $formsessionID       = '';    //----id that is unique to this instance of form
	public  $recordID            = '';
	private $subformRowNumber    = 0;
	public  $subformPrefix       = '';
	public  $nuSubformRow        = '';
	public  $objectType          = '';
	public  $objectName          = '';
	public  $TT                  = '';
	public  $customDirectory     = '';
	public  $activity            = '';
	public  $session             = '';    //----id that remains the same throughout login time
	private $offset             = 0;



    function __construct(Form $form, $pRecordID){

		$GLOBALS['HTTP_USER_AGENT'] = (isset($GLOBALS['HTTP_USER_AGENT']) ? $GLOBALS['HTTP_USER_AGENT'] : '');
		if(stripos($GLOBALS['HTTP_USER_AGENT'], 'iPad') === false){  //--not running in an iPad
			$this->offset        = 16;
		}else{
			$this->offset        = 13;
		}
    	$this->parentForm        = $form;
    	$this->recordID          = $pRecordID;
    	$this->TT                = TT();           //---temp table name
    	$this->setup             = $form->setup;

    }

	public function nextRowNumber(){
		$this->subformPrefix     = $this->objectName.substr('000'.$this->subformRowNumber,-4);
		$this->subformRowNumber  = $this->subformRowNumber + 1;
	}
	
    public function setObjectProperties($o){	
		
		reset($o);
		while(list($key, $value)                   = each($o)){
			$this->objectProperty[$key] = $value;	
		}
		
		//  Run sql statement(s) that are run before object is created 
		// -ideal for creating a temp file that will be used by a dropdown.
 		
		if($o->sob_all_sql_run_before_display != ''){
			//---replace any hashes with variables
	    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_all_sql_run_before_display'], $this->recordID);
	    	$sqlStatements = array();
	    	$sqlStatements = explode(';', $sql);
	    	//---create a tempfile to be used later as object is being built.
	    	for($i = 0 ; $i < count($sqlStatements) ; $i++){
	    		if(trim($sqlStatements[$i]) != ''){
				    nuRunQuery($sqlStatements[$i]);
	    		}
	    	}
		}

    }
	
	public function setValue($pRecordID, $pClone, $pValue){

		$type             = $this->objectProperty['sob_all_type'];
		$this->objectType = $this->objectProperty['sob_all_type'];
		if ($type    == 'display'){
			//---replace any hashes with variables
	    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_display_sql'], $this->recordID);
	    	//---run sql and use the first row and colomn as the value for this field
            if(trim($sql) == ''){return '';}
		    $t        = nuRunQuery($sql);
		    $r        = db_fetch_row($t);
	    	return formatTextValue($r[0], $this->objectProperty['sob_display_format']);
    	}
	if ($type    == 'dropdown'){
	    	if($this->activity OR $pRecordID == '-1' OR ($pClone AND $this->objectProperty['sob_all_clone'] != '1')){
				//---get default value from sob_text_default_value_sql
				if($this->objectProperty['sob_dropdown_default_value_sql']==''){
			    	return '';
				}
				//---replace any hashes with variables
		    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_dropdown_default_value_sql'], $this->recordID);
		    	//---run sql and use the first row and colomn as the default value for this field
			    $t        = nuRunQuery($sql);
			    $r        = db_fetch_row($t);
		    	return $r[0];
			}
	    	return $pValue; //---return value already in record
    	}
	if ($type    == 'lookup'){
	    	if($pValue == '' or $this->activity OR $pRecordID == '-1' OR ($pClone AND $this->objectProperty['sob_all_clone'] != '1')){
    			//---get default value from sob_text_default_value_sql
	    		if($this->objectProperty['sob_lookup_default_value_sql']==''){
		    		$this->lookupArray['id']            = '';
			    	$this->lookupArray['code']          = '';
				    $this->lookupArray['description']   = '';
    				return '';
	    		}

	    		$defaultValueSQL  = replaceVariablesInString('',$this->objectProperty['sob_lookup_default_value_sql'], '');
		    	$T                = nuRunQuery($defaultValueSQL);
			    $R                = db_fetch_row($T);
    			$pValue           = $R[0];
	       	}

	    	//--get sql from lookup form
            if($this->objectProperty['sob_lookup_load_zzsysform_id'] == ''){
    	    	$lookupFormID                                          = $this->objectProperty['sob_lookup_zzsysform_id'];
            }else{
    	    	$lookupFormID                                          = $this->objectProperty['sob_lookup_load_zzsysform_id'];
            }
	    	$t                                                         = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$lookupFormID'");
	    	$LUr                                                       = db_fetch_object($t);
    		$browseTable                                               = $this->TT;
	    	$TT                                                        = $this->TT;
		    $this->parentForm->arrayOfHashVariables['#formSessionID#'] = $this->parentForm->formsessionID;
		    $this->parentForm->arrayOfHashVariables['#formID#']        = $this->parentForm->formID;
		    $this->parentForm->arrayOfHashVariables['#browseTable#']   = $this->TT;
    		$this->parentForm->arrayOfHashVariables['#TT#']            = $this->TT;

			
			$GLOBALS['nuEvent'] = "(nuBuilder Before Browse) of " . $LUr->sfo_name . " : ";
    		eval(replaceHashVariablesWithValues($this->parentForm->arrayOfHashVariables, $LUr->sfo_custom_code_run_before_browse)); //--replace hash variables then run code
			$GLOBALS['nuEvent'] = '';
    		$LUr->sfo_sql         = replaceHashVariablesWithValues($this->parentForm->arrayOfHashVariables, $LUr->sfo_sql); //--replace hash variables
	    	$SQL                  = new sqlString($LUr->sfo_sql);

    		if($SQL->getWhere()==''){
	    		$SQL->setWhere("WHERE ".$this->objectProperty['sob_lookup_id_field']." = '$pValue'");
		    }else{
			    $SQL->setWhere($SQL->getWhere() . " AND (".$this->objectProperty['sob_lookup_id_field']." = '$pValue')");
    		}
	    	$SQL->removeAllFields();
		    $SQL->addField($this->objectProperty['sob_lookup_id_field']);
    		$SQL->addField($this->objectProperty['sob_lookup_code_field']);
	    	if($this->objectProperty['sob_lookup_description_field']==''){
		    	$SQL->addField("''");
    		}else{
	    		$SQL->addField($this->objectProperty['sob_lookup_description_field']);
		    }

    		$T = nuRunQuery($SQL->SQL);

	    	$R = db_fetch_row($T);
		    $this->lookupArray['id']            = $R[0];
    		$this->lookupArray['code']          = $R[1];
	    	$this->lookupArray['description']   = $R[2];
    		return $pValue; //---return value already in record
    	}
	if ($type    == 'inarray'){
		if($this->activity OR $pRecordID == '-1' OR ($pClone AND $this->objectProperty['sob_all_clone'] != '1')){
			//---get default value from sob_text_default_value_sql
			if($this->objectProperty['sob_inarray_default_value_sql']==''){
				return '';
			}
			//---replace any hashes with variables
			$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_inarray_default_value_sql'], $this->recordID);
			//---run sql and use the first row and column as the default value for this field
			$t        = nuRunQuery($sql);
			$r        = db_fetch_row($t);
			return $r[0];
		}
		return $pValue; //---return value already in record
    	}
	if ($type    == 'password'){
		return $pValue;
   	}
	if ($type    == 'file'){
		return $pValue;
   	}
	if ($type    == 'text'){
		if($this->activity OR $pRecordID == '-1' OR ($pClone AND $this->objectProperty['sob_all_clone'] != '1')){
		//---get default value from sob_text_default_value_sql
			if($this->objectProperty['sob_text_default_value_sql']==''){
				return formatTextValue('', $this->objectProperty['sob_text_format']);
			}
			//---replace any hashes with variables
		    $sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_text_default_value_sql'], $this->recordID);
		    	//---run sql and use the first row and colomn as the default value for this field
			$t        = nuRunQuery($sql);
			$r        = db_fetch_row($t);
		    	return formatTextValue($r[0], $this->objectProperty['sob_text_format']);
		}
	    	return formatTextValue($pValue, $this->objectProperty['sob_text_format']); //---return value already in record
    	}
	if ($type    == 'textarea'){
	    	if($this->activity OR $pRecordID == '-1' OR ($pClone AND $this->objectProperty['sob_all_clone'] != '1')){
			//---get default value from sob_text_default_value_sql
			if($this->objectProperty['sob_textarea_default_value_sql']==''){
			    	return '';
			}
			//---replace any hashes with variables
		    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_textarea_default_value_sql'], $this->recordID);
		    	//---run sql and use the first row and colomn as the default value for this field
			$t        = nuRunQuery($sql);
			$r        = db_fetch_row($t);
			return $r[0];
		}
	    	return $pValue; //---return value already in record
    	}
}

	private function addProperty($pName, $pValue){
		if($pValue==''){return;}
		return $pName . '="' . $pValue. '" ';
	}

	private function addStyle($pName, $pValue){
		return "$pName:$pValue;";
	}

	public function buildObjectHTML($CRLF, $TAB, $PREFIX){

		$dq              = '"';
		$fieldName       = $PREFIX.$this->objectProperty['sob_all_name'];

		//--create an event for globeadmin users
		$openObject      = '';
		if($this->parentForm->zzsys_user_id == 'globeadmin' and $this->parentForm->form->sys_setup != '1'){
			$theObjectID = '"' . $this->objectProperty['zzsys_object_id'] . '"' ;
			$openObject  = " ondblclick='nuControlKey=true;openForm(\"object\",$theObjectID)' ";
		}

   		$fieldTitle      = "<div $openObject id='$fieldName" . "_title'>" . $this->objectProperty['sob_all_title'] . '</div>';

        if(!$this->displayThisObject){ //--set it blank so no title will be displayed
            $fieldTitle  = '';
        }

		$fieldValue      = html_entity_decode(htmlentities($this->objectValue,ENT_QUOTES, "UTF-8"),ENT_NOQUOTES, "UTF-8");
		$type            = $this->objectProperty['sob_all_type'];

		$id              = uniqid('',true);
		$pValue          = addEscapes($this->objectValue);
		$ses             = $_GET['ses'];

		$GLOBALS['deleteTT'][]         .= "`$this->TT`";
		if ($type  != 'words'){
			$GLOBALS['insertSV'][]     .= "('$id', '$this->formsessionID', '" . nuDateAddDays(Today(),2) . "', '$ses', '$fieldName', '$pValue', '" . date('Y-m-d H:i:s') . "')";
		}

		if ($type    == 'button'){
    		$htmlString   = $this->buildHTMLForButton($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'display'){
    		$htmlString   = $this->buildHTMLForDisplay($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'dropdown'){
    		$htmlString   = $this->buildHTMLForDropdown($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'graph'){
    		$htmlString   = $this->buildHTMLForGraph($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue);
    	}
		if ($type    == 'html'){
    		$htmlString   = $this->buildHTMLForHtml($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue);
    	}
		if ($type    == 'inarray'){
    		$htmlString   = $this->buildHTMLForInarray($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'listbox'){
    		$htmlString   = $this->buildHTMLForListbox($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue);
    	}
		if ($type    == 'lookup'){
    		$htmlString   = $this->buildHTMLForLookup($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'text'){
    		$htmlString   = $this->buildHTMLForText($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'file'){
    		$htmlString   = $this->buildHTMLForText($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX, 'file');
    	}
		if ($type    == 'textarea'){
    		$htmlString   = $this->buildHTMLForTextarea($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX);
    	}
		if ($type    == 'words'){
    		$htmlString   = $this->buildHTMLForWords($CRLF, $TAB, $PREFIX);
    	}
		if ($type    == 'subform'){

			if($this->parentForm->zzsys_user_id == 'globeadmin' and $this->parentForm->form->sys_setup != '1'){
				$openObject  = " ondblclick='if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};nuControlKey=true;openForm(\"object\",$theObjectID)' ";
			}

			$fieldTitle      = "<div $openObject id='$fieldName" . "_sf_inner_title'>" . $this->objectProperty['sob_all_title'] . '</div>';
    		$htmlString   = $this->buildHTMLForSubform($CRLF, $TAB, $fieldName, $fieldTitle, $this->objectProperty['sob_subform_blank_rows'], $this->objectProperty['zzsys_object_id']);
    	}
		return $htmlString;

	}

	private function buildHTMLForText($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX, $object = ''){

		$td1                     = '';
		$tr1                     = '';
		$td2                     = '';
		$tr2                     = '';
		$titleTableDetail        = '';
		$textFormat              = '';
		
	    if ($this->displayThisObject && $this->objectProperty['sob_text_length'] > 0){

	    	if($this->objectProperty['sob_text_password'] == '1'){
		    	$inputType            = 'password';
	    	}else{
		    	$inputType            = 'text';
		    	$textFormat           = $this->objectProperty['sob_text_format'];
	    	}
	    }else{
	    	$inputType            = 'hidden';
	    	$fieldTitle           = '';
		}
		
		if($object == 'file'){
	    	$inputType            = 'file';
		}

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left;white-space: nowrap;'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}else{
			$rowPrefix            = $this->subformRowNumber;
		}
		if($PREFIX               != ''){
			$untick               = "untick('$PREFIX', this);";
			$moveit               = "";
		}else{
			$untick               = "";
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

		$style =          $this->addStyle('width', ($this->objectProperty['sob_text_length']*$this->offset)."px");
		$style = $style . $this->addStyle('text-align', align($this->objectProperty['sob_text_align']));
		$style = $style . $this->addStyle('vertical-align', 'top');

		$s     =      $titleTableDetail . $td1;
		$s     .= "<input ";
		$s     .= $this->addProperty('type'           , $inputType);
		$s     .= $this->addProperty('accept'         , $textFormat);
		$s     .= $this->addProperty('name'           , $this->subformPrefix.$fieldName);
		$s     .= $this->addProperty('id'             , $this->subformPrefix.$fieldName);
		$s     .= $this->addProperty('value'          , $fieldValue);
		$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
		if($object != 'file'){
			$s .= $this->addProperty('style'          , $style);
		}
		if($inputType == 'text'){
			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'      , 'objects');
			}else{
			    $s .= $this->addProperty('class'      , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			if($this->objectProperty['sob_text_total']==''){
				$totalUp = '';
			}else{
				$totalUp = "nuColumnTotal(this, '" . $this->objectProperty['sob_text_total'] . "');";
			}
			$s .= $this->addProperty('onchange'       , $untick."uDB(this,'text');$totalUp".fixDQ($this->objectProperty['sob_all_on_change']));
			$s .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
			$s .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".fixDQ($this->objectProperty['sob_all_on_focus']));
			$s .= $this->addProperty('title'          , fixDQ($this->objectProperty['sob_all_tool_tip']));
			$mask = '';
			if($this->objectProperty['sob_text_mask']!=''){
				$mask = ";return nuMask(this,'" . $this->objectProperty['sob_text_mask'] . "')";
			}
			$s .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']).$mask);
			$s .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_double_click']));
		}
		if($object == 'file'){
			$uncheck = "document.getElementById('delete_file_$fieldName').checked = false;document.getElementById('z___$fieldName').value = '';";
			$s .= $this->addProperty('onchange'       , "$uncheck$untick;");
			$s .= $this->addProperty('ondblclick'     , $moveit);
		}else{
			if($this->objectProperty['sob_text_read_only']=='1'){
				$s .= " readonly='readonly'  tabindex='10000' ";
			}
		}

		$s     .= ">$CRLF";
		$format=textFormatsArray();

		if($object == 'file'){
			if($fieldValue == ''){
				$chbx =  "checked = 'checked'";
			}else{
				$titl =  "$fieldValue\n(check to delete)";
			}
			$s   .= "<input type='checkbox' id='delete_file_$fieldName' name='delete_file_$fieldName' tabindex='10000' title='$titl' $chbx>&nbsp;$this->CRLF";
		}else{
			if ($this->displayThisObject && $this->objectProperty['sob_text_length'] > 0){
				if($format[$this->objectProperty['sob_text_format']]->type=='date' and $this->objectProperty['sob_text_read_only']!='1'){
					if($this->setup->set_calendar_mouse_up==''){
						$s   .= "<input type='button' class='calbutton' value='c' id='cal_$fieldName' onclick='nuSetRow(\"$PREFIX\"); getCal(\"$fieldName\")' tabindex='10000' style='font-size: 10pt'>&nbsp;$this->CRLF";
					}else{
						 $s  .= "<img class='calbutton' id='cal_$fieldName' src='cal_up.png' alt='Calendar' onmouseup=\"this.src='cal_up.png'\" onmouseout=\"this.src='cal_up.png'\"  onmousedown=\"this.src='cal_down.png'\" onclick='nuSetRow(\"$PREFIX\"); getCal(\"$fieldName\")'>$this->CRLF";
					}
				}
			}	
		}
		$s     .= $td2;

		return $tr1.$s.$tr2;
	}

	private function buildHTMLForDisplay($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

	    if ($this->displayThisObject && $this->objectProperty['sob_display_length'] > 0){
	    	$inputType            = 'text';
	    }else{
	    	$inputType            = 'hidden';
	    	$fieldTitle           = '';
		}

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}

		$style =          $this->addStyle('width', ($this->objectProperty['sob_display_length']*$this->offset)."px");
		$style = $style . $this->addStyle('text-align', align($this->objectProperty['sob_display_align']));

		$s     =      $titleTableDetail . $td1;
		$s     .= "<input ";
		$s     .= $this->addProperty('type'           , $inputType);
		$s     .= $this->addProperty('name'           , $fieldName);
		$s     .= $this->addProperty('id'             , $fieldName);
		$s     .= $this->addProperty('value'          , $fieldValue);
		$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
		$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);
		$s     .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".$this->objectProperty['sob_all_on_focus']);

		if($this->parentForm->zzsys_user_id == 'globeadmin' and $PREFIX == ''){
			$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
		}else{
			$moveit           = "";
		}
		$s     .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_double_click']));

		if($inputType == 'text'){
			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'          , 'objects');
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			$s .= $this->addProperty('style'          , $style);
		}

		$s     .= " readonly='readonly'  tabindex='10000'>$CRLF";
		$s     .= $td2;

		return $tr1.$s.$tr2;
	}

	private function buildHTMLForButton($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

	    if (!$this->displayThisObject){
	    	return '';
		}

		if($this->parentForm->zzsys_user_id == 'globeadmin' and $PREFIX == ''){
			$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
		}else{
			$moveit           = "";
		}
		
//      if($this->parentType      == 'form' and $this->objectProperty['sob_button_top']=='0'){ //--not a subform
        if($this->parentType      == 'form' and ($this->objectProperty['sob_button_top']=='0' || is_null($this->objectProperty['sob_button_top']))){ //--not a subform  (2012-12-19 massiws)
	    	$titleTableDetail     = "$TAB  <td class='selected'></td>$CRLF";
	    	$tr1                  = "$TAB<tr dblclick='$moveit' class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
	}else{
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
	}

		$style = '';
		$s     =      $titleTableDetail . $td1;
/*
		if($this->objectProperty['sob_button_top']!='0'){
			$style = $style . $this->addStyle('left'    , ($this->objectProperty['sob_button_left'])."px");
			$style = $style . $this->addStyle('top'     , ($this->objectProperty['sob_button_top'])."px");
			$style = $style . $this->addStyle('position', 'absolute');
		}
*/
		$style = $style . $this->addStyle('width',  ($this->objectProperty['sob_button_length']*$this->offset)."px");
		$s     .= "<input ";
		$s     .= $this->addProperty('type'           , 'button');
		$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);
		$s     .= $this->addProperty('value'          , $this->objectProperty['sob_all_title']);
		$s     .= $this->addProperty('style'          , $style);
		$s     .= $this->addProperty('name'           , $this->subformPrefix.$fieldName);
		$s     .= $this->addProperty('id'             , $this->subformPrefix.$fieldName);
		$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
		if($this->objectProperty['sob_button_zzsys_form_id']!=''){
			$f = $this->objectProperty['sob_button_zzsys_form_id'];
			if($this->objectProperty['sob_button_skip_browse_record_id']==''){//--open form via browse
				$b = $this->objectProperty['sob_button_browse_filter'];
	      		$s .= $this->addProperty('onclick'        , "if(isFirstClick()){openBrowse('$f', '$b', '', '$this->session', '');}");
			}else{//--open form with a certain ID
				$b = $this->objectProperty['sob_button_skip_browse_record_id'];
	      		$s .= $this->addProperty('onclick'        , "if(isFirstClick()){openForm('$f', '$b');}");
			}
		}else{
      		$s .= $this->addProperty('onclick'        , "nuSetRow('$PREFIX');".fixDQ($this->objectProperty['sob_all_on_double_click']));
		}
		$s     .= $this->addProperty('ondblclick'     , $moveit);

		if($this->objectProperty['sob_all_class']==''){
		    $s .= $this->addProperty('class'          , 'button');
		}else{
		    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
		}
		$s     .= ">$CRLF";
		$s     .= $td2;
		return $tr1.$s.$tr2;
	}

	private function buildHTMLForDropdown($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}

		if($PREFIX               != ''){
			$untick               = "untick('$PREFIX', this);";
			$moveit               = "";
		}else{
			$untick               = "";
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

	    if (!$this->displayThisObject){
	    	$s     = "$TAB  $td1<input type='hidden' name='fieldName' value='$fieldValue'>$CRLF";
			$s     .= "$TAB$CRLF$td2";
		}else{
			$style =      $this->addStyle('width', ($this->objectProperty['sob_dropdown_length']*$this->offset)."px");
			$s     =      $titleTableDetail . $td1;
			$s     .= '<select ';
			$s     .= $this->addProperty('name'           , $fieldName);
			$s     .= $this->addProperty('id'             , $fieldName);
//			$s     .= $this->addProperty('value'          , $fieldValue);
			$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
			$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);

			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'          , 'objects');
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			$s     .= $this->addProperty('style'          , $style);
			$s     .= $this->addProperty('onchange'       , $untick."uDB(this);".fixDQ($this->objectProperty['sob_all_on_change']));
			$s     .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
			$s     .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".fixDQ($this->objectProperty['sob_all_on_focus']));
			$s     .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']));
			$s     .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_double_click']));
			if($this->objectProperty['sob_dropdown_read_only']=='1'){
				$s .= " disabled='disabled' ";
			}

			$s     .= ">$CRLF";


			//---replace any hashes with variables
			if($this->parentType == 'form'){ //--- sets #id# to the record ID of the main form not the subform
				$hashID    = $this->recordID;
			}else{
				$hashID    = $this->parentForm->recordID;
			}
	    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_dropdown_sql'], $hashID);
	    	//---run sql and use the first row and colomn as the value for this field
		    $t        = nuRunQuery($sql);
		    	$s    .= "$TAB        <option value=''></option>$CRLF";
		    while($r  = db_fetch_row($t)){
				if($r[0] == $fieldValue){
			    	$s    .= "$TAB        <option selected value='$r[0]'>$r[1]</option>$CRLF";
				}else{
			    	$s    .= "$TAB        <option value='$r[0]'>$r[1]</option>$CRLF";
				}
			}
			$s     .= "$TAB</select>$CRLF$td2";

		}

		return $tr1.$s.$tr2;
	}


	private function buildHTMLForGraph($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue){

	    if (!$this->displayThisObject){
	    	return '';
		}
    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'><span id='$fieldName"."_title'>$fieldTitle</span></td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}else{
			$rowPrefix            = $this->subformRowNumber;
		}

		if($rowPrefix == ''){
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "ondblclick='if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};'";
			}else{
				$moveit           = "";
			}
		}
		
		
		$s     =      $titleTableDetail . $td1;
		$s     .= "<img $moveit alt='' ";
		$sesQS = 'ses=' . $this->parentForm->session . '&id=' . $this->parentForm->recordID . '&f=' . $this->parentForm->formID;  //--add session, form and record ids to querystring
		$s     .= $this->addProperty('src'         , "graph_object.php?$sesQS&dir=$this->customDirectory&graphID=" . $this->objectProperty['zzsys_object_id']);
		$s     .= $this->addProperty('id'          , $rowPrefix.$fieldName);
		$s     .= $this->addProperty('onclick'     , fixDQ($this->objectProperty['sob_all_on_double_click']));
		$s     .= ">$CRLF";
		$s     .= $td2;

		return $tr1.$s.$tr2;
	}



	private function buildHTMLForHtml($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue){

	    if (!$this->displayThisObject){
	    	return '';
		}
    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'></td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}else{
			$rowPrefix            = $this->subformRowNumber;
		}


		
		$s     = $titleTableDetail . $td1;
        $html  = replaceHashVariablesWithValues($this->parentForm->arrayOfHashVariables, $this->objectProperty['sob_html_code']);
		$s    .= $CRLF . $CRLF . $html . $CRLF . $CRLF . $td2;

		return $tr1.$s.$tr2;
	}


	private function buildHTMLForInarray($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

	    if ($this->displayThisObject){
	    	$inputType            = 'text';
	    }else{
	    	$inputType            = 'hidden';
	    	$fieldTitle           = '';
		}

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}

		if($PREFIX               != ''){
			$untick               = "untick('$PREFIX', this);";
			$moveit               = "";
		}else{
			$untick               = "";
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

		$style =          $this->addStyle('width', ($this->objectProperty['sob_inarray_length']*$this->offset)."px");
		$style = $style . $this->addStyle('text-align', $this->objectProperty['sob_inarray_align']);

		$s     =      $titleTableDetail . $td1;
		$s     .= "<input ";
		$s     .= $this->addProperty('type'           , $inputType);
		$s     .= $this->addProperty('name'           , $fieldName);
		$s     .= $this->addProperty('id'             , $fieldName);
		$s     .= $this->addProperty('value'          , $fieldValue);
		$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);

		if($inputType == 'text'){
			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'          , 'objects');
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			$s .= $this->addProperty('style'          , $style);
			$s .= $this->addProperty('onchange'       , $untick."uDB(this);" . $this->objectProperty['sob_all_name'] . '_array(this);'.fixDQ($this->objectProperty['sob_all_onchange']));
			$s .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
			$s .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".fixDQ($this->objectProperty['sob_all_on_focus']));
			$s .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']));
			$s .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_doubleclick']));
			$s .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);
		}
		if($this->objectProperty['sob_text_read_only']=='1'){
			$s .= " readonly='readonly'  tabindex='10000' ";
		}

		$s     .= ">$CRLF";
		$s     .= $td2;



		//---replace any hashes with variables
    	$sql      = replaceVariablesInString($this->TT,$this->objectProperty['sob_inarray_sql'], $this->recordID);
    	//---run sql and use the first row and colomn as the value for this field
	    $t        = nuRunQuery($sql);
		if(!in_array($this->objectProperty['sob_all_name'], $this->parentForm->inarrayFunctions)){

		    $fun      = "function " . $this->objectProperty['sob_all_name'] . "_array(pThis){ $CRLF";
		    $fun      = $fun . "   var ar    = new Array();$CRLF";
		    $fun      = $fun . "   var found = new Boolean(false);$CRLF";
		    $counter  = 0;
		    while($r  = db_fetch_row($t)){
		    	if($counter == 0){
					$first  = $r[0];
				}
				$last = $r[0];
			    $fun  = $fun . "   ar[$counter] = \"$r[0]\";$CRLF";
			    $counter = $counter + 1;
			}
	
			$dq = '"';
		    $fun      = $fun . "   $CRLF";
		    $fun      = $fun . "   for (i=0 ; i < ar.length ; i++){ $CRLF";
		    $fun      = $fun . "      if(ar[i] == pThis.value){ $CRLF";
		    $fun      = $fun . "         found=true;$CRLF";
		    $fun      = $fun . "      }$CRLF";
		    $fun      = $fun . "   }$CRLF";
		    $fun      = $fun . "   if(found==false){ $CRLF";
		    $fun      = $fun . "      alert('Must be between $dq$first$dq and $dq$last$dq')$CRLF";
		    $fun      = $fun . "      pThis.value = '';$CRLF";
		    $fun      = $fun . "   }$CRLF";
		    $fun      = $fun . "}$CRLF";
			$this->parentForm->inarrayFunctions[] = $this->objectProperty['sob_all_name'];
	    	$this->parentForm->appendJSfunction($fun);

			
		}

		return $tr1.$s.$tr2;
	}


	private function buildHTMLForListbox($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue){

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:center'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' align='left' style='align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}


		if($this->parentForm->zzsys_user_id == 'globeadmin'){
			$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
		}else{
			$moveit           = "";
		}


	    if (!$this->displayThisObject){
	    	$s     .= "$TAB  <input type='hidden' name='$fieldName' value='$fieldValue'>$CRLF";
		}else{
			$style =      $this->addStyle('width', ($this->objectProperty['sob_listbox_length']*$this->offset)."px");
			$s     =$titleTableDetail . $td1;
			if($this->objectProperty['sob_listbox_button_style']==''){
		    	$s .= "$TAB  <input type='button' style='$style' class='button' name='sel_$fieldName' id='sel_$fieldName' value='Select All' onclick=\"SelectAll('$fieldName')\" onblur=\"uDB(document.getElementById('theform').$fieldName,'listbox');\"><br>$CRLF";
			}else{
		    	$s .= "$TAB  <input type='button' class='" . $this->objectProperty['sob_listbox_button_style'] . "' name='sel_$fieldName' id='sel_$fieldName' value='Select All' onclick=\"SelectAll('$fieldName')\" onblur=\"uDB(document.getElementById('theform').$fieldName,'listbox');\"><br>$CRLF";
			}
			$s     .= "<select multiple='multiple' ";
			$s     .= $this->addProperty('name'           , $fieldName.'[]');
			$s     .= $this->addProperty('id'             , $fieldName);
			$s     .= $this->addProperty('size'           , $this->objectProperty['sob_listbox_height']);
			$s     .= $this->addProperty('value'          , $fieldValue);
			$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'          , 'objects');
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			$s     .= $this->addProperty('style'          , $style);
			$s     .= $this->addProperty('onchange'       , "uDB(this,'listbox');".$this->objectProperty['sob_all_on_change']);
			$s     .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
			$s     .= $this->addProperty('onfocus'        , fixDQ($this->objectProperty['sob_all_on_focus']));
			$s     .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']));
			$s     .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_double_click']));
			$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);
			$s     .= ">$CRLF";


			//---replace any hashes with variables

	    	$sql            = replaceVariablesInString($this->TT,$this->objectProperty['sob_listbox_sql'], $this->recordID);
	    	//---run sql and use the first row and colomn as the value for this field
		    $t              = nuRunQuery($sql);
		    while($r        = db_fetch_row($t)){
		    	$s          .= "$TAB        <option value='$r[0]'>$r[1]</option>$CRLF";
			}

		}
		$s     .= "$TAB   </select>$CRLF$td2";

		return $tr1.$s.$tr2;
	}


	private function buildHTMLForLookup($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}

		if($PREFIX               != ''){
			$untick               = "untick('$PREFIX', this);";
			$moveit               = "";
		}else{
			$untick               = "";
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id.substring(4), 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

		$s                        = $titleTableDetail . $td1;
    	$s                        .= "$TAB  <input name='$fieldName' id='$fieldName' type='hidden' value='".htmlentities($this->lookupArray['id'], ENT_QUOTES, "UTF-8")."'>$CRLF";
		$ip                       = ''; //--button for small browser

	    if ($this->displayThisObject){

			$style = $this->addStyle('width', (($this->objectProperty['sob_lookup_code_length']-1)*$this->offset)."px");
			$i     = $this->objectProperty['zzsys_object_id'];
			if($this->objectProperty['sob_lookup_read_only']!='1'){

				$dq = '"';
				$ip  = "<img class='lubutton' id='luup_$fieldName' ";
				$ip .= "src='luup.png' alt='Lookup' style='vertical-align:text-bottom' ";
				$ip .= "onmouseup   =$dq this.src='luup.png'  $dq ";
				$ip .= "onmouseout  =$dq this.src='luup.png'  $dq ";
				$ip .= "onmousedown =$dq this.src='ludown.png'$dq ";
				$ip .= "onclick     =$dq nuSetRow('$PREFIX'); nuControlKey=true;openIBrowse('$i', '', '$PREFIX', '$this->session', '$this->formsessionID') $dq >";

			}

			$s     .= "$TAB  <input ";
			$s     .= $this->addProperty('name'           , 'code'.$fieldName);
			$s     .= $this->addProperty('id'             , 'code'.$fieldName);
			$s     .= $this->addProperty('value'          , htmlentities($this->lookupArray['code'], ENT_QUOTES, "UTF-8"));
			$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
            $lookupcode = $this->objectProperty['sob_lookup_autocomplete'] ? 'lookupcode autocomplete' : 'lookupcode';
			if($this->objectProperty['sob_lookup_code_class'] == ''){
			    $s .= $this->addProperty('class'          , $lookupcode);
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_lookup_code_class']).($this->objectProperty['sob_lookup_read_only']=='1' ? '' : ' '.$lookupcode));
			}
			$s     .= $this->addProperty('style'          , $style);
			$s     .= $this->addProperty('onchange'       , $untick.';'.fixDQ($this->objectProperty['sob_all_on_change']));
			$s     .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
			$s     .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".fixDQ($this->objectProperty['sob_all_on_focus']));
			$s     .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']));
			$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);

			$f     = $this->objectProperty['sob_lookup_zzsysform_id'];


			if($this->objectProperty['sob_lookup_read_only']=='1'){
	      		$s .= $this->addProperty('ondblclick'        , $moveit);
				$s .= " readonly='readonly'  tabindex='10000'>";
			}else{
	      		$s .= $this->addProperty('accept'     , $i);
	      		$s .= $this->addProperty('ondblclick'        , $moveit."nuControlKey=true;openIBrowse('$i', '', '$PREFIX', '$this->session', '$this->formsessionID')");
	      		$s .= $this->addProperty('onclick'           , "edit_lookup('$f', '$fieldName')");
				$s .= ">$ip";
			}
			if($this->objectProperty['sob_lookup_no_description'] != 1){
				$style  = $this->addStyle('width', ($this->objectProperty['sob_lookup_description_length']*$this->offset)."px");
				$ds     .= $this->addProperty('name'           , 'description'.$fieldName);
				$ds     .= $this->addProperty('id'             , 'description'.$fieldName);
				$ds     .= $this->addProperty('value'          , htmlentities($this->lookupArray['description'], ENT_QUOTES, "UTF-8"));
				if($this->objectProperty['sob_lookup_description_class'] == ''){
									if($this->objectProperty['sob_lookup_description_length'] > 0) {
						$ds .= $this->addProperty('class'          , 'lookupdesc');
					} else {
						$ds .= $this->addProperty("type","hidden");
					}
				}else{
				    $ds .= $this->addProperty('class'          , $this->objectProperty['sob_lookup_description_class']);
				}
				$ds     .= $this->addProperty('style'          , $style);
				$s .= "<input $ds readonly='readonly' tabindex='10000'>$CRLF";
			}
			$s .= $td2;

		}

		return $tr1.$s.$tr2;
	}

	private function buildHTMLForTextarea($CRLF, $TAB, $fieldName, $fieldTitle, $fieldValue, $PREFIX){

	    if ($this->displayThisObject){
	    	$inputType            = 'password';
	    }else{
	    	$inputType            = 'hidden';
	    	$fieldTitle           = '';
		}

    	if($this->parentType      == 'form'){ //--not a subform
	    	$titleTableDetail     = "$TAB  <td class='selected' style='text-align:right'>$fieldTitle</td>$CRLF";
	    	$tr1                  = "$TAB<tr class='selected'>$CRLF";
	    	$tr2                  = "$TAB</tr>$CRLF";
	    	$td1                  = "$TAB  <td class='selected' style='text-align:left'>$CRLF$TAB    ";
	    	$td2                  = "$TAB  </td>$CRLF";
		}

		if($PREFIX               != ''){
			$untick               = "untick('$PREFIX', this);";
			$moveit               = "";
		}else{
			$untick               = "";
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

		$style =          $this->addStyle('width', ($this->objectProperty['sob_textarea_length']*$this->offset)."px");
		//17-06-09 -- Nick : added these in to prevent a horizontal scrollbar
		// vvvv				being present for textareas in both IE and FF
		$style = $style . $this->addStyle('overflow','scroll');
		$style = $style . $this->addStyle('overflow-y','scroll');
		$style = $style . $this->addStyle('overflow-x','hidden');
		$style = $style . $this->addStyle('overflow','-moz-scrollbars-vertical');
		// ^^^^
		//17-06-09
		$s     =      $titleTableDetail . $td1;
		$s     .= "<textarea ";
		$s     .= $this->addProperty('name'           , $fieldName);
		$s     .= $this->addProperty('id'             , $fieldName);
		$s     .= $this->addProperty('cols'           , ceil($this->objectProperty['sob_textarea_length']));
		$rows  = round($this->objectProperty['sob_textarea_height'])-1;
		$s     .= $this->addProperty('rows'           , $rows);
		$s     .= $this->addProperty('accesskey'      , $this->objectProperty['sob_all_access_key']);
			if($this->objectProperty['sob_all_class']==''){
			    $s .= $this->addProperty('class'          , 'objects');
			}else{
			    $s .= $this->addProperty('class'          , str_replace('.', '', $this->objectProperty['sob_all_class']));
			}
			if($this->objectProperty['sob_textarea_read_only'] == '1'){
				$s .= " readonly='readonly'  tabindex='10000' ";
			}
		$s     .= $this->addProperty('style'          , $style);
		$s     .= $this->addProperty('onchange'       , $untick."uDB(this);".fixDQ($this->objectProperty['sob_all_on_change']));
		$s     .= $this->addProperty('onblur'         , fixDQ($this->objectProperty['sob_all_on_blur']));
		$s     .= $this->addProperty('onfocus'        , "nuSetRow('$PREFIX');".$this->objectProperty['sob_all_on_focus']);
		$s     .= $this->addProperty('onkeypress'     , fixDQ($this->objectProperty['sob_all_on_key_press']));
		$s     .= $this->addProperty('ondblclick'     , $moveit.fixDQ($this->objectProperty['sob_all_on_double_click']));
		$s     .= $this->addProperty('title'          , $this->objectProperty['sob_all_tool_tip']);

		$s     .= ">$fieldValue</textarea>$CRLF";
	    if (!$this->displayThisObject){
	    	$s = '';
	    }
		$s     .= $td2;

		return $tr1.$s.$tr2;
	}


	private function buildHTMLForWords($CRLF, $TAB, $PREFIX){

    	$fieldValue           = $this->objectProperty['sob_all_title'];
    	$fieldName            = $this->objectProperty['sob_all_name'];

		if($PREFIX               != ''){
			$moveit               = "";
		}else{
			if($this->parentForm->zzsys_user_id == 'globeadmin'){
				$moveit           = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
			}else{
				$moveit           = "";
			}
		}

	    if ($this->displayThisObject){
	    	if($this->parentType      == 'form'){ //--not a subform
		    	$s                    = "$TAB<tr class='selected'>$CRLF";
		    	$s                    .= "$TAB  <td class='selected'>$CRLF$TAB    ";
		    	$s                    .= "$TAB  </td>$CRLF";
		    	$s                    .= "$TAB  <td class='selected' style='text-align:left'>";
		    	$s                    .= "$TAB  <span ondblclick='$moveit' class='selected' id='$PREFIX$fieldName'><b>$fieldValue</b></span>$CRLF$TAB    ";
		    	$s                    .= "$TAB  </td>$CRLF";
		    	$s                    .= "$TAB</tr>$CRLF";
		    	return $s;
			}else{
		    	return "<span style='color:black'>".$this->objectProperty['sob_all_name'].'</span>';
			}
	    }
	}


	private function buildHTMLForSubform($CRLF, $TAB, $fieldName, $fieldTitle, $blankRows, $objectID){

		if($this->parentForm->zzsys_user_id == 'globeadmin'){
			$moveit                    = "if(nuShiftKey){nuMoveObject(this.id, 0,0);return;};";
		}else{
			$moveit                    = "";
		}

		if($this->objectProperty['sob_subform_zzsysform_id'] != ''){
		
			$title                         = $this->objectProperty['sob_all_title'];
			$top                           = $this->objectProperty['sob_subform_top'] . 'px';
			$titletop                      = ($this->objectProperty['sob_subform_top'] - 20) . 'px';
			$left                          = $this->objectProperty['sob_subform_left'] . 'px';
			$titleleft                     = $this->objectProperty['sob_subform_left'] . 'px';
			$width                         = $this->objectProperty['sob_subform_width'] . 'px';
			$height                        = $this->objectProperty['sob_subform_height'] . 'px';
			$form                          = $this->objectProperty['sob_subform_zzsysform_id'];
			$searchstring                  = str_replace("#", "%23", $this->objectProperty['sob_subform_browse_filter']);
			$dir                           = $_GET['dir'];
			$ses                           = $_GET['ses'];
			$formsession                   = $this->parentForm->formsessionID;
			

			if($this->parentForm->zzsys_user_id == 'globeadmin' and $this->parentForm->form->sys_setup != '1'){
				$theObjectID               = '"' . $this->objectProperty['zzsys_object_id'] . '"' ;
				$openObject                = " ondblclick='$moveit"."nuControlKey=true;openForm(\"object\",$theObjectID)' ";
			}

			$fieldTitle                    = "<div  style='position:absolute;left:$titleleft;top:$titletop' $openObject id='$fieldName'> $title</div>";
			
			return $fieldTitle."<iframe id='ifr_$fieldName' style='position:absolute;left:$left;top:$top;width:$width;height:$height'  src='browse.php?x=1&p=1&f=$form&s=$searchstring&nuopener=$formsession&prefix=&dir=$dir&ses=$ses&form_ses=$ses&subform=1'></iframe>";
		}
		
	    if (!$this->displayThisObject){
	    	return '';
		}else{
			$this->parentForm->subformNames[] = $this->objectProperty['sob_all_name'];
			$this->parentForm->subformTabs[]  = $this->parentForm->formTabNames[$this->objectProperty['sob_all_tab_title']];
		}
		$format=textFormatsArray();
		if(fixDQ($this->objectProperty['sob_all_on_change'])!=''){
			$OnChange = 'onchange="' . fixDQ($this->objectProperty["sob_all_on_change"]) . '" ';
		}
		$this->CRLF                       = $CRLF;
		$this->TAB                        = $TAB;
		$hGap                             = 3;                                                   //gap between fields
		$vHeight                          = 23;                                                  //row height
		$vTitleHeight                     = ($this->objectProperty['sob_subform_title_height']*16);                                                  //row height
		$vTop                             = 10;                                                  //row height
		$sfDimensions                     = array();
		$fldDimensions                    = array();
		$t                                = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '".$this->objectProperty['zzsys_object_id']."' ORDER BY sob_all_column_number, sob_all_order_number");
		while($r                          = db_fetch_array($t)){
			$objectLength                 = $r['sob_'.$r['sob_all_type'].'_length'];                 // eg. text objects are 'sob_text_length'
			if ($r['sob_all_type']        == 'lookup'){
				$objectLength             = $r['sob_lookup_code_length'];                        // code length
				if($r['sob_lookup_no_description'] != '1'){
					$objectLength         = $objectLength + .5 + $r['sob_lookup_description_length']; // description length
				}
			}
			if ($r['sob_all_type']        == 'text'){
				if($format[$r['sob_text_format']]->type == 'date'){
					$objectLength                                   = $objectLength + 1.5;       // date button length
				}
	    	}
			if ($r['sob_all_type']        == 'file'){
				$objectLength                                       = 16.5;       // file upload length
	    	}
			$fldDimensions[$r['sob_all_name']]->id                  = $r['zzsys_object_id'];
			$fldDimensions[$r['sob_all_name']]->type                = $r['sob_all_type'];
			$fldDimensions[$r['sob_all_name']]->columnAlign         = 'left';
			if($r['sob_all_type'] == 'text'){
				$fldDimensions[$r['sob_all_name']]->columnAlign     = $r['sob_text_align'];
			}
			if($r['sob_all_type'] == 'display'){
				$fldDimensions[$r['sob_all_name']]->columnAlign     = $r['sob_display_align'];
			}
			$fldDimensions[$r['sob_all_name']]->columnTitle         = $r['sob_all_title'];
			$fldDimensions[$r['sob_all_name']]->column              = $r['sob_all_column_number'];
			$fldDimensions[$r['sob_all_name']]->leftCoordinate      = round($sfDimensions[$r['sob_all_column_number']]->columnWidth);
			$fldDimensions[$r['sob_all_name']]->columnWidth         = ($objectLength * 16) + $hGap;
			$sfDimensions[$r['sob_all_column_number']]->columnWidth = round($sfDimensions[$r['sob_all_column_number']]->columnWidth) + $fldDimensions[$r['sob_all_name']]->columnWidth;
			$colHeight                                              = round($sfDimensions[$r['sob_all_column_number']]->columnHeight);
			if($colHeight == 0){
				$sfDimensions[$r['sob_all_column_number']]->columnHeight = $vHeight;
				$colHeight                                               = $sfDimensions[$r['sob_all_column_number']]->columnHeight;
			}
			if($r['sob_all_type'] == 'textarea' AND $colHeight < $r['sob_textarea_height'] * 16){
				$sfDimensions[$r['sob_all_column_number']]->columnHeight = $r['sob_textarea_height'] * 16;
			}
		}
		$longest = '';

		foreach($sfDimensions as $key => $value){                                               // get longest row
			if($value->columnWidth > $longest){$longest                 = $value->columnWidth;}
			$rowHeight = $rowHeight + $value->columnHeight;
		}
		$prntCheckBox                                                   = $this->objectProperty['sob_subform_delete_box'] == '1' and $this->objectProperty['sob_subform_read_only'] != '1';

		if($prntCheckBox){                               //add room for the delete tick box
			$longest                                                    = $longest + $vHeight;
		}else{
			$longest                                                    = $longest + 20;
		}

//add scroll bar
		$sfWidth    = $longest + 20;
		$sfLeft     = $this->objectProperty['sob_subform_left'];
//add height of subform title + column title(s) to overall height
		$sfHeight   = ($this->objectProperty['sob_subform_height']*16) -($this->objectProperty['sob_subform_title_height']*16) -100;
		$sfTop      = $this->objectProperty['sob_subform_top'];

		if($this->objectProperty['sob_subform_width']==0){
			$scsfWidth  = $sfWidth + 10;
		}else{
			$scsfWidth  = $this->objectProperty['sob_subform_width'];
		}
		
		$scsfHeight = $sfHeight - 20;
		$scsfHeight = $sfHeight - 16;//changed from -10 to -16

		$s          =      "<div id='sf_title$fieldName' class='selected' dblclick='$moveit' style='text-align:left;position:absolute;height:20px;top:".$sfTop."px;left:".$sfLeft."px;width:".$sfWidth."px;'>$fieldTitle</div>$CRLF";
		$sfTop      = $sfTop + 20;
		$s          .= "$TAB<div id='$fieldName' class='selected' style='position:absolute;overflow:auto;width:".$scsfWidth."px;height:".$scsfHeight."px;top:".$sfTop."px;left:".$sfLeft."px;'>$CRLF";
		$s          .= "$TAB   <div id='title$fieldName' style='position:absolute;top:0px;left:0px;background:#6D7B8D'>$CRLF";

// build subform column titles
 		$columnTop            = 0;
 		$columnNumber         = '';
		foreach($fldDimensions as $key => $value){
			if($columnNumber != $value->column){
				if($columnNumber == ''){
					$columnNumber = $value->column;
				}
			}
			if($columnTop == 0){// only print headings for the first row (sob_subform_column_order)
				if($columnNumber == $value->column){
				$width    = $value->columnWidth;
        		$dbc      = '';
        	    if($this->parentForm->zzsys_user_id == 'globeadmin' and $this->parentForm->form->sys_setup != '1'){
        			$dbc    = " ondblclick=\"checkDbl$value->id(event)\"";
        			$s .= "$TAB      <script type='text/javascript'>$CRLF";
					$s .= "$TAB      function checkDbl$value->id(e){\n";
					$s .= "$TAB      	if (!e){\n";
					$s .= "$TAB      		var e = window.event;\n";
					$s .= "$TAB      	}\n";
					$s .= "$TAB      	var source = (window.event) ? e.srcElement : e.target;\n";
					$s .= "$TAB      	if (source.nodeName == 'DIV' || source.nodeName == 'div'){\n";
					$s .= "$TAB      		//openbrowse\n";
					$s .= "$TAB      		nuControlKey=true;openBrowse('object', '$objectID', '', '{$this->parentForm->session}', '');\n";
					$s .= "$TAB      	}else{\n";
					$s .= "$TAB      		//specific field name on the subform\n";
					$s .= "$TAB				nuControlKey=true;openForm('object','$value->id');\n";
					$s .= "$TAB      	}\n";
					$s .= "$TAB      }\n";
        			$s .= "$TAB      </script>$CRLF";
        	    }
				if(! isset($nextLeft)){$nextLeft = 0;} // make sure $subformFieldDiv->leftCoordinate is defined
				$s   .= "$TAB      <div $dbc class='unselected' style='vertical-align:top;font-size:10pt;font-family:tahoma;font-weight:bold;top:0px;left:".$nextLeft."px;width:".$width."px;height:".$vTitleHeight."px;overflow:hidden;position:absolute;text-align:".align($value->columnAlign).";'>$CRLF";
				$s  .= "$TAB         <span>$value->columnTitle$CRLF</span>";
				$s  .= "$TAB      </div>$CRLF";
						$nextLeft  = $value->leftCoordinate + $value->columnWidth;
				}
			}
		}

		$sfHeight = $sfHeight - $columnTop-$vHeight;                 //  adjusting for scrollng div
		$this->objectName  = $this->objectProperty['sob_all_name'];  //  set subform name
//add room for the delete tick box

		if($this->objectProperty['sob_subform_read_only'] != '1'){
			$s   .= "$TAB      <div class='unselected' style='top:0px;left:".$nextLeft."px;width:50px;height:".$vTitleHeight."px;overflow:hidden;position:absolute;text-align:left;'>$CRLF"; //align:left removed
			if($prntCheckBox){
				$s  .= "$TAB         <span style='vertical-align:top;font-size:10pt;font-family:tahoma;font-weight:bold;'>&nbsp;" . nuTranslate('Delete') . "&nbsp;</span>$CRLF";
			}
			$s  .= "$TAB      </div>$CRLF";
		}//end of subform column titles

//start scrolling div
		$sfHeight     = $sfHeight - $vTitleHeight; // adjust a bit to see all of scroll bar
		$columnTop    = $columnTop + $vTitleHeight;
		//added by nick 10-06-09 $grey needs to be defined before it can be used
		//vvvvv
		$grey                 = iif($grey==$this->objectProperty['sob_subform_odd_background_color'],$this->objectProperty['sob_subform_even_background_color'],$this->objectProperty['sob_subform_odd_background_color']);
		//^^^^^
		$subformClass = str_replace('.', '', $this->objectProperty['sob_all_class']);
		$s            .= "$TAB         <div class='$subformClass' id='scroller$fieldName' style='border-style:solid;border-width:2px;border-color:white;position:absolute;overflow:scroll;width:".$sfWidth."px;height:".$sfHeight."px;top:".$columnTop."px;left:0px;background:$grey;'>$CRLF";

//put subform objects in an array
		$subformObjects           = array();
		$t                        = nuRunQuery("SELECT * FROM zzsys_object WHERE sob_zzsys_form_id = '".$this->objectProperty['zzsys_object_id']."' ORDER BY sob_all_column_number, sob_all_order_number");
		while($r                  = db_fetch_object($t)){
			$subformObjects[]     = $r;
		}



//get SQL for subform //-- added by sc 4-feb-2009
		if(is_array($this->parentForm->recordValues)){
			$hVariables           = arrayToHashArray($this->parentForm->recordValues);                                               //--session values (access level and user etc. )
		}
		if($this->objectProperty['sob_all_clone'] == '0' and $this->parentForm->cloning == '1'){  //-- dont clone subform records
			$this->recordID = '-1';
		}
		$subformSQL               = replaceVariablesInString($this->TT,$this->objectProperty['sob_subform_sql'], $this->recordID);
		if(is_array($this->parentForm->recordValues)){
			$subformSQL           = replaceHashVariablesWithValues($hVariables, $subformSQL);
		}
//---------
		$subformTable             = nuRunQuery($subformSQL);
 		$columnTop                = (($vHeight)*-1)+5;
 		$nextTop                  = 0;
		$columnNumber             = '';

//loop through subform records
		if($this->parentForm->cloning == '1'){
			$primaryKey           = '';
		}else{
			$primaryKey           = $this->objectProperty['sob_subform_primary_key'];
		}
		while($subformRecord      = db_fetch_array($subformTable)){
			$this->recordID       = $subformRecord[$this->objectProperty['sob_subform_primary_key']];
			$this->nextRowNumber();
//loop through each object for this subform record
			$newRow               = true;
			$grey                 = iif($grey==$this->objectProperty['sob_subform_odd_background_color'],$this->objectProperty['sob_subform_even_background_color'],$this->objectProperty['sob_subform_odd_background_color']);
			$dq                   = '"';
			$s                    .= "$TAB               <div class='subform-row' id='rowdiv_$this->subformPrefix' style='background:$grey;height:".$rowHeight."px'>$CRLF";
			$checkBoxDone         = false;
			for($i = 0 ; $i < count($subformObjects) ; $i++){
				$subformFieldDiv  = $fldDimensions[$subformObjects[$i]->sob_all_name];
				if($columnNumber != $subformFieldDiv->column OR $i == 0){
					$columnNumber = $subformFieldDiv->column;
					$columnTop    = $nextTop;
					$nextTop      = $columnTop + $sfDimensions[$columnNumber]->columnHeight;
				}
//add room for the delete tick box
				if($prntCheckBox and !$checkBoxDone){
					$checkBoxDone = true;

					$s            .= "$TAB               <div style='position:absolute;top:".$columnTop."px;left:".$nextLeft."px'>$CRLF";
					$s            .= "$TAB                  <input name='row$this->subformPrefix' id='row$this->subformPrefix' type='checkbox' onFocus='nuSetRow(\"$this->subformPrefix\");' $OnChange tabindex='10000'>$CRLF";
					$s            .= "$TAB               </div>$CRLF";
				}
				$s                .= "$TAB               <div id='row$this->subformPrefix"."l$subformFieldDiv->leftCoordinate"."t$columnTop' style='position:absolute;top:".$columnTop."px;left:".$subformFieldDiv->leftCoordinate."px'>$CRLF";
				$fieldWidth       = $subformFieldDiv->columnWidth - $hGap;
				if($newRow){
					$s            .= "$TAB                  <input name='$this->subformPrefix$primaryKey' id='$this->subformPrefix$primaryKey' value='$this->recordID' type='hidden'>$CRLF";
					$newRow       = false;
				}
				$s                .= "$TAB                  ".$this->buildObject($subformObjects[$i],$subformRecord)."$CRLF";
				$s                .= "$TAB               </div>$CRLF";
			}
			$rowNumber            = $rowNumber + 1;
			$newRow               = true;
			$s                    .= "$TAB               </div>$CRLF";
		}

		$sfRowTotal               = $rowNumber + $blankRows;
		$columnNumber             = '';

//loop through blank subform records
		for($blankRecord          = 0 ;  $blankRecord < $blankRows ; $blankRecord++){
			$this->recordID       = '-1';
			$this->nextRowNumber();
//loop through each object for this subform record
			$grey                 = iif($grey==$this->objectProperty['sob_subform_odd_background_color'],$this->objectProperty['sob_subform_even_background_color'],$this->objectProperty['sob_subform_odd_background_color']);
//			$grey                 = iif($grey=='#E0E0E0 ','#F0F0F0','#E0E0E0 ');
			$s                    .= "$TAB               <div class='subform-row' id='rowdiv_$this->subformPrefix' style='background:$grey;height:".$rowHeight."px'>$CRLF";
			$checkBoxDone = false;
			for($i = 0 ; $i < count($subformObjects) ; $i++){
				$subformFieldDiv  = $fldDimensions[$subformObjects[$i]->sob_all_name];
				if($columnNumber != $subformFieldDiv->column){
					$columnNumber = $subformFieldDiv->column;
					$columnTop    = $nextTop;
					$nextTop      = $columnTop + $sfDimensions[$columnNumber]->columnHeight;
				}
//add room for the delete tick box
				if($prntCheckBox and !$checkBoxDone){
					$checkBoxDone = true;
					$s            .= "$TAB               <div style='position:absolute;top:".$columnTop."px;left:".$nextLeft."px'>$CRLF";
					$s            .= "$TAB                  <input name='row$this->subformPrefix' id='row$this->subformPrefix' type='checkbox' onFocus='nuSetRow(\"$this->subformPrefix\");' $OnChange tabindex='10000' checked='checked'>$CRLF";
					$s            .= "$TAB               </div>$CRLF";
				}
				$s                .= "$TAB               <div id='$this->subformPrefix"."l$subformFieldDiv->leftCoordinate"."t$columnTop' style='position:absolute;top:".$columnTop."px;left:".$subformFieldDiv->leftCoordinate."px'>$CRLF";
				$fieldWidth       = $subformFieldDiv->columnWidth - $hGap;
				$s                .= "$TAB                  ".$this->buildObject($subformObjects[$i],$subformRecord)."$CRLF";
				$s                .= "$TAB               </div>$CRLF";
			}
			$rowNumber            = $rowNumber + 1;
			$columnNumber         = '';
			$s                    .= "$TAB               </div>$CRLF";

		}

		$s                        .= "$TAB         </div>$CRLF";
		$s                        .= "$TAB      </div>$CRLF";
		$s                        .= "$TAB   </div>$CRLF";
		$s                        .= "$TAB   <div style='position:absolute;overflow:hidden;width:0px;height:0px;top:0px;left:10px;background:blue;'>$CRLF";
		$sfColumns                = count($fldDimensions);

		$s                        .= "$TAB      <input name='subformid$fieldName' id='subformid$fieldName' value='".$this->objectProperty['zzsys_object_id']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='rows$fieldName' id='rows$fieldName' value='$sfRowTotal' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='columns$fieldName' id='columns$fieldName' value='$sfColumns' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='table$fieldName' id='table$fieldName' value='".$this->objectProperty['sob_subform_table']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='foreignkey$fieldName' id='foreignkey$fieldName' value='".$this->objectProperty['sob_subform_foreign_key']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='primarykey$fieldName' id='primarykey$fieldName' value='".$this->objectProperty['sob_subform_primary_key']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='readonly$fieldName' id='readonly$fieldName' value='".$this->objectProperty['sob_subform_read_only']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='deletebox$fieldName' id='deletebox$fieldName' value='".$this->objectProperty['sob_subform_delete_box']."' type='hidden' >$CRLF";

		$s                        .= "$TAB      <input name='rowColor_$fieldName' id='rowColor_$fieldName' value='".$this->objectProperty['sob_subform_selected_row_color']."' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='lastRow_$fieldName' id='lastRow_$fieldName' value='' type='hidden' >$CRLF";
		$s                        .= "$TAB      <input name='lastColor_$fieldName' id='lastColor_$fieldName' value='' type='hidden' >$CRLF";



		$cNo                      = 0;
		foreach($fldDimensions as $key => $value){
			$s                    .= "$TAB      <input name='$fieldName$cNo' id='$fieldName$cNo' value='$key' type='hidden' >$CRLF";
			$cNo                  = $cNo + 1;
		}
		$s                        .= "$TAB</div>$CRLF";

		return $s;

	}

    private function buildObject($o,$r){

        $subformObject                    = new formObject($this->parentForm, $this->recordID);
		$subformObject->parentType        = 'subform';
		$subformObject->session           = $this->parentForm->session;
        $subformObject->formsessionID     = $this->parentForm->formsessionID;
        $subformObject->customDirectory   = $this->parentForm->customDirectory;

        $subformObject->setObjectProperties($o);
        //---decide whether to show or hide this object
        $subformObject->displayThisObject = displayCondition($this->parentForm->arrayOfHashVariables, $o->sob_all_display_condition);
        //---get the value and format it
        if($subformObject->objectProperty['sob_'.$subformObject->objectProperty['sob_all_type'].'_read_only'] != '1'){
    	    $GLOBALS['formValues'][]          = $this->subformPrefix.$subformObject->objectProperty['sob_all_name'];
        }
		if($o->sob_all_type == 'file'){
			$value = $r[$subformObject->objectProperty['sob_all_name'] . '_file_name'];
		}else{
			$value = $r[$subformObject->objectProperty['sob_all_name']];
		}
        $subformObject->objectValue       = $subformObject->setValue($this->recordID, $this->parentForm->cloning, $value);
        //---create the html string that will display it
		$subformObject->objectHtml        = $subformObject->buildObjectHTML($this->CRLF, $this->TAB.$this->TAB,$this->subformPrefix);
		return $subformObject->objectHtml;

    }

}



?>