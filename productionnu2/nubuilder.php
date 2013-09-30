<?php
/*
** File:           nubuilder.php
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

$dir                             = $_GET['dir'];
$ses                             = $_GET['ses'];
$f                               = $_GET['f'];
$r                               = $_GET['r'];
$c                               = $_GET['c'];
$d                               = $_GET['d'];
$debug                           = $_GET['debug'];
$fb                              = '0';

if (strpos($dir,"..") !== false)
	die;

require_once("../$dir/database.php");
require_once('common.php');

$form = formFields($f);
print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n";
print "<html>\n";
print "<head>\n<title>$form->sfo_title</title>\n<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>\n</head>\n";
print "<frameset border='0' rows='100%,*,*,*,*,*,*,*,*,*,*,*'>\n";

if($f == 'index'){
	print "<frame name='index' style='border:none;margin:0px;padding:0px;' frameborder='$fb' scrolling='yes' src='form.php?x=1&amp;f=$f&amp;r=$r&amp;dir=$dir&amp;ses=$ses&amp;c=$c&amp;d=$d&amp;debug=$debug'>\n";
}else{
	print "<frame name='main' style='border:none;margin:0px;padding:0px;' frameborder='$fb' scrolling='yes' src='form.php?x=1&amp;f=$f&amp;r=$r&amp;dir=$dir&amp;ses=$ses&amp;c=$c&amp;d=$d&amp;debug=$debug'>\n";
}

for ($i = 0 ; $i < 11 ; $i++){
	print "<frame name='hide$i' noresize='noresize' style='border:none;margin:0px;padding:0px;visibility:hidden;' scrolling='no' frameborder='$fb' src='blank.html'>\n";
}

print "</frameset>\n";
print "</html>\n";
?>
