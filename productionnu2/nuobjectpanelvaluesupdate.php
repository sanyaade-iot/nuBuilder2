<?php
    
    $dir                = $_GET['dir'];
    
    require_once("../$dir/database.php");
    require_once('common.php');
    
    $updSQL = "UPDATE zzsys_object SET ";
    $updSQL .= "sob_zzsys_form_id = '".mysql_real_escape_string($_POST['sob_zzsys_form_id'])."', ";
    $updSQL .= "sob_all_type = '".mysql_real_escape_string($_POST['sob_all_type'])."', ";
    $updSQL .= "sob_all_title = '".mysql_real_escape_string($_POST['sob_all_title'])."', ";
    $updSQL .= "sob_all_name = '".mysql_real_escape_string($_POST['sob_all_name'])."', ";
    $updSQL .= "sob_all_tab_title = '".mysql_real_escape_string($_POST['sob_all_tab_title'])."', ";
    $updSQL .= "sob_all_tab_number = '".mysql_real_escape_string($_POST['sob_all_tab_number'])."', ";
    $updSQL .= "sob_all_column_number = '".mysql_real_escape_string($_POST['sob_all_column_number'])."', ";
    $updSQL .= "sob_all_order_number = '".mysql_real_escape_string($_POST['sob_all_order_number'])."', ";
    $updSQL .= "sob_text_format = '".mysql_real_escape_string($_POST['sob_text_format'])."', ";
    $updSQL .= "sob_text_length = '".mysql_real_escape_string($_POST['sob_text_length'])."', ";
    $updSQL .= "sob_textarea_length = '".mysql_real_escape_string($_POST['sob_textarea_length'])."', ";
    $updSQL .= "sob_textarea_height = '".mysql_real_escape_string($_POST['sob_textarea_height'])."', ";
    $updSQL .= "sob_button_length = '".mysql_real_escape_string($_POST['sob_button_length'])."', ";
    $updSQL .= "sob_button_left = '".mysql_real_escape_string($_POST['sob_button_left'])."', ";
    $updSQL .= "sob_button_top = '".mysql_real_escape_string($_POST['sob_button_top'])."', ";
    $updSQL .= "sob_lookup_code_length = '".mysql_real_escape_string($_POST['sob_lookup_code_length'])."', ";
    $updSQL .= "sob_lookup_description_length = '".mysql_real_escape_string($_POST['sob_lookup_description_length'])."', ";
    $updSQL .= "sob_dropdown_length = '".mysql_real_escape_string($_POST['sob_dropdown_length'])."', ";
    $updSQL .= "sob_display_length = '".mysql_real_escape_string($_POST['sob_display_length'])."', ";
    $updSQL .= "sob_inarray_length = '".mysql_real_escape_string($_POST['sob_inarray_length'])."', ";
    $updSQL .= "sob_subform_left = '".mysql_real_escape_string($_POST['sob_subform_left'])."', ";
    $updSQL .= "sob_subform_top = '".mysql_real_escape_string($_POST['sob_subform_top'])."', ";
    $updSQL .= "sob_subform_height = '".mysql_real_escape_string($_POST['sob_subform_height'])."', ";
    $updSQL .= "sob_subform_width = '".mysql_real_escape_string($_POST['sob_subform_width'])."', ";
    $updSQL .= "sob_subform_title_height = '".mysql_real_escape_string($_POST['sob_subform_title_height'])."', ";
    $updSQL .= "sob_subform_blank_rows = '".mysql_real_escape_string($_POST['sob_subform_blank_rows'])."' ";
    $updSQL .= "WHERE zzsys_object_id = '".mysql_real_escape_string($_POST['zzsys_object_id'])."' ";
    nuRunQuery($updSQL);
    
    //print $updSQL;
    header('Location: nuobjectpanel.php?f='.$_POST['sob_zzsys_form_id'].'&dir='.$dir.'&objname='.$_POST['sob_all_name']);
    
?>