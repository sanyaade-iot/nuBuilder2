<?php
/*
** File:           formhelp.php
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

require_once('../' . $_GET['dir'] . '/database.php');
require_once('common.php');

$f                                  = $_GET['f'];

$t = nuRunQuery("SELECT * FROM zzsys_form WHERE zzsys_form_id = '$f'");
$r = db_fetch_object($t);

print "<html>";
print "	<head>";
print "		<title>Help for $r->sfo_title Screen</title>";
print "		<script type='text/javascript' src='jquery.js'></script>";
print "		<script type='text/javascript' src='common.js'></script>";
print "		<script type='text/javascript'>";
/*
print "			self.setInterval('checknuC()', 1000);";
print "			function checknuC(){";
print "				if(nuReadCookie('nuC') == null){";
print "					window.close();";
print "				}";
print "			}";
*/

print "			function customDirectory(){";
print "				return '".$_GET['dir']."';";
print "			}";
print "		</script>";
print "	</head>";
print "	<body bgcolor=#ffffff text=#000000 link=#0000cc vlink=#551a8b alink=#ff0000>";
print "		$r->sfo_help";
print "	</body>";
print "</html>";
?>