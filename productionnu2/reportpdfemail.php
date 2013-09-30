<?php
/*
** File:           reportpdfemail.php
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
require_once("config.php");
require_once("common.php");

$report_url 	= $_GET['report_url'];
$x 		= $_GET['x'];
$dir		= $_GET['dir'];
$ses		= $_GET['ses'];
$form_ses	= $_GET['form_ses'];
$r		= $_GET['r'];

$to		= $_GET['to'];
$from		= $_GET['from'];
$subject	= $_GET['subject'];
$message	= $_GET['message'];
$filename	= $_GET['filename'];

//put url together
$fqurl		= $report_url."?x=".$x."&dir=".$dir."&ses=".$ses."&form_ses=".$form_ses."&r=".$r;

//create file on server
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
$pfile = dirname(__FILE__)."/temppdf/".$emailID.date("YmdHis").".pdf";
$fp = fopen($pfile,"w");
$fwritetest=fwrite($fp,$content);
fclose($fp); 

$ok=PDF_sendEmail($to, $from, '', $message, $html = true, $subject, $wordWrap = 120, $pfile, $filename.".PDF");
@unlink($pfile);
nuCreateCookie("emailPDF", $form_ses);

function PDF_sendEmail($to, $from = "noreply@nubuilder.com", $fromname, $content = "nuBuilder Email", $html = false, $subject = "", $wordWrap = 120, $filesource, $filename = "attachedFile.PDF") {
	// use nuSendEmail($to, $replyto = "", $content = "nuBuilder Email", $html = false, $subject = "", $wordWrap = 120, $filelist, $receipt=false, $replytoname="", $toname="")
	// function contained in emaillib.php
	nuSendEmail($to, $from, $content, $html, $subject, $wordWrap, array($filename => $filesource), false, $fromname);
}

?>
