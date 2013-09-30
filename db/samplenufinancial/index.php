<?php
$dbfolder = "samplenufinancial";
$nameText = "Financial Module";
$build    = "productionnu2";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<style type='text/css'>


* {
	margin: 0px;
	padding: 0px;
}

html {
	width: 100%;
	height: 100%;
}

body {
	background-image: url('nuBuilder-Logo.png');
	background-repeat: no-repeat;
	background-attachment: scroll;
	background-position: center 60px; 
	margin: 0px;
	padding: 0px;
	border: 0px;
	min-width: 100%;
	min-height: 100%;
	width: 100%;
	height: 100%;
	font-family: tahoma, verdana, helvetica
}

#main {


	width: 800px;
	height: 100%;
	left: 50%;
	margin: 0px;
	margin-left: -400px;
	position: absolute;
	padding: 0px;

}


#sitename {
        margin: 0px;
        padding: 0px;
        position: absolute;
        top: 275px;
        left: 180px;
        text-align: left;
}
#credentials {
	margin: 0px;
	padding: 0px;
	position: absolute;
	top: 305px;
	left: 180px;
	text-align: right;
}
#credentials input {
	margin-bottom: 5px;
	vertical-align: top;
}
.button {
	background-color: orange;
}

</style>


<body onload='document.forms[0]["u"].focus()'>
<div id="main">
<form name='index' method='POST' action='../../<?php echo $build;?>/formlogin.php?x=1&d=db/<?php echo $dbfolder;?>'>
<div id="sitename">
	<p>Site Name: <?php echo $nameText;?></p>
</div>
<div id="credentials">
	<p>Username <input type='text' name='u'><br />
	Password <input type='password' name='p'><br />
	<input type='submit' class="button" value="Login" />
</div>
</form>
</div>


</body>
</html>

