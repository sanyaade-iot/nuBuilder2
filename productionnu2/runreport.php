<?php
/*
** File:           runreport.php
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

	$dir = $_GET['dir'];
	
	if (strpos($dir,"..") !== false)
		die;

	require_once("../$dir/database.php");
	require_once('common.php');

	$T      = nuRunQuery("SELECT sat_report_display_code FROM zzsys_activity WHERE sat_all_code = '" . $_GET['r'] . "'");
	$A      = db_fetch_object($T);
	eval($A->sat_report_display_code);
	$report = new Reporting();
	$ver    = $report->Version;

	if($ver == '3'){
		include('run_report_html_v3.php');
	}else if($ver == '2'){
		include('run_report_html_v2.php');
	}else{
		include('run_report_html_v1.php');
	}

	run_html_report();
	
	for($i = 0 ; $i < count($GLOBALS['table']) ; $i++){
		nuRunQuery('DROP TABLE IF EXISTS ' . $GLOBALS['table'][$i]);
	}
	$GLOBALS['table']                   = array();

	
?>
