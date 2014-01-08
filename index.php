<?php
$cache = true;//should the script cache images?

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

//image cache locations
$mcSkinCache = "cache/skin/{$mc}.png";
$mcHeadCache = "cache/head/{$mc}-{$size}.png";
$mcHelmCache = "cache/helm/{$mc}-{$size}.png";

//location to store last cache date
$dateCache   = "tmp/{$mc}-{$size}.cache.txt";

header("Content-Type: image/png");//make output png

//check last cache time
if ($data = @file_get_contents($dateCache)) {
    $mysqldate = strtotime(date('Y-m-d H:i:s', $data));
} else {
    $mysqldate = false;
}

$mysqldateToday = strtotime(date('Y-m-d H:i:s'));

if ($cache === false || $mysqldate === false || ($mysqldateToday - $mysqldate) >= 600) {
	$skinUrl = "http://www.minecraft.net/skin/{$mc}.png";
	
	$skinGrab = @file_get_contents($skinUrl);
	
	if (!$skinGrab) {
		$skinUrl = "http://www.minecraft.net/skin/char.png";//no skin exists, return char (Steve)
		$skinGrab = @file_get_contents($skinUrl);
	}
	
	$skinImage = imagecreatefromstring($skinGrab);//load skin image
	file_put_contents($mcSkinCache, $skinGrab);//save skin
	
	//head
	$headImage = imagecreatetruecolor($size, $size);//create blank canvas
	imagecopyresampled($headImage, $skinImage, 0, 0, 8, 8, $size, $size, 8, 8);//copy head onto canvas
	imagepng($headImage, $mcHeadCache);//save head
		
	//helm
	$tempImage = imagecreatetruecolor($size, $size);//create blank canvas
	$helmImage = imagecreatefrompng($mcHeadCache);//load head
	imagesavealpha($tempImage, true);//set alpha
	imagealphablending($tempImage, false);//remove alpha blending
	
	imagecopyresampled($tempImage, $skinImage, 0, 0, 40, 8, $size, $size, 8, 8);//move helm onto canvas
	imagecopy($helmImage, $tempImage, 0, 0, 0, 0, $size, $size);//move helm onto head
	imagepng($helmImage, $mcHelmCache);//save head
    
	//save cache date file
    $fh = fopen($dateCache, 'w');
    fwrite($fh, $mysqldateToday);
    fclose($fh);
} else {
	$skinImage = imagecreatefrompng($mcSkinCache);
	$helmImage = imagecreatefrompng($mcHelmCache);
	$headImage = imagecreatefrompng($mcHeadCache);
}

switch($url[0]) {//switch function
	case "head":
		imagepng($headImage);//display head
		break;
	case "helm":
		imagepng($helmImage);//display head
		break;
	case "skin":
		imagepng($skinImage);//display skin
		break;
}
	
//destroy images
@imagedestroy($skinImage);
@imagedestroy($headImage);
@imagedestroy($helmImage);