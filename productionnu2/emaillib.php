<?php
/*
** File:           emaillib.php
** Author:         nuSoftware
** Created:        2009/12/09
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

if (isset($_GET['dir'])) {$dir = $_GET['dir'];}
if (isset($_POST['dir'])) {$dir = $_POST['dir'];}
if (isset($GLOBALS['dir'])) {$dir = $GLOBALS['dir'];}
if (isset($dir)) { require_once("../" . $dir . "/database.php"); } else { Die("Unable to find path to database.php"); }

require_once("phpmailer/class.phpmailer.php");
require_once("config.php");
require_once("common.php");

function nuSendEmail($to, $replyto = "", $content = "nuBuilder Email", $html = false, $subject = "", $wordWrap = 120, $filelist, $receipt=false, $replytoname="", $toname="") {

		$error = 0; 
		$errorText = "";
		// Read SMTP AUTH Settings from zzsys_setup table
		$setup = nuSetup();
		if (!empty($setup->set_smtp_username)) 		{ $SMTPuser = trim($setup->set_smtp_username);} 	else { $error += 1; $errorText .= "SMTP Username not set.\n";}
		if (!empty($setup->set_smtp_password)) 		{ $SMTPpass = trim($setup->set_smtp_password);} 	else { $error += 1; $errorText .= "SMTP Password not set.\n";}
		if (!empty($setup->set_smtp_host)) 			{ $SMTPhost = trim($setup->set_smtp_host);} 		else { $error += 1; $errorText .= "SMTP Host not set.\n";}
		if (!empty($setup->set_smtp_from_address)) 	{ $SMTPfrom = trim($setup->set_smtp_from_address);}	else { $error += 1; $errorText .= "SMTP From Address not set.\n";}
		if (!empty($setup->set_smtp_port)) 			{ $SMTPport = intval($setup->set_smtp_port);} 		else { $error += 1; $errorText .= "SMTP PORT not set.\n";}
		if (!empty($setup->set_smtp_use_ssl)) 		{ $SMTPauth = (intval($setup->set_smtp_use_ssl) == 1) ? true : false;} else { $SMTPauth = false;}
		if (!empty($setup->set_smtp_from_name)) 	{ $SMTPname = trim($setup->set_smtp_from_name);}	else { $SMTPname = "nuBuilder";}
		
		if ($error > 0) {
			Die("Unable to send SMTP Email, the following error(s) occured:\n" . $errorText);
		}
		
        $mail = new PHPMailer();
		$mail->IsSMTP();
        $mail->Host     	= $SMTPhost;
        $mail->Port     	= $SMTPport;
        $mail->SMTPSecure 	= $SMTPauth ? 'ssl' : '';
        $mail->SMTPAuth 	= $SMTPauth;
        if ($SMTPauth)
			{
            $mail->Username = $SMTPuser;
            $mail->Password = $SMTPpass;
			}

		if($receipt) { $mail->ConfirmReadingTo = $replyto; }
	
		$mail->From = $SMTPfrom;
		$mail->FromName = $SMTPname;
		
		if(empty($replyto)){
			$mail->AddReplyTo($SMTPfrom,'');
		}else{
			$mail->AddReplyTo($replyto, $replytoname);
		}
	
		$tonameArray = explode(',',$toname);
		$toArray = explode(',',$to);
		for($i = 0; $i < count($toArray); $i++){
			if($toArray[$i]){
				if (isset($tonameArray[$i])) { 
					$thisToName = $tonameArray[$i]; 
				} else { 
					$thisToName = "";
				}
				$mail->AddAddress($toArray[$i], $thisToName);
			}
		}
	
		$mail->WordWrap = $wordWrap;
		$mail->IsHTML($html);
		if(isset($filelist)) {
			foreach($filelist as $filename=>$filesource) {
				$mail->AddAttachment($filesource,$filename);
			}
		}
		
		$mail->Subject = $subject;
		$mail->Body    = $content;

		return $mail->Send();
}

?>