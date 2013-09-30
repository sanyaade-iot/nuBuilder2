<?php
    
    $dir = $_GET['dir'];
    if (strpos($dir,"..") !== false)
        die;

    require_once("../$dir/database.php");
    require_once('common.php');
    
    $objNameID = '';
    if($_GET['objname']){
        $objNameQRY = nuRunQuery("
            SELECT zzsys_object_id FROM zzsys_object WHERE sob_all_name = '".$_GET['objname']."' AND sob_zzsys_form_id = '".$_GET['f']."'
        ");
        $objNameID = mysql_fetch_object($objNameQRY)->zzsys_object_id;
    }
    
    // copied this code from form.php
    // should be the width coefficient for styles of text objects etc...
    if(stripos($GLOBALS['HTTP_USER_AGENT'], 'iPad') === false){  //--not running in an iPad
        $offset        = 16;
    }else{
        $offset        = 13;
    }
    
    // Header Info
    $nuObjectPanelMainHTML = "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
<head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>
<link type='text/css' href='nuobjectpanel.css' rel='stylesheet' />
<script type='text/javascript' src='jquery.js'></script>
<script type='text/javascript'>

$(document).ready(function(){
    $('#zzsys_object_id').val('".$objNameID."');
    loadObjectValues($('#zzsys_object_id').val());
    if($('#sob_zzsys_form_id').val() == '' && '".$_GET['f']."' != ''){
        $('#sob_zzsys_form_id').val('".$_GET['f']."')
    }
});

function loadObjectValues(objectID){
    if(objectID != ''){
        $.ajax({
            url: 'nuobjectpanelvaluesget.php', 
            data: {zzsys_object_id: objectID, dir : '".$dir."'}, 
            cache: false,
            success: function(json){
                // loop through the posts here
                if(json.sob_zzsys_form_id) $('#sob_zzsys_form_id').val(json.sob_zzsys_form_id);
                if(json.sob_all_type) $('#sob_all_type').val(json.sob_all_type);
                if(json.sob_all_title) $('#sob_all_title').val(json.sob_all_title);
                if(json.sob_all_name) $('#sob_all_name').val(json.sob_all_name);
                if(json.sob_all_tab_title) $('#sob_all_tab_title').val(json.sob_all_tab_title);
                if(json.sob_all_tab_number) $('#sob_all_tab_number').val(json.sob_all_tab_number);
                if(json.sob_all_column_number) $('#sob_all_column_number').val(json.sob_all_column_number);
                if(json.sob_all_order_number) $('#sob_all_order_number').val(json.sob_all_order_number);
                if(json.sob_text_format) $('#sob_text_format').val(json.sob_text_format);
                if(json.sob_text_length) $('#sob_text_length').val(json.sob_text_length);
                if(json.sob_textarea_length) $('#sob_textarea_length').val(json.sob_textarea_length);
                if(json.sob_textarea_height) $('#sob_textarea_height').val(json.sob_textarea_height);
                if(json.sob_button_length) $('#sob_button_length').val(json.sob_button_length);
                if(json.sob_button_left) $('#sob_button_left').val(json.sob_button_left);
                if(json.sob_button_top) $('#sob_button_top').val(json.sob_button_top);
                if(json.sob_lookup_code_length) $('#sob_lookup_code_length').val(json.sob_lookup_code_length);
                if(json.sob_lookup_description_length) $('#sob_lookup_description_length').val(json.sob_lookup_description_length);
                if(json.sob_dropdown_length) $('#sob_dropdown_length').val(json.sob_dropdown_length);
                if(json.sob_display_length) $('#sob_display_length').val(json.sob_display_length);
                if(json.sob_inarray_length) $('#sob_inarray_length').val(json.sob_inarray_length);
                if(json.sob_subform_left) $('#sob_subform_left').val(json.sob_subform_left);
                if(json.sob_subform_top) $('#sob_subform_top').val(json.sob_subform_top);
                if(json.sob_subform_height) $('#sob_subform_height').val(json.sob_subform_height);
                if(json.sob_subform_width) $('#sob_subform_width').val(json.sob_subform_width);
                if(json.sob_subform_title_height) $('#sob_subform_title_height').val(json.sob_subform_title_height);
                if(json.sob_subform_blank_rows) $('#sob_subform_blank_rows').val(json.sob_subform_blank_rows);
            }
        });
    }
}

function adjustObjectOnForm(object){
    objectType      = $('#sob_all_type').val();
    objectName      = object.name;
    objectValue     = object.value;
    formObjectName  = $('#sob_all_name').val();
    
    // return for now if its a subform
    if($('#sob_zzsys_form_id option:selected').text().substr(-9) == '(SUBFORM)'){
        return false;
    }
    
    switch(objectType){
        case 'text':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_text_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
            }
            break;
        case 'button':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName).value = objectValue;
            }
            if(objectName == 'sob_button_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
            }
            // if button has a top coordinate
            if($('#sob_button_top').val() != '0' && $('#sob_button_top').val() != ''){
                window.parent.document.getElementById(formObjectName).style.top = $('#sob_button_top').val()+'px';
                window.parent.document.getElementById(formObjectName).style.left = $('#sob_button_left').val()+'px';
                window.parent.document.getElementById(formObjectName).style.position = 'absolute';
            }
            break;
        case 'lookup':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_lookup_code_length'){
                window.parent.document.getElementById('code'+formObjectName).style.width = objectValue*".$offset."+'px';
            }
            if(objectName == 'sob_lookup_description_length'){
                window.parent.document.getElementById('description'+formObjectName).style.width = objectValue*".$offset."+'px';
            }
            break;
        case 'dropdown':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_dropdown_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
            }
            break;
        case 'words':
            
            break;
        case 'subform':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_subform_left'){
                window.parent.document.getElementById('sf_title'+$('#sob_all_name').val()).style.left = objectValue+'px';
                window.parent.document.getElementById($('#sob_all_name').val()).style.left = objectValue+'px';
            }
            if(objectName == 'sob_subform_top'){
                window.parent.document.getElementById('sf_title'+$('#sob_all_name').val()).style.top = objectValue+'px';
                window.parent.document.getElementById($('#sob_all_name').val()).style.top = (Number(objectValue)+20)+'px';
            }
            if(objectName == 'sob_subform_height'){
                // code copied from form.php
                // might help to keep it arranged how it is so that its easier to see when form.php gets changed what to do...
                sfHeight = (Number(objectValue)*16) -(Number($('#sob_subform_title_height').val())*16) - 100;
                scsfHeight = sfHeight - 20;
                scsfHeight = sfHeight - 16;
                vHeight = 23;
                columnTop = 0;
                sfHeight = sfHeight - columnTop - vHeight;
                sfHeight = sfHeight  - (Number($('#sob_subform_title_height').val())*16);
                window.parent.document.getElementById($('#sob_all_name').val()).style.height = scsfHeight+'px';
                window.parent.document.getElementById('scroller'+$('#sob_all_name').val()).style.height = sfHeight+'px';
            }
            if(objectName == 'sob_subform_width'){
                window.parent.document.getElementById($('#sob_all_name').val()).style.width = Number(objectValue)+'px';
            }
            break;
        case 'inarray':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_inarray_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
            }
            break;
        case 'display':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_display_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
            }
            break;
        case 'textarea':
            if(objectName == 'sob_all_title'){
                window.parent.document.getElementById(formObjectName+'_title').innerHTML = objectValue;
            }
            if(objectName == 'sob_textarea_height'){
                // shouldn't be less than 2
                if(Number(objectValue) < 2){
                    objectValue = 2;
                    object.value = 2;
                }
                window.parent.document.getElementById(formObjectName).rows = Math.round(Number(objectValue-1));
            }
            if(objectName == 'sob_textarea_length'){
                window.parent.document.getElementById(formObjectName).style.width = objectValue*".$offset."+'px';
                window.parent.document.getElementById(formObjectName).cols = Math.ceil(Number(objectValue));
            }
            break;
        default:
            
    }
}

// function that opens the nuFormPanel
function nuObjectPanelBulkUpdate(){
    if($('#sob_zzsys_form_id').val() != ''){
        formID = $('#sob_zzsys_form_id').val();
    } else {
        formID = '".$_GET['f']."';
    }
    window.location = 'nuobjectpanelbulk.php?f='+formID+'&dir=".$_GET['dir']."';
}

</script>
</head>
<body>
";
    
    // Start building panel
    $nuObjectPanelMainHTML .= "
    <form name='objectForm' action='nuobjectpanelvaluesupdate.php?dir=".$dir."' method='post'>
        <table>
            <tr>
                <td colSpan='2'>
                    <input type='submit' value='Save' class='savebutton' />
                </td>
            </tr>
            <tr>
                <td class='title' colSpan='2'>
                    Option To Edit:
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <select id='zzsys_object_id' name='zzsys_object_id' style='width:450px;' onchange='loadObjectValues(this.value)'> 
                        <option value=''></option>
";
                    // find all objects for this form
                    $objQRY = nuRunQuery("
                        SELECT zzsys_object_id, CONCAT( IFNULL( sob_all_tab_title,  '' ) ,  ' - ', IFNULL( sob_all_type,  '' ) ,  ' - ', IFNULL( sob_all_name,  '' ) ,  ' - ', IFNULL( sob_all_title,  '' ) ) AS optionDescription, sob_all_tab_number AS tab, sob_all_column_number AS col, sob_all_order_number AS ord
                        FROM zzsys_object
                        WHERE sob_zzsys_form_id =  '".$_GET['f']."'
                        UNION 
                        SELECT subformObjects.zzsys_object_id, CONCAT(  '(S) ', IFNULL(subform.sob_all_name,''), ' - ', IFNULL( subformObjects.sob_all_tab_title,  '' ) ,  ' - ', IFNULL( subformObjects.sob_all_type,  '' ) ,  ' - ', IFNULL( subformObjects.sob_all_name,  '' ) ,  ' - ', IFNULL( subformObjects.sob_all_title,  '' ) ) AS optionDescription, CONCAT(subform.sob_all_title,subformObjects.sob_all_tab_number) AS tab, subformObjects.sob_all_column_number AS col, subformObjects.sob_all_order_number AS ord
                        FROM zzsys_object AS subformObjects
                        INNER JOIN zzsys_object AS subform ON subformObjects.sob_zzsys_form_id = subform.zzsys_object_id
                        AND subform.sob_zzsys_form_id =  '".$_GET['f']."'
                        ORDER BY CASE WHEN SUBSTRING(optionDescription,1,3) = '(S)' THEN 2 ELSE 1 END, tab, col, ord, optionDescription
                    ");
                    while($objOBJ = mysql_fetch_object($objQRY)){
                        $nuObjectPanelMainHTML .= "                        <option value='".mysql_real_escape_string($objOBJ->zzsys_object_id)."'>".mysql_real_escape_string($objOBJ->optionDescription)."</option>
";
                    }
$nuObjectPanelMainHTML .= "                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <input id='nuObjectPanelBulkBtn' name='nuObjectPanelBulkBtn' type='button' value='Bulk Object Update Panel' onclick='nuObjectPanelBulkUpdate()' />
                    <br />
                </td>
            </tr>
            <tr>
                <td class='title' colSpan='2'>
                    <br />
                    All
                </td>
            </tr>
            <tr>
                <td class='heading' colSpan='2'>
                    Displayed On: 
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <select id='sob_zzsys_form_id' name='sob_zzsys_form_id' style='width:450px;' />
                        <option value=''></option>
";
                        // be able to change object to any form
                        $formQRY = nuRunQuery("
                            SELECT * FROM
                            (SELECT zzsys_form_id AS theid, CONCAT(sfo_name, ' (FORM)') as thename FROM zzsys_form
                            UNION
                            SELECT zzsys_object_id AS theid, CONCAT(sfo_name, ' (FORM) ', sob_all_name, ' (SUBFORM)') AS thename
                            FROM zzsys_object INNER JOIN zzsys_form ON sob_zzsys_form_id = zzsys_form_id 
                            WHERE sob_all_type = 'subform') AS forms ORDER BY thename
                        ");
                        while($formOBJ = mysql_fetch_object($formQRY)){
                            $nuObjectPanelMainHTML .= "                        <option value='".mysql_real_escape_string($formOBJ->theid)."'>".mysql_real_escape_string($formOBJ->thename)."</option>
";
                        }
$nuObjectPanelMainHTML .= "                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Type: 
                </td>
                <td>
                    <select id='sob_all_type' name='sob_all_type' style='width:150px;'>
                        <option value=''></option>
";
                        // be able to change object to any form
                        $typeQRY = nuRunQuery("
                            SELECT sli_option, sli_description FROM  zzsys_list  WHERE sli_name = 'fieldtype'
                        ");
                        while($typeOBJ = mysql_fetch_object($typeQRY)){
                            $nuObjectPanelMainHTML .= "                        <option value='".mysql_real_escape_string($typeOBJ->sli_option)."'>".mysql_real_escape_string($typeOBJ->sli_description)."</option>
";
                        }
$nuObjectPanelMainHTML .= "                </td>
            </tr> 
            <tr>
                <td class='heading'>
                    Title: 
                </td>
                <td>
                    <input id='sob_all_title' name='sob_all_title' style='width:240px;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Field Name: 
                </td>
                <td>
                    <input id='sob_all_name' name='sob_all_name' style='width:240px;' type='text' />
                </td>
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
            <tr>
                <td class='heading'>
                    Order Number: 
                </td>
                <td>
                    <input id='sob_all_order_number' name='sob_all_order_number' style='width:50px;text-align: right;' type='text' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Text Format: 
                </td>
                <td>
                    <select id='sob_text_format' name='sob_text_format' style='width:150px;'>
                        <option value=''></option>
";
                        // be able to change object to any form
                        $typeQRY = nuRunQuery("
                            SELECT sli_option, sli_description FROM  zzsys_list  WHERE sli_name = 'nuformat'
                        ");
                        while($typeOBJ = mysql_fetch_object($typeQRY)){
                            $nuObjectPanelMainHTML .= "                        <option value='".mysql_real_escape_string($typeOBJ->sli_option)."'>".mysql_real_escape_string($typeOBJ->sli_description)."</option>
";
                        }
$nuObjectPanelMainHTML .= "                </td>
            </tr> 
            <tr>
                <td class='title' colSpan='2'>
                    <br />
                    Lengths
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Text Length: 
                </td>
                <td>
                    <input id='sob_text_length' name='sob_text_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this)' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Textarea Length: 
                </td>
                <td>
                    <input id='sob_textarea_length' name='sob_textarea_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Textarea Height: 
                </td>
                <td>
                    <input id='sob_textarea_height' name='sob_textarea_height' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Button Length: 
                </td>
                <td>
                    <input id='sob_button_length' name='sob_button_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Button Left: 
                </td>
                <td>
                    <input id='sob_button_left' name='sob_button_left' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Button Top: 
                </td>
                <td>
                    <input id='sob_button_top' name='sob_button_top' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    LU Code Length: 
                </td>
                <td>
                    <input id='sob_lookup_code_length' name='sob_lookup_code_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    LU Desc Length: 
                </td>
                <td>
                    <input id='sob_lookup_description_length' name='sob_lookup_description_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Dropdown Length: 
                </td>
                <td>
                    <input id='sob_dropdown_length' name='sob_dropdown_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Display Length: 
                </td>
                <td>
                    <input id='sob_display_length' name='sob_display_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    Inarray Length: 
                </td>
                <td>
                    <input id='sob_inarray_length' name='sob_inarray_length' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Left: 
                </td>
                <td>
                    <input id='sob_subform_left' name='sob_subform_left' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Top: 
                </td>
                <td>
                    <input id='sob_subform_top' name='sob_subform_top' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Height: 
                </td>
                <td>
                    <input id='sob_subform_height' name='sob_subform_height' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Width: 
                </td>
                <td>
                    <input id='sob_subform_width' name='sob_subform_width' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Title Height: 
                </td>
                <td>
                    <input id='sob_subform_title_height' name='sob_subform_title_height' style='width:50px;text-align: right;' type='text' onchange='adjustObjectOnForm(this);' />
                </td>
            </tr>
            <tr>
                <td class='heading'>
                    SF Blank Rows: 
                </td>
                <td>
                    <input id='sob_subform_blank_rows' name='sob_subform_blank_rows' style='width:50px;text-align: right;' type='text' />
                </td>
            </tr>
        </table>
    </form>
    </body>
</html>
    ";
    print $nuObjectPanelMainHTML;
    
?>