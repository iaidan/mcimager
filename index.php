<?php
/*Copyright (C) 2015 Aidan Taylor - www.aidantaylor.net - Do not remove this notice*/

(require_once("inc/handle.class.php")) or die("Failed to load");
(require_once("inc/cache.class.php")) or die("Failed to load");
(require_once("inc/imager.class.php")) or die("Failed to load");


$cache = new Cache();
$handle = new Handle();

if (empty($handle->url) || !in_array($handle->get(0), array("head", "helm", "skin"))) {
	die();
}

$size = 32;
$user = "";

if ($handle->get(1) !== null) {
	$handle1 = str_replace(".png", "", $handle->get(1));

	if ($handle->get(2)  !== null) {
	 	$handle2 = str_replace(".png", "", $handle->get(2));

		if (is_numeric($handle1)) {
			$size = $handle1;
			$user = $handle2;
		} else {
			$size = $handle2;
			$user = $handle1;
		}
	} else {
		$user = $handle1;
	}
} else {
	die();
}

$imager = new Imager($user, $cache);

header("Content-Type: image/png");

switch($handle->get(0)) {
	case "head":
		$head = $imager->renderHead($size);
		imagepng($head);
		@imagedestroy($head);
		break;
	case "helm":
		$helm = $imager->renderHelm($size);
		imagepng($helm);
		@imagedestroy($helm);
		break;
	case "skin":
		$skin = $imager->renderSkin($size);
		imagepng($skin);
		@imagedestroy($skin);
		break;
}