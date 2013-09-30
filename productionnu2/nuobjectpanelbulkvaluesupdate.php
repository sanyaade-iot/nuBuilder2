<?php
    
    $dir                = $_GET['dir'];
    
    require_once("../$dir/database.php");
    require_once('common.php');
    
    // get str of where clause for all objects selected in listbox
    $whereSTR = " WHERE (";
    foreach($_POST['zzsys_object_listbox'] as $key => $value){
        $whereSTR .= "zzsys_object_id = '".$value."' OR ";
    }
    $whereSTR .= "1 = 2) AND sob_zzsys_form_id = '".$_POST['zzsys_form_id']."' ";
    
    $updSQL = "UPDATE zzsys_object SET ";
    if($_POST['sob_all_tab_title'] != '' && $_POST['sob_all_tab_title'] != NULL){
        $updSQL .= "sob_all_tab_title = '".mysql_real_escape_string($_POST['sob_all_tab_title'])."', ";
    }
    if($_POST['sob_all_tab_number'] != '' && $_POST['sob_all_tab_number'] != NULL){
        $updSQL .= "sob_all_tab_number = '".mysql_real_escape_string($_POST['sob_all_tab_number'])."', ";
    }
    if($_POST['sob_all_column_number'] != '' && $_POST['sob_all_column_number'] != NULL){
        $updSQL .= "sob_all_column_number = '".mysql_real_escape_string($_POST['sob_all_column_number'])."', ";
    }
    $updSQL .= "sob_zzsys_form_id = '".$_POST['zzsys_form_id']."' ";
    $updSQL .= $whereSTR;
    if(sizeof($_POST['zzsys_object_listbox'])){
        nuRunQuery($updSQL);
    }
    
    // print $updSQL;
    // die();
    header('Location: nuobjectpanelbulk.php?f='.$_POST['zzsys_form_id'].'&dir='.$dir);
    
?>