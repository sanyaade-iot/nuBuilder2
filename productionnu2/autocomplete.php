<?php
/*
** File:           autocomplete.php
** Author:         nuSoftware
** Created:        2011/09/27
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

    $dir          = $_GET['dir'];
    $form_ses     = $_GET['form_ses'];
    $ses          = $_GET['ses'];
    $prefix       = $_GET['p'];
    $o            = $_GET['o'];
    $r            = $_GET['r'];
    $term         = $_GET['term'];

    if (strpos($dir,"..") !== false)
        die;
    
    require_once("../$dir/database.php");
    require_once('common.php');

    $response = array();
    $response['SUCCESS'] = true;
    $response['ERRORS'] = array();
    $response['DATA'] = array('results'=>array());
    
    if ($term != "") {
        
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
		$GLOBALS['nuEvent'] = "(nuBuilder Before Browse) of $lookupForm->sfo_name : ";
        //----------allow for custom code----------------------------------------------
        eval(replaceHashVariablesWithValues($arrayOfHashVariables, $lookupForm->sfo_custom_code_run_before_browse));
		$GLOBALS['nuEvent'] = '';

        $lookIn                          =  $object->sob_lookup_code_field;
        $lookInDescription               =  $object->sob_lookup_description_field;
        $idField                         =  $object->sob_lookup_id_field;
        $searchTerms                     = explode(' ',preg_replace('/\s+/',' ',$term));
        $fieldNames                      = array();
        
        $t                               = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$object->sob_lookup_zzsysform_id'");
        $form                            = db_fetch_object($t);
        $old_sql_string                  = $form->sfo_sql;
        $new_sql_string                  = replaceHashVariablesWithValues($arrayOfHashVariables, $old_sql_string);
        $SQL                             = new sqlString($new_sql_string);
        
        foreach ($searchTerms as $searchTerm) {
            $searchTerm = db_real_escape_string($searchTerm);
            if($SQL->where == ''){
                $SQL->setWhere("WHERE ($lookIn LIKE '$searchTerm%' OR $lookInDescription LIKE '%$searchTerm%')");
            }else{
                $SQL->setWhere("$SQL->where AND ($lookIn LIKE '$searchTerm%' OR $lookInDescription LIKE '%$searchTerm%')");		
            }
        }

        $SQL->removeAllFields();
        $SQL->addField(" $lookIn AS code ");
        $SQL->addField(" $idField AS id ");
        if ($lookIn != $lookInDescription) $SQL->addField(" $lookInDescription AS name ");
        $SQL->setOrderBy(" ORDER BY code ASC, name ASC ");
        
        $T                                 = nuRunQuery($SQL->SQL . ' LIMIT 20');
        
        foreach($searchTerms as $key => $st) {
            $searchTerms[$key] = '/'.preg_replace('/\//','//',$st).'/i';
        }
        $response['DEBUG']['searchtermsregex'] = $searchTerms;
        while ($obj = db_fetch_object($T)) {
            $obj->code = preg_replace($searchTerms,"<span class='browsematch'>$0</span>",htmlspecialchars($obj->code));
            $obj->name = preg_replace($searchTerms,"<span class='browsematch'>$0</span>",htmlspecialchars($obj->name));
            $response['DATA']['results'][] = $obj;
        }
        
        if (!count($response['DATA']['results'])) {
            $emptyResponse                 = new StdClass();
            $emptyResponse->code           = 'No match found for "'.htmlspecialchars($term).'"';
            $emptyResponse->name           = 'Click to search';
            $emptyResponse->id             = '';
            $response['DATA']['results'][] = $emptyResponse;
        }
        
        if ($_SESSION['nu_access_level'] == 'globeadmin') $response['DEBUG']['sql'] = $SQL->SQL;
    }

    // Cache drop-downs for five minutes (not all browsers honour this)
    // Due to form ses will only apply on current form
    $expires = 60*5;
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header("Cache-Control: private",false);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

    echo json_encode($response);
    
?>