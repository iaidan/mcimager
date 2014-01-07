<?php
if (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {//get url (passed from apache)
	$url = explode("/", $_REQUEST['url']);
	
	if (!@intval($url[1])) {//detect if size is in url
		$size = 32;//if no set default size
		$mc = $url[1];//set mc username
	} else {
		$size = $url[1];//if yes, set size
		$mc = $url[2];//set mc username
	}
	
	$mc = str_replace(".png", "", $mc);//replace .png at the end of the username
} else {
	die("Access Denied.");//no vairables found in url, deny access
}

header("Content-Type: image/png");//make output png

$skinUrl = "http://www.minecraft.net/skin/{$mc}.png";//minecraft skin url

if (!@file_get_contents($skinUrl)) {//check for skins existance
	$skinUrl = "http://www.minecraft.net/skin/char.png";//no skin exists, return char (Steve)
}

$skin = @imagecreatefrompng($skinUrl);//create skin image

$head = imagecreatetruecolor($size, $size);//create blank canvas
imagecopyresampled($head, $skin, 0, 0, 8, 8, $size, $size, 8, 8);//copy head onto canvas

$helm = imagecreatetruecolor($size, $size);//create blank canvas
imagesavealpha($helm, true);//set alpha
imagealphablending($helm, false);//remove alpha blending

imagecopyresampled($helm, $skin, 0, 0, 40, 8, $size, $size, 8, 8);//move helm onto canvas

switch($url[0]) {//switch function
	case "head":
		imagepng($head);//display head
		break;
	case "helm":
		imagecopy($head, $helm, 0, 0, 0, 0, $size, $size);//move helm onto head
		imagepng($head);//display head
		break;
	case "skin":
		imagepng($skin);//display skin
		break;
}

imagedestroy($skin);//destroy skin
imagedestroy($head);//destroy head
imagedestroy($helm);//destroy helm