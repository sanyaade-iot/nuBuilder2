<?php
/*
** File:           reportemail.php
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
require_once("config.php");
require_once("emaillib.php");
require_once("../".$_GET['dir']."/database.php");
require_once("common.php");

if (strtoupper($_GET['reporttype']) == 'PDF') {
	$toRun = "runpdf.php";
	$ext   = ".PDF";
} else {
	$toRun = "runreport.php";
        $ext   = ".HTM";
}

$report_url     = $_GET['report_url'].$toRun;
$x              = $_GET['x'];
$dir            = $_GET['dir'];
$ses            = $_GET['ses'];
$form_ses       = $_GET['form_ses'];
$r              = $_GET['r'];

$to             = (empty($_GET['to'])) ? ('error@nusoftware.com.au') : ($_GET['to']);
$replyto        = (empty($_GET['replyto'])) ? ('noreply@nusoftware.com.au') : ($_GET['replyto']);
$receipt        = (!empty($_GET['receipt'])) ? (($_GET['receipt'] == "true") ? true : false) : false;
// BEGIN - 2009/05/29 - Michael
// Added urldecode to these GET variables.
$subject        = (!empty($_GET['subject']))  ? urldecode($_GET['subject'])  : 'nuBuilder Report';
$message        = (!empty($_GET['message']))  ? urldecode($_GET['message'])  : 'Please save the attached file to the desktop before opening.';
$filename       = (!empty($_GET['filename'])) ? urldecode($_GET['filename']) : 'attached';
// END - 2009/05/29 - Michael

//put url together
$fqurl          = $report_url."?x=".$x."&dir=".$dir."&ses=".$ses."&form_ses=".$form_ses."&r=".$r."&thisauth=4887aa210c4f420080724070105&emailer=1";

//create file on server
$pfile = getReportFile($fqurl, $ext);

// BEGIN - 2009/05/29 - Michael
	// Check if there was an error getting the report file.
	// sendResponse terminates the script.
if (!$pfile)
	sendResponse("report_error");
// END - Michael

//send email
// BEGIN - 2009/05/29
	// Check if there was an error sending the email.
if (!nuSendEmail($to, $replyto, $message, $html = true, $subject, $wordWrap = 120, array($filename.$ext => $pfile), $receipt))
	sendResponse("email_error");
// END - 2009/05/29 - Michael

//delete file
@unlink($pfile);

// 2009/05/29 - Michael
sendResponse($form_ses);
//set cookie

function getReportFile($fqurl, $ext) {
// BEGIN - 2009/06/10 - Michael
// Changed the code that gets the report's content from fopen to CURL.
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fqurl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch);
	curl_close($ch);
// END - 2009/06/10 - Michael
	$emailID = uniqid();
	$pfile = dirname(__FILE__)."/temppdf/".$emailID.date("YmdHis").$ext;
	$fp = @fopen($pfile,"w");
// BEGIN - 2009/05/29 - Michael
		// Make sure we could open our temp file.
	if (!$fp)
		return NULL;
	// Make sure we could write to our temp file.
	if (!@fwrite($fp,$content))
	{
			// We need to close the file because we did open it.
			fclose($fp);
		return NULL;
	} // if
// END - 2009/05/29
	fclose($fp);
        return $pfile;
}

// 2009/05/29 - Michael
// sendResponse()
//
// Sets the "emailREPORT" cookie
function sendResponse($cookie_value)
{
	nuCreateCookie("emailREPORT", $cookie_value);
	echo <<<EOHTML
			<html>
			<head>
				<script type='text/javascript'>
				window.parent.emailSendResponse();
				</script>
			</head>
			</html>
EOHTML;
	die;
} // func

?>
