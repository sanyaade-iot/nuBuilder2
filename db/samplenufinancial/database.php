<?php

require_once(dirname(__FILE__).'/config.php');

function nuRunQuery($pSQL, $pStopOnError = true)
{
	global $nuQueriesRun;
	global $DBHost;
	global $DBName;
	global $DBUser;
	global $DBPassword;

	$GLOBALS['nuRunQuery']++;
	$nuQueriesRun++;
	
	if($pSQL == '')
	{
		$a           = array();
		$a[0]        = $DBHost;
		$a[1]        = $DBName;
		$a[2]        = $DBUser;
		$a[3]        = $DBPassword;
		return $a;
	}
	
	$t = dbQuery($DBHost, $DBName, $DBUser, $DBPassword, $pSQL, $pStopOnError);
	return $t;
}

?>
