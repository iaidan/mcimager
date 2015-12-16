<?php
/*Copyright (C) 2015 Aidan Taylor - www.aidantaylor.net - Do not remove this notice*/

class Handle {
	public $url = "";
	public $parts = "";

	public function __construct() {
		if (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {
			$this->url = $_REQUEST['url'];
			$this->parts = explode("/", $this->url);
		}
	}

	public function get($part) {
		if (isset($this->parts[$part])) {
			return $this->parts[$part];
		} else {
			return false;
		}
	}
}