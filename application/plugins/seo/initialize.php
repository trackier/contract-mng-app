<?php

// initialize seo
include("seo.php");

if (defined('GCDN')) {
	$prefix = GCDN;
} else {
	$prefix = "";
}

$seo = new SEO(array(
    "title" => "Dashboard",
    "photo" => $prefix . "img/logo.png"
));

Framework\Registry::set("seo", $seo);
