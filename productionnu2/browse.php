<?php
/*
** File:           browse.php
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

$dir                             = $_GET['dir'];
$ses                             = $_GET['ses'];
$form_ses                        = $_GET['form_ses'];
$f                               = $_GET['f'];
$p                               = $_GET['p'];
$s                               = $_GET['s'];
$prefix                          = $_GET['prefix'];
$GLOBALS['microtime']            = microtime(true);
$GLOBALS['nuRunQuery']           = 0;
$_GET['fly']                     = (isset($_GET['fly']) ? $_GET['fly'] : '');
$_GET['inframe']                 = (isset($_GET['inframe']) ? $_GET['inframe'] : '');
$_GET['subform']                 = (isset($_GET['subform']) ? $_GET['subform'] : '');
$subform                         = $_GET['subform'];
$_GET['o']                       = (isset($_GET['o']) ? $_GET['o'] : '');
$_GET['d']                       = (isset($_GET['d']) ? $_GET['d'] : '');
$o                               = $_GET['o'];
$d                               = $_GET['d'];

//=============== changed to hold history====================================
		if(isset($_GET['historySearch'])){
			$_POST['search']         = base64_decode($_GET['historySearch']);
		}else{
			if(isset($_POST['search'])){
				$_POST['search']     = $_POST['search'];
			}else{
				if(isset($_GET['presearch'])){
					$_POST['search'] = $_GET['presearch'];
				}else{
					$_POST['search'] = '';
				}
			}
		}
//===========================================================================
$_POST['newsearch'] = (isset($_POST['newsearch']) ? $_POST['newsearch'] : '');
if($_POST['newsearch'] == '1'){$p = 1;}

if (strpos($dir,"..") !== false)
	die;

require_once("../$dir/database.php");
require_once('common.php');

$GLOBALS['fly_para'] = ($_GET['subform'] == '1' ? '2' : '');
if(passwordNeeded(getFormIDifObjectID($f))){

	if($_GET['inframe'] == '1'){
		print "\n\n<div title='Close Lookup' style='position:absolute;left:0px;top:0px;width:12px;height:17px;background-color:lightgrey;font-family:arial;font-size:15px' onclick='parent.window.nuControlKey=false;parent.window.toggleModalMode();parent.document.getElementById(\"nuIBrowse\").parentNode.removeChild(parent.document.getElementById(\"nuIBrowse\"));'>X</div>\n\n";
	}

	if(!continueSession()){
		print "<a href='../$dir'>" . nuTranslate('You have been logged out') . "</a>";
		return;
	}
	if(!accessableForm()){
		print nuTranslate('You do not have access');
		return;
	}
}

	$nuBrowse                      = new Browse();
	$nuBrowse->loadAfterConstruct($f, $p, $o, $d, $s, $prefix, $dir, $ses, $form_ses, $_SESSION['nu_user_id']); //-- $session->sss_zzsys_user_id);

	

function addJSFunction($pCode){

	global $nuBrowse;
	$nuBrowse->appendJSFunction($pCode);

}
	
$nuBrowse->execute();

$micTime =  microtime(true) - $GLOBALS['microtime'];

print "\n\n<!--built in $micTime seconds, " . $GLOBALS['nuRunQuery'] . " queries, thread id ".db_thread_id().", local time ".date("r").", ".$nuBrowse->SQL->SQL."-->";

nuRunQuery("DROP TABLE IF EXISTS `$nuBrowse->TT`");

class Browse{

    public  $Row                   = array();
    public  $Column                = array();
    public  $searchField           = array();
    public  $searchFor             = array();
    public  $form                  = array();
    public  $setup                 = array();
    public  $jsFunctions           = array();
    public  $arrayOfHashVariables  = array();
    public  $startTime             = 0;
    public  $CRLF                  = "\n";
    public  $TAB                   = '    ';
    public  $pageHeader            = '';
    public  $pageStatus            = '';
    public  $pageBody              = '';
    public  $pageFooter            = '';
    public  $SQL                   = null;
    public  $rowHeight             = 0;
    public  $pageRows              = 0;
	public  $pageWidth             = 0;
    public  $searchString          = '';
    public  $pageWhereClause       = '';
    public  $pageSQL               = '';
    public  $theFormID             = '';
    public  $oldFormID             = '';
    public  $PageNo                = 0;
    public  $orderBy               = '';
    public  $isDescending          = '';
    public  $isLookup              = '';
    public  $lookFor               = '';
    public  $TT                    = '';
    public  $rowPrefix             = '';
    public  $customDirectory       = '';
    public  $session               = '';
    public  $form_session          = '';
    public  $old_sql_string        = '';
    public  $new_sql_string        = '';
    public  $zzsys_user_id         = '';
	public  $BPid				   = 0;
	
    function loadAfterConstruct($theFormID, $thePageNumber, $theOrderBy, $isDescending, $theSearchString, $subformPrefix, $dir, $ses, $form_ses, $zzsys_user_id){

   	$this->TT                  = TT();           //---temp table name
	$this->rowPrefix           = $subformPrefix;
	$this->customDirectory     = $dir;
	$this->session             = $ses;
	$this->form_session        = $form_ses;
	$this->PageNo              = $thePageNumber;
	$this->orderBy             = $theOrderBy;
	$this->isDescending        = $isDescending;
	$this->oldFormID           = $theFormID;
	$this->theFormID           = $this->getFormID($theFormID);
	$this->searchString        = $theSearchString;
	$this->rowHeight           = 20;    //-- if no customisation
	$this->pageRows            = 25;    //-- if no customisation
	$this->pageWidth           = 968;   //-- if no customisation
	$this->startTime           = time();
	$this->setup               = $GLOBALS['nuSetup'];
	$this->getColumnInfo($this->theFormID);
	$this->form                = formFields($this->theFormID);
	
	$this->defaultJSfunctions();
	$this->zzsys_user_id       = $zzsys_user_id;
//----------create an array of hash variables that can be used in any "hashString" 
	$sesVariables                    = recordToHashArray('zzsys_session', 'zzsys_session_id', $ses);  //--session values (access level and user etc. )
	$sesVariables['#TT#']            = $this->TT;
	$sesVariables['#browseTable#']   = $this->TT;
	$sesVariables['#formSessionID#'] = $form_ses;
	$sesVariables['#formID#']        = $theFormID;               //--form id
	$sysVariables                    = sysVariablesToHashArray($form_ses);                            //--values in sysVariables from the calling lookup page
	$this->arrayOfHashVariables      = joinHashArrays($sysVariables, $sesVariables);                  //--join the arrays together

	$newFormArray                    = sysVariablesToHashArray($_GET['nuopener']);                 //-- id of opening form
	$this->arrayOfHashVariables      = joinHashArrays($this->arrayOfHashVariables, $newFormArray); //--join the arrays together
	$this->searchString              = replaceHashVariablesWithValues($this->arrayOfHashVariables, $this->searchString);
	$C   = '';
	$s   =      "function theBrowseFilter(){ $C";
	$s   = $s . "   return '" . urlencode($this->searchString) . "';$C";
	$s   = $s . "}$C";
	$this->appendJSfunction($s);

	//----------allow for custom code----------------------------------------------
	$browseTable                     = $this->TT;
	$GLOBALS['nuEvent'] = "(nuBuilder Before Browse) of " . $this->form->sfo_name . " : ";
	eval(replaceHashVariablesWithValues($this->arrayOfHashVariables, $this->form->sfo_custom_code_run_before_browse));
	$GLOBALS['nuEvent'] = '';

	$this->old_sql_string      = $this->form->sfo_sql;
	$this->new_sql_string      = replaceHashVariablesWithValues($this->arrayOfHashVariables, $this->form->sfo_sql);
	
	$s   =      "\n/*\n old : $this->old_sql_string \n*/\n";
	$s   = $s . "\n/*\n new : $this->new_sql_string \n*/\n";
	$this->appendJSfunction($s);
	
	$this->SQL                 = new sqlString($this->new_sql_string);
	$this->buildWhereClause($this->searchString);
	$this->SQL->setWhere($this->pageWhereClause);
	$this->SQL->setOrderBy($this->buildOrderBy());
	$this->pageBody            = $this->buildBody();
	$this->pageHeader          = $this->buildHeader();

}

private function getFormID($formID){
	
// get form_id from object_id (if $formID is not a form_id)
	$T                     = nuRunQuery("SELECT count(*) FROM zzsys_form WHERE zzsys_form_id = '$formID'");
	$R                     = db_fetch_row($T);
	if($R[0] == 1){ // is a form_id
		$this->isLookup    = false;
		return $formID;
	}
	$t                     = nuRunQuery("SELECT sob_lookup_zzsysform_id FROM zzsys_object WHERE zzsys_object_id = '$formID'");
	$r                     = db_fetch_object($t);
	$this->isLookup        = true;
	$this->lookFor         = $formID;  //---the field name of the form that this selection will be for
	return $r->sob_lookup_zzsysform_id;

}

private function getColumnInfo($formID){

// get properties for all the fields on the browse page
	$t                         = nuRunQuery("SELECT * FROM zzsys_browse WHERE sbr_zzsys_form_id = '$formID' ORDER BY sbr_order");
	while($r                   = db_fetch_object($t)){
		
		if($r->sbr_visible     == '1'){
			$this->Column[]    = $r;
		}
		if($r->sbr_searchable  == '1'){
			$this->searchField[]    = $r;
		}
	}
	
}

    public function buildOrderBy(){
    	
    	if($this->orderBy         == ''){
    		$s                     = $this->SQL->DorderBy;
    	}else{

			$s                     = ' ORDER BY ' . $this->Column[$this->orderBy]->sbr_sort;
			if($this->isDescending == '1'){
				$s                 = $s . ' DESC';
			}
    		
    	}
    	return $s;
    }

    public function buildWhereClause($pString){

		if($_GET['lookup_code'] != ''){  //--the code, if duplicates are found while using a lookup

			$lookupValue          = $_GET['lookup_code'];
			$lookupObject         = objectFields($this->oldFormID);
			$whereStart           = "WHERE ($lookupObject->sob_lookup_code_field = '$lookupValue') ";

			if($this->SQL->where == ''){
				$this->SQL->where = $whereStart;
			}else{
				$this->SQL->where = $whereStart . ' AND ' . substr($this->SQL->where, 6);
			}
			
		}
		$_POST['search']          = trim($_POST['search']);
		$_GET['presearch']        = (isset($_GET['presearch']) ? $_GET['presearch'] : '');
		if($pString == '' AND $_POST['search'] == '' AND $_GET['presearch'] == ''){
			$this->pageWhereClause = $this->SQL->where;
		} else {
			$dq = '"';
//=============== changed to hold history====================================
		if(isset($_GET['historySearch'])){
			$searchString         = base64_decode($_GET['historySearch']);
		}else{
			if(isset($_POST['search'])){
				$searchString     = $_POST['search'];
			}else{
				if(isset($_GET['presearch'])){
					$searchString = $_GET['presearch'];
				}else{
					$searchString = '';
				}
			}
		}
//===========================================================================
// add querystring search ('s') + user entry (POST)
			$string              = str_replace('\"', '"', $searchString). ' ' . $pString;
	    	$searchFor           = array();
	    	$explode             = array();
// put strings that are in quotes into an array
			$strings             = getArrayFromString($string,'"');
			for($i = 0 ; $i < count($strings) ; $i++){
				$searchFor[]     = $strings[$i];
			}
	
// remove strings that are in quotes from the original string
			for($i = 0 ; $i < count($searchFor) ; $i++){
				$string          = str_replace('"' . $searchFor[$i] . '"', ' ', $string);
			}

			$string          = str_replace('"', '\"', $string);
// put individual words or strings into an array
			$explode             = explode(' ', $string);		
			for($i = 0 ; $i < count($explode) ; $i++){
				if($explode[$i] != ''){
					$searchFor[] = $explode[$i];
				}
			}
	
		$this->searchFor     = reorderSearchStrings($searchFor);  //-- array of all the strings to search for
// build the start of the where clause
			$whereClause         = iif($this->SQL->where == '', ' WHERE (', $this->SQL->where . '  AND (');
			$AND                 = '';
//loop through all the words or phrases
			for($i = 0 ; $i < count($searchFor) ; $i++){
				if(substr_count($searchFor[$i], ' ') == 0 and substr($searchFor[$i],0,1) == '-'){
					$LIKE        = ' NOT LIKE ';
					$ANDOR       = ' AND ';
					$searchFor[$i] = substr($searchFor[$i],1);
				}else{
					$LIKE        = ' LIKE ';
					$ANDOR       = ' OR ';
				}
				$columnWhere     = '';
	
// loop through all the searchable fields
				for($ii = 0 ; $ii < count($this->searchField) ; $ii++){

					if($LIKE == ' NOT LIKE '){
						$removeNull = 'IF(ISNULL(' . formatSQLWhereCriteria($this->searchField[$ii]) . '),"",' . formatSQLWhereCriteria($this->searchField[$ii]) . ')';
					}else{
						$removeNull = formatSQLWhereCriteria($this->searchField[$ii]);
					}
					if($columnWhere == ''){
						$columnWhere = $columnWhere . " \n$AND (\n($removeNull $LIKE $dq%" . $searchFor[$i] . '%") ';
						$AND         = ') AND';
					}else{
						$columnWhere = $columnWhere . "\n" . " $ANDOR ($removeNull $LIKE $dq%" . $searchFor[$i] . '%") ';
					}
				}
				$whereClause             = $whereClause . $columnWhere;
			}
			$this->pageWhereClause       = $whereClause . '))';
		}
	}

    public function buildHeader(){

		$dq      = '"';
		$tempAr  = array(); 
//        $searchString = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['presearch']) ? $_GET['presearch'] : '');
//=============== changed to hold history====================================
		if(isset($_GET['historySearch'])){
			$searchString         = base64_decode($_GET['historySearch']);
		}else{
			if(isset($_POST['search'])){
				$searchString     = $_POST['search'];
			}else{
				if(isset($_GET['presearch'])){
					$searchString = $_GET['presearch'];
				}else{
					$searchString = '';
				}
			}
		}
//===========================================================================
		$searchTitle = nuTranslate('Search');


		$s       = "$this->CRLF<div id='actionButtons' class='nuSearch' style='position:absolute'  >$this->CRLF";
		$s       = $s . "<input name='newsearch' id='newsearch' type='hidden'> ";
		$s       = $s . "<input  accesskey='s' onchange=$dq document.getElementById('displayPage').value=1;changeAction();$dq ";
		$s       = $s . "class='searchcolor' name='search' id='search' ";
		$s       = $s . "value=\"".htmlspecialchars($searchString)."\">&nbsp;$this->CRLF";
		$s       = $s . "<input type='submit' class='actionButton' value='$searchTitle'>&nbsp;$this->CRLF";

		if($this->form->sfo_add_button    == '1'  and !hideFormButton('add') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_add_button_display_condition)){
			$title = iif($this->form->sfo_add_title == '',nuTranslate('Add Record'),$this->form->sfo_add_title);
			$nuwin = '';
			if($_GET['inframe'] == '1'){
				$nuwin = 'nuControlKey=true;';
			}
			$s     = $s . "<input type='button' class='actionButton' value='$title' accesskey='a' onclick='$nuwin addThis()'>&nbsp;$this->CRLF";
		}

//BrowsePrint button =================================================================================================================================================
		if($this->form->sfo_print_button    == '1'  and !hideFormButton('print') and displayCondition($this->arrayOfHashVariables, $this->form->sfo_print_button_display_condition)){
			$title = iif($this->form->sfo_print_title == '',nuTranslate('Print'),$this->form->sfo_print_title);
			$s     = $s . "<input type='button' class='actionButton' value='$title' accesskey='p' onclick='BrowsePrint($this->BPid)'/>&nbsp;$this->CRLF";
		}
	
//BrowsePrint button =================================================================================================================================================

		
		$s       = $s . "</div>$this->CRLF";
		return $s;

	}

    private function displayJavaScript(){

        print "$this->CRLF$this->CRLF<!-- Form Functions -->$this->CRLF";
		print makeCSS();
        print "<script type='text/javascript'>$this->CRLF";
        print "/* <![CDATA[ */$this->CRLF";
        print "isEditScreen  = false;\n";
        print "var nuLastTab = 0;\n";
        for($i=0;$i<count($this->jsFunctions);$i++){
            print $this->jsFunctions[$i];
            print "$this->CRLF$this->CRLF";
        }
        print "/* ]]> */ $this->CRLF";
        print "</script>$this->CRLF<!-- End Of Form Functions -->$this->CRLF$this->CRLF";

    }

    private function defaultJSfunctions(){
	
		$lookup_code  = '&lookup_code=' . $_GET['lookup_code'];  //--the code, if duplicates are found while using a lookup
		$lookup_code .= '&nuopener=' . $_GET['nuopener'];        //--form session of the openeing form
		$lookup_code .= '&inframe=' . $_GET['inframe'];          //--form session of the openeing form

		$fly_para     = $GLOBALS['fly_para'];

        $C   = $this->CRLF;
        $div = count($this->Column) + 1;

        $s   =      "function MIN(rw){//---mouse over menu$C";
        $s   = $s . "   if(rw == ''){return;} $C";
        $s   = $s . "   for(i = 0 ; i < $div ; i++){ $C";
        $s   = $s . "       $('#'+rw+i).removeClass('nuUnselectedRow').addClass('nuSelectedRow');$C";
        $s   = $s . "   } ;$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function MOUT(rw){//---mouse out menu$C";
        $s   = $s . "   if(rw == ''){return;} $C";
        $s   = $s . "   for(i = 0 ; i < $div ; i++){ $C";
        $s   = $s . "       $('#'+rw+i).removeClass('nuSelectedRow').addClass('nuUnselectedRow');$C";
        $s   = $s . "   } ;$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


   		if($this->form->sfo_access_without_login != '1'){
            $s   =      "self.setInterval('checknuC()', 1000) $C$C";
        }
        $s   = $s . "function checknuC(){ $C";
        $s   = $s . "   if(nuSingleWindow()){return;} $C";
        $s   = $s . "   if(nuReadCookie('nuC') == null){ $C";
        $s   = $s . "      pop = window.open('', '_parent');$C";
        $s   = $s . "      pop.close();$C";
        $s   = $s . "   }$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s    =     "function focusSearch() { $C";
		$s   .=     "   try { $C";
		$s   .=     "       if (document.hasFocus() || '" . $_GET['inframe'] . "' == '1') { $C";
		$s   .=     "           document.getElementById('search').focus();$C";
		$s   .=     "           //---Move the cursor to the end of the search input box by re-setting its value$C";
		$s   .=     "           document.getElementById('search').value = document.getElementById('search').value;$C";
		$s   .=     "       }$C";
		$s   .=     "   } catch (e) { $C";
		$s   .=     "       //---Do nothing$C";
		$s   .=     "   }$C";
		$s   .=     "}$C";
		$s   .=     "window.onfocus = focusSearch;$C";
        $this->appendJSfunction($s);
		
        $s   =      "function LoadThis(){//---load form$C";
        $s   = $s . "   if(window.nuLoadThis){;$C";
        $s   = $s . "      nuLoadThis();$C";
        $s   = $s . "   };$C";
		$s   .=     "   focusSearch();$C";
        $s   = $s . "};$C";
        $this->appendJSfunction($s);

        $s   =      "function getImage(pID){ $C"; 
        $s   = $s . "   return 'formimage.php?dir='+customDirectory()+'&iid='+pID; $C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function TIN(pthis){ $C";
        $s   = $s . "   document.getElementById(pthis.id).style.color='" . $this->setup->set_hover_color . "';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function TOUT(pthis){ $C";
        $s   = $s . "   document.getElementById(pthis.id).style.color='';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


		$SF  = $_GET['subform']; 
		$fly = $_GET['fly'];  //-- if == 1 it means its being added on the fly from a Lookup Browse Form, 2 it means its being added/edited on the fly from a Browse Subform
        $s   =  "function isNuSubform(){ $C";
        $s  .=  "   return '1' == '$SF'; $C";
        $s  .=  "}$C";
        $this->appendJSfunction($s);

		$noHashes  = str_replace("#", "%23", $this->searchString);
        $s   =      "function changeAction(){ $C";
        $s   = $s . "   document.forms[0].action = 'browse.php?x=1&s=$noHashes&subform=$SF&fly=$fly&dir=$this->customDirectory&form_ses=$this->form_session&ses=$this->session&prefix=$this->rowPrefix&f=$this->oldFormID$lookup_code&o=$this->orderBy&d=$this->isDescending" . addHistory() . "&p=1' + addHistorySearch();$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function runPage(pAdd){ $C";
        $s   = $s . "   var thePage = parseInt(document.getElementById('displayPage').value);$C";
        $s   = $s . "   thePage     = thePage + pAdd;$C";
        $s   = $s . "   if(thePage > parseInt(document.getElementById('nu_max_page_no').value)){ $C";
        $s   = $s . "      thePage = parseInt(document.getElementById('nu_max_page_no').value); $C";
        $s   = $s . "   }$C";
        $s   = $s . "   if(thePage < 1){thePage = 1;}$C";
        $s   = $s . "   if(pAdd   == 2){thePage = 100000000;}$C";
        $s   = $s . "   document.forms[0].action = 'browse.php?x=1&s=$noHashes&subform=$SF&fly=$fly&dir=$this->customDirectory&form_ses=$this->form_session&ses=$this->session&prefix=$this->rowPrefix&f=$this->oldFormID$lookup_code&o=$this->orderBy&d=$this->isDescending" . addHistory() . "&p='+thePage + addHistorySearch();$C";
        $s   = $s . "   document.forms[0].submit();$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


        $s   =      "function runOrder(pOrderNo, pDesc){ $C";
        $s   = $s . "   document.forms[0].action = 'browse.php?x=1&s=$noHashes&subform=$SF&fly=$fly&dir=$this->customDirectory&form_ses=$this->form_session&ses=$this->session&prefix=$this->rowPrefix&f=$this->oldFormID&p=1$lookup_code&o='+pOrderNo+'" . addHistory() . "&d=' + pDesc + addHistorySearch();$C";
        $s   = $s . "   document.forms[0].submit();$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function customDirectory(){ $C";
        $s   = $s . "   return '$this->customDirectory';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function form_session_id(){ $C";
        $s   = $s . "   return '$this->form_session';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function session_id(){ $C";
        $s   = $s . "   return '$this->session';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function doIt(pID){ $C";
        $s   = $s . "   if(pID == ''){return;} $C";
        if($this->isLookup){

//-----------hold control key and you can edit the lookup record------------------------------------------------------
			$s   = $s . "   if(nuControlKey){ $C";

			if($this->setup->set_single_window == '1'){ //-- stop accidental double clicking
				$s   = $s . "      if(!isFirstClick() && !isNuSubform()){ $C";
				$s   = $s . "         return; $C";
				$s   = $s . "      } $C"; 
			} 
			if($this->form->sfo_redirect_form_id == ''){  //---- this can open a different window for editing
				$s   = $s . "      openForm('$this->theFormID', pID,'$fly_para');$C";
			}else{
				$s   = $s . "      openForm('" . $this->form->sfo_redirect_form_id . "', pID,'$fly_para');$C";
			}

	        $s   = $s . "   }else{ $C";

	        $s   = $s . "      getRecordFromIframeList(pID);$C";

			
	        $s   = $s . "   } $C";
	        $s   = $s . "   nuControlKey = false; $C";
			
//--------------------------------------------------------------------------------------

			
        }else{
			if($this->setup->set_single_window == '1'){ //-- stop accidental double clicking
				$s   = $s . "   if(!isFirstClick() && !isNuSubform()){ $C";
				$s   = $s . "      return; $C";
				$s   = $s . "   } $C"; 
			} 
			if($this->form->sfo_redirect_form_id == ''){  //---- this can open a different window for editing
				$s   = $s . "   openForm('$this->theFormID',pID,'$fly_para');$C";
			}else{
				$s   = $s . "   openForm('" . $this->form->sfo_redirect_form_id . "',pID,'$fly_para');$C";
			}
        }
        $s   = $s . "   return true;$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

		
		$fly = '';
		if($_GET['inframe'] == '1'){
			$fly = '1';
		}else if($_GET['subform'] == '1'){
			$fly = '2';
		}
		
        $s   =      "function addThis(){ $C";
		if($this->form->sfo_redirect_form_id == ''){  //---- this can open a different window for editing
			$s   = $s . "   openForm('$this->theFormID','-1', '$fly', theBrowseFilter());$C";
		}else{
			$s   = $s . "   openForm('" . $this->form->sfo_redirect_form_id . "','-1', '$fly', theBrowseFilter());$C";
		}
        $s   = $s . "};$C";
        $this->appendJSfunction($s);

// BrowsePrint function - called when BrowsePrint Button is clicked - opens the report in a new window/tab
		$f   = getFormIDifObjectID($_GET['f']);  //--added by SC 7/1/12
        $s   =      "function BrowsePrint(BPid){ $C";
        $s   = $s . "   window.open('browseprint.php?id=' + BPid + '&f=$f&dir=" . $_GET['dir'] . "&ses=" . $_GET['ses'] . "','_blank'); $C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


        $s   =      "function frameLookFor(){ $C";
        $s   = $s . "   return '$this->lookFor';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


        $s   =      "function nuFrameLeft(){ $C";
        $s   = $s . "   return '" . getCSS('.nuLookupFrame', 'left') . "';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function nuFrameTop(){ $C";
        $s   = $s . "   return '" . getCSS('.nuLookupFrame', 'top') . "';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);

        $s   =      "function frameRowPrefix(){ $C";
        $s   = $s . "   return '$this->rowPrefix';$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


        $s   =      "function addHistorySearch(){ $C";
        $s   = $s . "   return '&historySearch=' + Base64.encode(document.getElementById('search').value);$C";
        $s   = $s . "}$C";
        $this->appendJSfunction($s);


		$s    = setWindowNavigation($this->setup->set_single_window);
        $this->appendJSfunction($s);  //-- add js for window nav.

	}

    public function appendJSfunction($pValue){
        $this->jsFunctions[]=$pValue;
    }

    private function displayHeader(){

		print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">$this->CRLF";
		print "<html>$this->CRLF";
		print "<head>$this->CRLF";
		$forceRefresh = uniqid('1');
		print "<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>$this->CRLF";
		print "<title>".$this->form->sfo_title."</title>$this->CRLF";
		jsinclude('jquery.js');
		jsinclude('common.js');
		jsinclude('nuCalendar.js');
		jsinclude('nuEmailForm.js');
		//-- the following loop is used to add extra javascript libraries  - like jquery.js
		$nuFileT = nuRunQuery("SELECT sli_option FROM zzsys_list WHERE sli_name = 'javascriptFile'");
		while($nuFileR = db_fetch_object($nuFileT)){
			jsinclude($nuFileR->sli_option);
		}
        
        cssinclude('css/core.css');
		$this->displayJavaScript();

		print "</head>$this->CRLF";
		if($_GET['inframe'] == '1'){
			print "<body onload='LoadThis()' onkeydown='keyDownEvent(event)' onkeyup='keyUpEvent(event)'  onmousemove='mouseMoveIF(event)'  onmouseup='mouseUpIF(event)' onmousedown='mouseDnIF(event)'>$this->CRLF";
		}else{
			print "<body onload='LoadThis()' onkeydown='keyDownEvent(event)' onkeyup='keyUpEvent(event)'>$this->CRLF";
		}
		
		print "<form name='thebrowse' id='thebrowse' action='' method='post'>$this->CRLF";
		if($_GET['inframe'] == '1'){
			print "\n\n<div title='Close Lookup' style='position:absolute;left:0px;top:0px;width:12px;height:17px;background-color:lightgrey;font-family:arial;font-size:15px' onclick='parent.window.nuControlKey=false;parent.window.toggleModalMode();parent.document.getElementById(\"nuIBrowse\").parentNode.removeChild(parent.document.getElementById(\"nuIBrowse\"));'>X</div>\n\n";
		}
		print $this->pageHeader;
    }

    private function buildFooter(){

        $build             = time()- $this->startTime;

        $this->pageFooter  = breadCrumbHTML($this->setup->set_single_window, $this->form->sfo_title);
        $this->pageFooter .= "$this->CRLF</form>$this->CRLF</body>$this->CRLF</html>$this->CRLF$this->CRLF";
		return $this->pageFooter;
    }


      public function buildBody(){

        $left                  = 0;
		$s                     = '';
//-------set row height
        $customHeight          = intval($this->form->sfo_row_height);
		if($customHeight != 0){
			$this->rowHeight   = $customHeight;
		}
//-------set rows per page
        $customRows            = intval($this->form->sfo_rows);
		if($customRows != 0){
			$this->pageRows    = $customRows;
		}
//-------set page width
        $customWidth           = intval(getCSS('.nuBrowse', 'width'));
		if($customWidth != 0){
			$this->pageWidth    = $customWidth;
		}

        $select                = '';
        $totalWidth            = 0;
        for($i                 = 0 ; $i < count($this->Column) ; $i++){
			$totalWidth        = $totalWidth + $this->Column[$i]->sbr_width;
        }
		if($_GET['subform']   != '1'){
			$totalWidth        = max($this->pageWidth, $totalWidth);
		}

		if($this->pageWidth < $totalWidth){  //--adds a margin to the right of the browse
            $padRight          =  "<div id='PadDiv' style='position:absolute;top:1px;width:1px;height:1px;left:" . ($totalWidth+20) . "px;' ></div>$CRLF";
		}
	    $this->SQL->removeAllFields();
			
			$brp_col		   = "";
			$heading           = '';
            for($i             = 0 ; $i < count($this->Column) ; $i++){
				$this->SQL->addField($this->Column[$i]->sbr_display);
				$w             = $this->Column[$i]->sbr_width;
				$brp_col	   = $brp_col . $this->Column[$i]->sbr_width . "," . $this->Column[$i]->sbr_align . "," . $this->Column[$i]->sbr_title . "," . $this->Column[$i]->sbr_format . "~";
				$a             = align($this->Column[$i]->sbr_align);
				$heading       = $heading . "$this->TAB<div class='nuBrowse' style='border-style:none;padding:0px;margin:4px 0px 4px 0px;overflow:hidden;position:absolute;text-align:$a;top:0px;left:$left"."px;width:$w"."px;height:25px'>$this->CRLF";
				$desc          = iif($i == $this->orderBy AND $this->isDescending == '', '1', '');
				$heading       = $heading . "$this->TAB$this->TAB<span style='border-style:none;padding:0px;cursor:pointer;' class='nuBrowse' id='title$i' onmouseout='TOUT(this)' onmouseover='TIN(this)' onclick='runOrder($i, \"$desc\")'>&nbsp;".$this->Column[$i]->sbr_title."&nbsp;</span>$this->CRLF";
				$heading       = $heading . "$this->TAB</div>$this->CRLF";
				$left          = $left + $w;
            }

//BrowsePrint add items to zzsys_trap =========================================================================================================================================
			$this->BPid = dbGetUniqueID('', 'zzsys_trap', 'zzsys_trap_id');
			$BPstr	 = "\$TRAP_sql='"   . str_hex(preg_replace('/(\s\s+|\t|\n)/', ' ', $this->SQL->SQL)) . "'; ";
			$BPstr	.= "\$TRAP_b4sql='" . str_hex(preg_replace('/(\s\s+|\t|\n)/', ' ', replaceHashVariablesWithValues($this->arrayOfHashVariables, $this->form->sfo_custom_code_run_before_browse))) . "'; ";
			$BPstr	.= "\$TRAP_col='"   . str_hex($brp_col) . "'; ";
			$BPstr	.= "\$TRAP_cnt='"   . count($this->Column) . "'; ";
			$BPstr	.= "\$TRAP_tmp='"   . $this->TT . "'; ";
			nurunquery("UPDATE zzsys_trap SET tra_message = '" . db_real_escape_string($BPstr) . "' ,sys_added = '" . date("Y-m-d H:i:s") . "' WHERE zzsys_trap_id = " . $this->BPid); 
//BrowsePrint add items to zzsys_trap =========================================================================================================================================
			$bHeight           = (($this->pageRows * $this->rowHeight) + 50) . 'px';
			$bWidth            = $totalWidth . 'px';
			
            $s                 = $s . "<div id='browse' class='nuBrowse' style='position:absolute;height:$bHeight;width:$bWidth'>$this->CRLF". $heading;
			$s      	       = $s . "$this->TAB<div class='nuBrowse' style='border-style:none;overflow:hidden;position:absolute;text-align:$a;top:0px;left:$left"."px;width:3000px;height:25px'></div>$this->CRLF";

			if($this->PageNo  == 100000000){ //--get last page
				$t             = nuRunQuery($this->SQL->SQL);
				$this->PageNo  = ceil(db_num_rows($t) / $this->pageRows);
			}

			
            $page              = ($this->PageNo -1) * $this->pageRows;
            $this->SQL->addField($this->form->sfo_primary_key);
            $primaryKeyNumber  = count($this->SQL->fields)-1;
            $t                 = nuRunQuery($this->SQL->SQL);
			$totalPages        = ceil(db_num_rows($t) / $this->pageRows);
            $top               = 20 - $this->rowHeight;
            $row               = 0;
            for($browseRow = 0 ; $browseRow < $page ; $browseRow ++){
				  $r           = db_fetch_row($t);
			}

//          while($r           = db_fetch_row($t)){
            for($browseRow = 0 ; $browseRow < $this->pageRows ; $browseRow ++){
				  $r           = db_fetch_row($t);
            	  $theID       = '"' . $r[$primaryKeyNumber] . '"';
                  $rowname     = 'rw'.substr('0'.$row,-2);
                  $param       = '"'.$rowname.'"';
				  $cur         = 'cursor:pointer';
				  if($r[$primaryKeyNumber] == ''){
     				  $cur     = '';
					$param     = '""';
				  }
                  $left        = 0;
                  $top         = $top + $this->rowHeight;
                  for($i       = 0 ; $i < count($this->Column) ; $i++){
                        $w     = $this->Column[$i]->sbr_width;
                        $a     = align($this->Column[$i]->sbr_align);
                        $s     = $s . "$this->TAB<div onmouseover='MIN($param)' onmouseout='MOUT($param)' onclick='doIt($theID)' class='nuUnselectedRow' id='$rowname$i' style='position:absolute;overflow:hidden;$cur;text-align:$a;top:$top"."px;left:$left"."px;width:$w"."px;height:$this->rowHeight"."px'>";
						$fText = formatTextValue($r[$i], $this->Column[$i]->sbr_format);
						$fText = highlightSearch($fText, $this->searchFor);
                        $s     = $s . $fText . "&nbsp;</div>$this->CRLF";
                        $left  = $left + $w;
                  }
                  $w           = iif($left < $this->pageWidth, $this->pageWidth - $left,0);
                  $s           = $s . "$this->TAB<div onmouseover='MIN($param)' onmouseout='MOUT($param)' onclick='doIt($theID)' class='nuUnselectedRow' id='$rowname$i' style='position:absolute;overflow:hidden;cursor:pointer;top:$top"."px;left:$left"."px;width:$w"."px;height:$this->rowHeight"."px'>$this->CRLF";
                  $s           = $s . "$this->TAB</div>$this->CRLF";
// allow keyboad movement                  
                  $s           = $s . "$this->TAB<div onmouseover='MIN($param)' onmouseout='MOUT($param)' class='nuUnselectedRow' id='hidden$rowname$i' style='position:absolute;overflow:hidden;top:$top"."px;left:$left"."px;width:0px;height:$this->rowHeight"."px'>$this->CRLF";
                  $s           = $s . "$this->TAB<input onfocus='MIN($param)' onblur='MOUT($param)' >$this->CRLF";
                  $s           = $s . "$this->TAB</div>$this->CRLF";

                  $row         = $row + 1;
            }
//--- add blank rows at end of table rows			
            $s                 = $s . $this->displaynuPage($top + $this->rowHeight, $totalWidth, $totalPages);
            $s                 = $s . '</div><div style="position:absolute;top:'.$top.'px;width:1px;height:100px"></div>';
            
        return $s;

    }

    private function displaynuPage($pTop, $pWidth, $pPages){

		$CRLF   = $this->CRLF;
		$brdw   = intval(getCSS('.nuBrowse', 'border-width'));
		$bw     = $pWidth . 'px';
		$bl     = (getCSS('.nuBrowse', 'left') + $brdw) . 'px';
		$btop   = intval(getCSS('.nuBrowse', 'top')) + $brdw + 50;
		$stop   = (($this->pageRows* $this->rowHeight) + 26) . 'px';

		
        $s  = "$CRLF<div id='BorderDivB' class='nuBrowse' style='border-style:none;border-width:0px;position:absolute;height:30px;top:$stop;left:0px;width:$bw;margin:0px;padding:0px'>$CRLF$CRLF";
        $s .= "<table cellspacing='0px' cellpadding='0px' class='nuBrowse' style='border-style:none;border-width:0px;height:20px;width:100%;vertical-align:center;margin:0px;'>$CRLF<tr cellspacing='0px' cellpadding='0px' >$CRLF";
		$s .= "<td cellspacing='0px' cellpadding='0px' class='nuBrowse' style='border-width:0px;height:20px;width:33%;text-align:left;vertical-align:center;'>";
		if($_SESSION['nu_access_level'] == 'globeadmin' and $this->form->sys_setup != '1'){
			$s .= "<span id='loggedin' ondblclick=\"nuControlKey=true;openForm('form', '{$this->form->zzsys_form_id}')\">&nbsp;" . $this->form->sfo_title . "</span>";
		}else{
			$s .= "<span id='loggedin'>&nbsp;". $this->form->sfo_title ."</span>";
		}

		$s .= "</td>$CRLF";
		$s .= "<td cellspacing='0px' cellpadding='0px' class='nuBrowse' style='border-style:none;border-width:0px;height:20px;width:33%;text-align:center;vertical-align:center;'>";
		$s .= "<span onclick='runPage(-1)' style='border-style:none;cursor:pointer;' class='nuBrowse'>&lt;&nbsp;</span>\n";
		$s .= "<span class='nuBrowse' style='border-style:none'>" . nuTranslate('Page') . " <input onchange='runPage(0)' style='margin:0px;padding:0px;border-style:none;text-align:center;width:40px;height:15px;font-size:14px' name='displayPage' id='displayPage' value='$this->PageNo'> / $pPages</span>\n";
		$s .= "<span onclick='runPage(1)' style='border-style:none;cursor:pointer;' class='nuBrowse'>&nbsp;&gt;</span>\n";
		$s .= "</td>$this->CRLF";

		$t = nuRunQuery("SELECT * FROM zzsys_user WHERE zzsys_user_id = '$this->zzsys_user_id'");
		$r = db_fetch_object($t);
        $s .= "<td class='nuBrowse' style='border-width:0px;height:20px;text-align:right;border-style:solid;width:30%'>";
		$r->sus_login_name      = (isset($r->sus_login_name) ? $r->sus_login_name : '');
		if($r->sus_login_name == ''){$r->sus_login_name = 'globeadmin';}
		
		if($this->form->sfo_help != ''){
			$help = "title='help' onclick=\"openHelp('".$this->form->zzsys_form_id."')\"";
			$s .= "<span $help >($r->sus_login_name)&nbsp;|&nbsp;" . nuTranslate('Help') . "</span>";
		}else{
			$help = "";
			$s .= "<span $help >($r->sus_login_name)&nbsp;</span>";
		}
        $s .= "</td>$CRLF</tr>$CRLF</table>$CRLF$CRLF";
        $s .= "<input type='hidden' value='$pPages' id='nu_max_page_no'>$CRLF";
        $s .= "</div>$CRLF$CRLF";

        return $s;

	}
    

    
      public function displayBody(){
        print $this->pageBody;
		
    }

      public function displayStatus(){
        print $this->pageStatus;
    }

    
      public function displayFooter(){
        print $this->pageFooter;
    }

      public function execute(){

        $this->displayHeader();
        $this->displayBody();
		$this->displayStatus();
        $this->buildFooter();
        $this->displayFooter();
   		nuRunQuery("DROP TABLE IF EXISTS $this->TT");

    }

}




function formatSQLWhereCriteria($pBrowseObject){

	if($pBrowseObject->sbr_format == ''){
		return $pBrowseObject->sbr_display;
	}else{
		//-- get number and date format array
		$sFormat = textFormatsArray();
		return str_replace("??", $pBrowseObject->sbr_display, $sFormat[$pBrowseObject->sbr_format]->sql);
	}

}


function reorderSearchStrings($pArray){

	$tt     = TT();
	$sorted = array();
	$sql    = "CREATE TABLE  `$tt` (theid INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,";
	$sql   .= "thestring VARCHAR(255) NOT NULL ,thelength INT, INDEX (thelength))";
	nuRunQuery($sql);

	for($i = 0 ; $i < count($pArray) ; $i++){
		nuRunQuery("INSERT INTO `$tt` (thestring, thelength) VALUES ('" .  db_real_escape_string($pArray[$i]) . "', '" . strlen($pArray[$i]) . "')");
	}
	
	$t      = nuRunQuery("SELECT DISTINCT thestring FROM `$tt` ORDER BY thelength");

	while($r  = db_fetch_row($t)){
		$sorted[] = $r[0];
	}
	
	nuRunQuery("DROP TABLE `$tt`");

	return $sorted;

}



function highlightSearch($pString, $pSearchArray){

	for($i = 0 ; $i < count($pSearchArray) ; $i++){
		if($pSearchArray[$i] != '`'){
			$pString  = str_ireplace ($pSearchArray[$i], "`````" . $pSearchArray[$i] . '````', $pString);
		}
	}

		$pString  = str_ireplace ('`````', "<span class='browsematch'>", $pString);
		$pString  = str_ireplace ('````',  "</span>", $pString);
	
	return $pString;
}


?>
