<?php
/*
** File:           formlogin.php
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

	//get dir value from form and add to query string as dir
	$dir                                   = $_GET['d'];
	$_GET['dir']                           = $_GET['d'];

	//this is a hack prevention
	if (strpos($dir,"..") !== false) {
		die;
	}
	
	//now that we have the dir value we can do the lib includes
	require_once("../$dir/database.php");
	require_once("config.php");
	require_once("common.php");

	//sets a unique value as both a cookie and as a PHP session array which both need to match
	$cookiename = security_check("security_check");
	
	//set username and password values from POST
	$user = db_real_escape_string($_POST["u"]);
    $pass = db_real_escape_string($_POST["p"]);
	
	//set form and record values from POST
	if (isset($_POST["f"])){
		$loginForm = db_real_escape_string($_POST["f"]);
	} else {
		$loginForm = "index";
	}
	
	if (isset($_POST["r"])){
		$loginRecord = db_real_escape_string($_POST["r"]);
	} else {
		$loginRecord = "";
	}

	//clean two days old data before logging in
	cleanOldData();
	
	//initialise some values
	$_SESSION[$cookiename] = $GLOBALS['security_check'];
	$id                    = $_SESSION[$cookiename];
	$_SESSION['ses']       = $id;
	$stoplogin             = true;
		
	// populate globeadmin password array with password from the db config setting 	
	$globeadminPasswords = populateGlobeadminPasswordArray($DBGlobeadminPassword,$NUGlobeadminPassword,$DBSiteGlobeadminPasswordOnly);
		
	// authenticate user
	$stoplogin = nuLoginAuthenticate($user, $pass, $globeadminPasswords, $id);
	
	//check which page to go to on login
	if($stoplogin){ 
	
		//failed login returns to login screen
		$loginOutput = createStopLoginUrl();
	
	} else { 
	
		//success login returns to index/edit/browse
		$loginOutput = createLoginUrl($loginForm,$loginRecord,$dir,$id);
	}	
		
	//Final result of login
	echo $loginOutput;
	
	/////////////functions//////////////////////////////////////////////////////
	
	function nuLoginAuthenticate($user, $pass, $globeadminPasswords, $id) {
		
		//default 
		$stoplogin = true;	
	
		//valid globeadmin login 	
		if ($user=='globeadmin' && in_array($pass, $globeadminPasswords)){
			nuBuilderUserLogin($id, "globeadmin", "globeadmin", "globeadmin");
			$stoplogin = false;	
			return $stoplogin;
		}
		
		//query db for username and password
		$s  = "SELECT zzsys_user_id AS ID, sal_name AS AccessLevel, sug_group_name as UserGroupName FROM zzsys_user ";
		$s .= "INNER JOIN zzsys_user_group ON sus_zzsys_user_group_id = zzsys_user_group_id ";
		$s .= "INNER JOIN zzsys_access_level ON sug_zzsys_access_level_id = zzsys_access_level_id ";
		$s .= "WHERE sus_login_name = '$user' AND sus_login_password = '" . md5('nu' . $pass) . "'";
		$t  = nuRunQuery($s);
		$r  = db_fetch_object($t);
		// the globeadmin username should not exist in the zzsys_user table 
		if($r->ID!='' && $user=='globeadmin'){
			return $stoplogin;
		}
		
		//user name and password failed	
		if($r->ID==''){
			return $stoplogin;
		}	

		//user name and password success
		nuBuilderUserLogin($id, $r->AccessLevel, $r->ID, $r->UserGroupName);
		$stoplogin = false;	 
		return $stoplogin;
				
	}
	
	function populateGlobeadminPasswordArray($DBGlobeadminPassword,$NUGlobeadminPassword,$DBSiteGlobeadminPasswordOnly) {
		
		$globeadminPasswords = array();
		
		// populate globeadmin password array with password from the db config setting 	
		if ($DBGlobeadminPassword) {
			$globeadminPasswords[] = $DBGlobeadminPassword;
		}	
		
		// populate globeadmin password array with password from the global config setting 
		// checks for the setting DBSiteGlobeadminPasswordOnly which the global config password can never be used	
		if (!$DBSiteGlobeadminPasswordOnly && $NUGlobeadminPassword) {
				$globeadminPasswords[] = $NUGlobeadminPassword;
		}	
		
		return $globeadminPasswords;
		
	}
	
	function nuBuilderUserLogin($id, $accessLevel, $userID, $userGroupName) {
		
		//set cookie to keep pages active
		nuCreateCookie("nuC",1,1);
		
		//set user session variables
		$_SESSION['nu_session']         = $id;
		$_SESSION['nu_access_level']    = $accessLevel;
		$_SESSION['nu_user_id']         = $userID;
		$_SESSION['nu_user_group']      = $userGroupName;
		$_SESSION['nu_last_login']      = time();
		
		//log successfull login to db
		logSuccessfulLogin($userID, $id); 
	}
	
	function logSuccessfulLogin($userID, $id) {
		$now = date('Y-n-d H:i:s');
		nuRunQuery("INSERT INTO zzsys_user_log (zzsys_user_log_id, sul_zzsys_user_id, sul_ip, sul_start) VALUES ('$id', '$userID', '{$_SERVER['REMOTE_ADDR']}', '$now')");
	}
	
	//creates the meta refesh to failed logins	
	function createStopLoginUrl() {
		$result = $_SERVER['HTTP_REFERER'];
		return "<META HTTP-EQUIV=Refresh CONTENT=\"0; URL=$result\">";
	}
	
	//creates the meta refesh to successful logins	
	function createLoginUrl($loginForm, $loginRecord, $dir, $id) {
		
			//checking for a form with a blank record
			if ($loginForm != "index" && $loginRecord == "") { //assume a browse login
				$result  = "";
				$result .= "browse.php?x=1&p=1&f=";
				$result .= $loginForm;
			}	
			
			//checking for non standard login form and record 
			if ($loginForm != "index" && $loginRecord != "") { //assume a non standard login
				$result .= "form.php?x=1&f=";
				$result .= $loginForm;
				$result .= "&r=";
				$result .= $loginRecord;
			}
			
			//checking for standard login form
			if ($loginForm == "index") { //assume record id -1
				$result .= "form.php?x=1&f=";
				$result .= $loginForm;
				$result .= "&r=";
				$result .= "-1";
			}
			
			$result .= "&dir=";
			$result .= $dir; 
			$result .= "&ses=";
			$result .= $id;
			return "<META HTTP-EQUIV=Refresh CONTENT=\"0; URL=$result\">";
	}
	
	//sets a unique value as both a cookie and as a PHP session array which both need to match
	function security_check($security_label){
		
		//create a unique value
		$value = uniqid('1');
		
		//set this a a cookie
		$cookieName = nuCreateCookie($security_label, $value);
		
		//set this a PHP globals
		$GLOBALS[$security_label] = $value;
		
		//return value
		return $cookieName;
	}
	
	//clean two days old data 
	function cleanOldData() {
	
		$twodaysago                            = nuDateAddDays(Today(),-2);
		nuRunQuery("DELETE FROM zzsys_variable WHERE sva_expiry_date < '$twodaysago'");
		nuRunQuery("DELETE FROM zzsys_trap WHERE sys_added is null OR sys_added < '$twodaysago'");
//		nuRunQuery("DELETE FROM zzsys_duplicate WHERE sdu_date < '$twodaysago'");
		nuRunQuery("DELETE FROM zzsys_session  WHERE sss_session_date < '$twodaysago'");
		
	}
	
?>