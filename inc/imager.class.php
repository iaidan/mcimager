<?php
/*Copyright (C) 2015 Aidan Taylor - www.aidantaylor.net - Do not remove this notice*/

class Imager {
	private $base = "https://api.mojang.com";
	private $sessionBase = "https://sessionserver.mojang.com/session";
	private $defaultSkin = "http://www.minecraft.net/skin/char.png";
	private $cacheTimeout = 120;

	private $user;
	private $cache;
	private $skin;

	public function __construct($user, $cache) {
		$this->user = $this->checkUser($user);
		$this->cache = $cache;

		$skin = urldecode($this->cache->get("mcimager-skin-{$this->user}"));

		if (empty($skin) || $skin === null) {
			$skin = $this->getSkin();
			$this->cache->set("mcimager-skin-{$this->user}", urlencode($skin), $this->cacheTimeout);
		}

		$this->skin = imagecreatefromstring($skin);
	}

	private function checkUser($user) {
		$apiLookup = $this->getJson("{$this->base}/users/profiles/minecraft/{$user}");

		if (isset($apiLookup['id'])) {
			$user = $apiLookup['id'];
		}

		return $user;
	}

	private function getSkin() {
		$sessionLookup = $this->getJson("{$this->sessionBase}/minecraft/profile/{$this->user}");

		if (isset($sessionLookup['properties'])) {
			$properties = base64_decode($sessionLookup['properties'][0]['value']);
			$properties = json_decode($properties, true);

			$skinGrab = file_get_contents($properties['textures']['SKIN']['url']);
		}

		if (!isset($skinGrab)) {
			$skinGrab = @file_get_contents($this->defaultSkin);
		}

		return $skinGrab;
	}

	private function getJson($call, $array = true) {
		$ctx = stream_context_create(array( 
		    'http' => array( 
		        'timeout' => 10
		        ) 
		    ) 
		); 

		$apiLookup = @file_get_contents($call, 0, $ctx);

		return json_decode($apiLookup, $array);
	}

	public function renderSkin($size) {
		return $this->skin;
	}

	public function renderHead($size) {
		$headImage = imagecreatetruecolor($size, $size);
		imagecopyresampled($headImage, $this->skin, 0, 0, 8, 8, $size, $size, 8, 8);
		
		return $headImage;
	}

	public function renderHelm($size) {
		$helmImage = imagecreatetruecolor($size, $size);
		imagesavealpha($helmImage, true);
		imagealphablending($helmImage, false);

		$headImage = imagecreatetruecolor($size, $size);
		imagecopyresampled($headImage, $this->skin, 0, 0, 8, 8, $size, $size, 8, 8);
	
		imagecopyresampled($helmImage, $this->skin, 0, 0, 40, 8, $size, $size, 8, 8);
		
		$helmExists = false;
		
		for($y = 0; $y <= $size; $y++) {
			for($x = 0; $x <= $size; $x++) {
				$rgb = @imagecolorat($helmImage, $x, $y);
				$colors = @imagecolorsforindex($helmImage, $rgb);
				
				if (is_array($colors) && $colors['alpha'] !== 0) {
					$helmExists = true;
					break;
				}
			}
			
			if ($helmExists) {
				break;
			}
		}
		
		if ($helmExists) {
			imagecopyresampled($headImage, $helmImage, 0, 0, 0, 0, $size, $size, $size, $size);
		}

		return $headImage;
	}
}