<?php
/*
** File:           runCronProcedure_nu2.php
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

	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

	$dir                             = $argv[1];
	$report                          = $argv[2];

	if (strpos($dir,"..") !== false)
		die;

	require_once("../$dir/database.php");
	require_once('common.php');

	$T                               = nuRunQuery("SELECT * FROM zzsys_activity WHERE sat_all_code = '$report'");
	$A                               = db_fetch_object($T);

	$GLOBALS['nuEvent'] = "(nuBuilder Procedure Code) of " . $A->sat_all_description . " : ";
	eval($A->sat_procedure_code);
	$GLOBALS['nuEvent'] = '';
?>
