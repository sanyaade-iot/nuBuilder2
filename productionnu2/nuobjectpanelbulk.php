<?php
    
    $dir = $_GET['dir'];
    if (strpos($dir,"..") !== false)
        die;

    require_once("../$dir/database.php");
    require_once('common.php');
    
    // Header Info
    $nuFormPanelMainHTML = "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
<head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>
<link type='text/css' href='nuobjectpanel.css' rel='stylesheet' />
<script type='text/javascript' src='jquery.js'></script>
<script type='text/javascript'>

$(document).ready(function(){
    $('#zzsys_form_id').val('".$_GET['f']."');
    loadFormValues($('#zzsys_form_id').val());
});

function loadFormValues(formID){
    if(formID != ''){
        $.ajax({
            url: 'nuobjectpanelbulkvaluesget.php', 
            data: {zzsys_form_id: formID, dir : '".$dir."'}, 
            cache: false,
            success: function(json){
                // looping through the form objects
                // clear object listbox
                document.getElementById('zzsys_object_listbox').length = 0;
                for(x in json){
                    if(json.zzsys_form_id) $('#zzsys_form_id').val(json.zzsys_form_id);
                    if(json[x].optionDescription && json[x].zzsys_object_id){
                        var objectOption = document.createElement('option');
                        objectOption.text = json[x].optionDescription;
                        objectOption.value = json[x].zzsys_object_id;
                        objectOption.selected = true;
                        document.getElementById('zzsys_object_listbox').options.add(objectOption);
                    }
                }
            }
        });
        document.getElementById('objectPanelLink').href = 'nuobjectpanel.php?f='+$('#zzsys_form_id').val()+'&dir=".$dir."&objname=';
    }
}

function submitForm(){
    if(!confirm('Are you sure you want to bulk update these fields?')){
        return false;
    }
    document.getElementById('formForm').submit();
}

</script>
</head>
<body>
";
    
    // Start building panel
    $nuFormPanelMainHTML .= "
    <a id='objectPanelLink' name='objectPanelLink' class='link' href='nuobjectpanel.php?f=".$_GET['f']."&dir=".$dir."&objname='>Back To Object Panel</a><br /><br />
    <form id='formForm' name='formForm' action='nuobjectpanelbulkvaluesupdate.php?dir=".$dir."' method='post' >
        <table>
            <tr>
                <td colSpan='2'>
                    <input id='bulkUpdateBtn' name='bulkUpdateBtn' type='button' value='Update Objects' onclick='submitForm()' />
                </td>
            </tr>
            <tr>
                <td class='heading' colSpan='2'>
                    Form: 
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <select id='zzsys_form_id' name='zzsys_form_id' style='width:450px;' onchange='loadFormValues(this.value)'>
                        <option value=''></option>
";
                        // be able to change object to any form
                        $formQRY = nuRunQuery("
                            SELECT zzsys_form_id AS theid, CONCAT(sfo_name, ' (FORM)') as thename FROM zzsys_form ORDER BY thename
                        ");
                        while($formOBJ = mysql_fetch_object($formQRY)){
                            $nuFormPanelMainHTML .= "                        <option value='".mysql_real_escape_string($formOBJ->theid)."'>".mysql_real_escape_string($formOBJ->thename)."</option>
";
                        }
$nuFormPanelMainHTML .= "                </td>
            </tr>
            <tr>
                <td class='heading' colSpan='2'>
                    <br />
                    Objects To Update: 
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <select id='zzsys_object_listbox' name='zzsys_object_listbox[]' style='width:450px; height:300px;' multiple='multiple'>
                        <option value=''></option>
";
                        // be able to change object to any form
                        $objectQRY = nuRunQuery("
                            SELECT zzsys_object_id AS theid, CONCAT( IFNULL( sob_all_tab_title,  '' ) , ' - ', IFNULL( sob_all_tab_number,  '' ),  ' - ', IFNULL( sob_all_type,  '' ) ,  ' - ', IFNULL( sob_all_name,  '' ) ,  ' - ', IFNULL( sob_all_title,  '' ) ) as thename FROM zzsys_object WHERE sob_zzsys_form_id = '".$_GET['f']."'
                            ORDER BY CASE WHEN SUBSTRING(thename,1,3) = '(S)' THEN 2 ELSE 1 END, sob_all_tab_number, sob_all_column_number, sob_all_order_number, thename
                        ");
                        while($objectOBJ = mysql_fetch_object($objectQRY)){
                            $nuFormPanelMainHTML .= "                        <option value='".mysql_real_escape_string($objectOBJ->theid)."' selected>".mysql_real_escape_string($objectOBJ->thename)."</option>
";
                        }
$nuFormPanelMainHTML .= "                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Tab Title:
                </td>
                <td>
                    <input id='sob_all_tab_title' name='sob_all_tab_title' style='width:240px;' type='text' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Tab Number:
                </td>
                <td>
                    <input id='sob_all_tab_number' name='sob_all_tab_number' style='width:50px;text-align: right;' type='text' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Column Number:
                </td>
                <td>
                    <input id='sob_all_column_number' name='sob_all_column_number' style='width:50px;text-align: right;' type='text' />
                </td>
            </tr>
        </table>
    </form>
    </body>
</html>
    ";
    print $nuFormPanelMainHTML;
    
?>