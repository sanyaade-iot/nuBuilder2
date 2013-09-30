<?php
/*
** File:           formsetvalues.php
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
require_once('../' . $_POST['dir'] . '/database.php');
require_once('common.php');

$type                   = $_POST['type'];
$name                   = $_POST['name'];
$id						= $_POST['id'];
$values                   = $_POST['var'];
//$variable_string        = urldecode(str_replace('+', ' ', $_POST['var']));
//$values                 = explode('YX__XZ', $variable_string);  //---exploding leaves a blank at the beginning

if($type      == 'listbox'){
	setnuList($id, nuDateAddDays(Today(),2), $name, $values);
}elseif($type == 'lookup'){
	$name = substr($name,4);
	setnuVariable($id, nuDateAddDays(Today(),2), $name, $values[0]);
	setnuVariable($id, nuDateAddDays(Today(),2), 'code'.$name, $values[1]);
	setnuVariable($id, nuDateAddDays(Today(),2), 'description'.$name, $values[2]);
}else{
	setnuVariable($id, nuDateAddDays(Today(),2), $name, $values[0]);
}
		
		
?>
