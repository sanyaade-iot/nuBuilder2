<?php

/* Please supply your own consumer key and consumer secret */
include 'Dropbox/autoload.php';


$consumerKey    = 'kpdfwwz21hkseln';
$consumerSecret = 'lnyo9lod1jgp8wh';
$oauth          = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);
$dropbox        = new Dropbox_API($oauth);
$tokens         = $dropbox->getToken('steven@nubuilder.com', '7jcopley'); 

$oauth->setToken($tokens);

header('Content-Type: image/jpeg');
session_start();

echo $dropbox->getFile('flower.jpg');

?>