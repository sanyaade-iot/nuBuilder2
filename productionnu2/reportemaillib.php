<?php
/*
** File:           reportemaillib.php
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
require_once("emaillib.php");

function sendReportEmail($to, $from = "noreply@nubuilder.com", $content = "nuBuilder Email", $html = false, $subject = "", $wordWrap = 120, $filesource, $filename) {
	// use nuSendEmail($to, $replyto = "", $content = "nuBuilder Email", $html = false, $subject = "", $wordWrap = 120, $filelist, $receipt=false, $replytoname="", $toname="")
	// function contained in emaillib.php
	nuSendEmail($to, $from, $content, $html, $subject, $wordWrap, array($filename => $filesource), false);
}

function getReportFile($fqurl, $ext) {

	$url = fopen($fqurl, "r");

	if(!$url) {
		die("File not found.");
	}

	$content = '';
	while (!feof($url)) {
  		$content .= fread($url, 8192);
	}
	ob_flush();
	$emailID = uniqid();
	$pfile = dirname(__FILE__)."/temppdf/".$emailID.date("YmdHis").$ext;
	$fp = fopen($pfile,"w");
	$fwritetest=fwrite($fp,$content);
	fclose($fp);
	
	return $pfile;
}


?>
