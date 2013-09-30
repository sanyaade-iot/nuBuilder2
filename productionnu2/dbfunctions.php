<?php
/*
** File:           dbfunctions.php
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

require_once("config.php");

function dbGetUniqueID($pID, $pTable, $pPrimaryKey){
		
	$pTable       = trim($pTable);
	$pPrimaryKey  = trim($pPrimaryKey);
	

	if($pID == '-1' or $pID == ''){             //-create new record

		if(dbIsAutoID($pTable, $pPrimaryKey)){  //-- create auto id
			$db   = nuRunQuery(''); // returns $db[0]=$DBHost; $db[1]=$DBName; $db[2]=$DBUserID; $db[3]=$DBPassWord;
			$link = mysql_connect($db[0], $db[2], $db[3]);
			if (!$link) { die('Could not connect: ' . mysql_error());}
			mysql_select_db($db[1]);
			mysql_query("INSERT INTO `$pTable` (`$pPrimaryKey`) VALUES (NULL)");
			return mysql_insert_id();
		}else{                                //-- create string id
			$id   = uniqid('1');
			nuRunQuery("INSERT INTO `$pTable` (`$pPrimaryKey`) VALUES ('$id')");
			return $id;
		}
	}else{                                    //-- return current record id
		return $pID;
	}

}

function dbIsAutoID($pTable, $pPrimaryKey){

//-- mysql's way of checking if its an auto-incrementing id primary key
	$t       = nuRunQuery("SHOW COLUMNS FROM $pTable WHERE `Field` = '$pPrimaryKey'");
	$r       = db_fetch_object($t);
	return $r->Extra == 'auto_increment';

}

function dbQuery($DBHost, $DBName, $DBUserID, $DBPassWord, $pSQL, $pStopOnError){
//---------open connection and database and return query
	static $con;
	global $NUExtraHTTPHeaders;
	if(!isset($con)){
		$con             = mysql_connect($DBHost,$DBUserID,$DBPassWord)  or die ("Could not connect to database");
		if($NUExtraHTTPHeaders && !headers_sent()){
			header("X-MySQL-Thread: ".mysql_thread_id());	
		}
		mysql_query("SET NAMES 'utf8'");
		//mysql_query("SET CHARACTER_SET utf8");
		mysql_select_db($DBName,$con) or die ("Could not select database");
	}
	$t           = mysql_query($pSQL);
	if (!$t && $pStopOnError) {
		// check if email has been set up
		$t1      = mysql_query("Select * From zzsys_setup");
		$setup   = mysql_fetch_object($t1);
		if(!empty($setup->set_smtp_username) && !empty($setup->set_smtp_password) && !empty($setup->set_smtp_host) && !empty($setup->set_smtp_from_address) && !empty($setup->set_smtp_port)){
			handleQueryError($pSQL,mysql_errno($con),mysql_error($con), true);
		} else {
			handleQueryError($pSQL,mysql_errno($con),mysql_error($con), false);
		}
	} else {
//---------log SQL statements
		if(strtoupper(substr(trim($pSQL), 0, 7))!='SELECT'){
			$t1       = mysql_query("Select * From zzsys_setup");
			$setup   = mysql_fetch_object($t1);
			if($setup->set_log_sql == '1'){
				$hex = str_hex($pSQL);
				mysql_query("INSERT INTO zzsys_sql_log (sql_sql) VALUES ('$hex')");
			}
		}
	}
	return $t;
}

function handleQueryError($pSQL,$pErrorNumber,$pError,$pEmail) {

	if ($GLOBALS['hasFailed']) { //Prevent recursion if logging the error fails, i.e. if this function has already been called once, bail.
		
		//Skip function, go straight to die()
		
	} else {
	
		$GLOBALS['hasFailed'] = true; //Prevent recursion
		
		require_once("common.php");
		
		//dump the output buffer - enable the error message to display unimpeded
		ob_clean();
		
		// Make it match the styling of the site
		echo "<html class='unselected'><head>".makeCSS()."<style>
		.actionButton,#loggedin,#pagetitle {display:  none;}
		</style></head><body style='width: 100%; height: 100%; margin: 0px;'>
		<div style='position: absolute; top: 10px; left: 10px; width: 972px; height: 600px; text-align: left; border-width: 4px;'>
		<div id='browse' class='browse' style='overflow: auto; visibility: visible; width: 920px; height: 556px; padding: 2em;'>
		<div class='BorderDiv' style='margin:-2em -2em 1em -2em;'><h1 class='unselected' style='font-weight: bold; padding: 0.5em; margin: 0px;' id='top'>Error</h1></div>
		";

		if (isset($GLOBALS['db_transaction_running']))
			{
			$trans_error = "The transaction is being rolled back...<BR>\n"; 
			$terb = db_rollback();
			if ($terb) $trans_error .= "Rollback successful.<BR>\n";
			else  $trans_error .= "Rollback returned Error No. " . $terb . "<BR>\n";
			}
		else
			{
			$trans_error = "No Transaction.<BR>\n";
			}
			
		$db = $GLOBALS['dir'];

		$errorReference = substr(uniqid(),-7); //Effectively a random hexadecimal string, short enough to quote over the phone

		// Get custom email addresses and error message details (for this site)
		@ require_once("../$db/config.php"); //Includes $ErrorUserMessage, $ErrorFromEmailAddress and $ErrorEmailAdminAddressList, ErrorEmailClientAddressList, $ErrorReplyEmailAddress. Won't fail if this file is missing.
		
		if (!$ErrorOverrideGlobalAddresses || !isset($ErrorUserMessage) || !isset($ErrorFromEmailAddress) || !isset($ErrorEmailAdminAddressList) || !isset($ErrorReplyEmailAddress)) {
			
			//$ErrorEmailClientAddressList is not retrieved from the global config.php - it can only be set on a per-site basis
			
			// Default action is to concatenate global and site admin email addresses. Of set to override, site address list will be used (but fall back if it doesn't exist)
			if (!$ErrorOverrideGlobalAddresses) {
			    $ErrorEmailAdminAddressList = $GLOBALS['NUErrorEmailAdminAddressList'].",".$ErrorEmailAdminAddressList;
			}
			if(!isset($ErrorUserMessage)) 			{$ErrorUserMessage = $GLOBALS['NUErrorUserMessage'];}
			if(!isset($ErrorFromEmailAddress)) 		{$ErrorFromEmailAddress = $GLOBALS['NUErrorFromEmailAddress'];}
			if(!isset($ErrorEmailAdminAddressList))	{$ErrorEmailAdminAddressList = $GLOBALS['NUErrorEmailAdminAddressList'];}
			if(!isset($ErrorReplyEmailAddress))		{$ErrorReplyEmailAddress = $GLOBALS['NUErrorReplyEmailAddress'];}		
		}
 	
		$errorMessage = ($ErrorUserMessage) ? $ErrorUserMessage : "An error has occurred while running this query. Please contact technical support and quote error reference: ";
		echo "<div style='width: 95%; border: 1px solid red; padding: 1em;'>$errorMessage <a href='mailto:$ErrorReplyEmailAddress?subject=Error Reference: $errorReference, Site: $db&body=An internal error occurred while I was [ENTER WHAT YOU WERE DOING]. %0AThis problem started [ENTER WHEN YOU FIRST SAW THIS ERROR]. %0AThe error reference is $errorReference.'>$errorReference</a>.</div>";

		//Log error to database (if it fails, the global hasFailed will prevent an infinite loop of failure)
		$logMessage = "Error Reference: $errorReference\nAn error occurred while running the following query:\n$pSQL";
		
		nuDebug($logMessage);
	
		if ($_SERVER['SERVER_PORT'] == 443) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
	
		$verboseOutput = "
			<h2>SQL</h2>
			<p>".htmlspecialchars($pSQL)."</p>
			<h2>Transaction</h2>
			<p>".$trans_error."</p>
			<h2>Error</h2>
			<p>".htmlspecialchars("{$pErrorNumber}: {$pError}")."</p>
			<h2>URL</h2>
			<p>".htmlspecialchars($protocol.$GLOBALS['HTTP_HOST'].$GLOBALS['REQUEST_URI'])."</p>
			<h2>Session variables</h2>
			<ul>
			<li><a href='#post'>Post Variables</a></li>
			<li><a href='#get'>Get variables</a></li>
			<li><a href='#cookie'>Cookies</a></li>
			<li><a href='#global'>Globals</a></li>
			<li><a href='#backtrace'>Stack Trace</a></li>
			</ul>
			<h3 id='post'>Post variables</h3><a href='#top'>Top</a>
			<pre>".htmlspecialchars(print_r($_POST,true))."</pre>
			<h3 id='get'>Get variables</h3><a href='#top'>Top</a>
			<pre>".htmlspecialchars(print_r($_GET,true))."</pre>
			<h3 id='cookie'>Cookies</h3><a href='#top'>Top</a>
			<pre>".htmlspecialchars(print_r($_COOKIE,true))."</pre>
			<h3 id='global'>Globals</h3><a href='#top'>Top</a>
			<pre>".htmlspecialchars(print_r($GLOBALS,true))."</pre>
			<h3 id='backtrace'>Stack Trace</h3><a href='#top'>Top</a>
			<pre>".htmlspecialchars(print_r(debug_backtrace(),true))."</pre>
			<a href='#top'>Top</a>
			";
		
		if ($_SESSION['nu_access_level'] == 'globeadmin') {
		
			//Administrator response for debugging/development
			
			echo "<p><strong>The following information is only provided for users logged on as globeadmin.</strong></p>".$verboseOutput;
						
		} else {

			// SMTP EMAIL Has NOT been set up - Die now
			if (!$pEmail) {
				die("<CENTER><H1>Warning</H1><H3>SMTP Email is not set up for this server<BR>This error report has not been sent.</H3></CENTER></div></div></body></html>");
			}
		
			//Non-globeadmin response (send notification emails if any user except globeadmin is logged in)

			require_once("emaillib.php");
			
			$emailMessage = "An error has occurred on site '$db'<br />\nError Reference: $errorReference<br />";
			
			//Send email to client tech staff
			$errorClientMails = array_unique(explode(",",$ErrorEmailClientAddressList)); //Comma-separated list from config.php for this site
			
			// So the user doesn't have to wait for emails to send before seeing the error message.
			ob_flush();
			flush();
		
			foreach ($errorClientMails as $errorMail) {
				if (trim($errorMail) && trim($ErrorFromEmailAddress)) { //Ignore blank strings
					nuSendEmail(trim($errorMail), trim($ErrorFromEmailAddress), $message = "<html><body>$emailMessage</body></html>", $html = true, $subject = "Error Reference: $errorReference, Site: $db",$wordWrap = null, $filelist = null, $receipt=false);
				}
			}
			
			// Add detailed debugging data for site administrator
			$emailMessage .= $verboseOutput;
			
			//Send email to administrator(s)
			$errorAdminMails = array_unique(explode(",",$ErrorEmailAdminAddressList)); //Comma-separated list from config.php for this site
		
			foreach ($errorAdminMails as $errorMail) {
				if (trim($errorMail) && trim($ErrorFromEmailAddress)) { //Ignore blank strings
					nuSendEmail(trim($errorMail), trim($ErrorFromEmailAddress), $message = "<html><body>$emailMessage</body></html>", $html = true, $subject = "Error Reference: $errorReference, Site: $db",$wordWrap = null, $filelist = null, $receipt=false);
				}
			}
		}
	}

	die("</div></div></body></html>");
}

function db_field_name($resource,$i){
	return mysql_field_name($resource,$i);
}

function db_num_fields($resource){
	return mysql_num_fields($resource);
}

function db_query($SQL){
	return mysql_query($SQL);
}

function db_select_db($DBName,$con){
	return mysql_select_db($DBName,$con);
}

function db_connect($DBHost,$DBUserID,$DBPassWord){
	return mysql_connect($DBHost,$DBUserID,$DBPassWord);
}

function db_fetch_object($resource){
	return mysql_fetch_object($resource);
}

function db_fetch_row($resource){
	return mysql_fetch_row($resource);
}

function db_fetch_array($resource){
	return mysql_fetch_array($resource);
}
function db_fetch_field($resource){
	return mysql_fetch_field($resource);
}

function db_insert_id($resource){
	return mysql_insert_id();
}

function db_num_rows($resource){
	return mysql_num_rows($resource);
}

function tableFieldNamesToArray($resource){
    $to               = mysql_num_fields($resource);
    $nameArray        = array();

    for($i = 0 ; $i < $to ; $i++){
        $nameArray[]  = mysql_field_name($resource,$i);
    }
    return $nameArray;
}

function db_thread_id(){
	return mysql_thread_id();
}


function db_real_escape_string($pString){
	return mysql_real_escape_string($pString);
}

function db_start_transaction(){
   $tmp = nuRunQuery("START TRANSACTION");
   $GLOBALS['db_transaction_running'] = True;
   return $tmp;
}

function db_begin(){
   $tmp = nuRunQuery("BEGIN");
   $GLOBALS['db_transaction_running'] = True;
   return $tmp;
}

function db_commit(){
   $tmp = nuRunQuery("COMMIT");
   unset($GLOBALS['db_transaction_running']);
   return $tmp;
}

function db_rollback(){
   $tmp = nuRunQuery("ROLLBACK");
   unset($GLOBALS['db_transaction_running']);
   return $tmp;
}


?>