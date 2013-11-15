<?php

date_default_timezone_set('Asia/Shanghai');

class Njss {
	protected $access_key;
	protected $access_secret;
	protected $service;

	public function __construct($access_key, $access_secret) {
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'JingdongStorageService.php';
		$service = new JingdongStorageService($access_key, $access_secret);
		$this->set_jss($service, $access_key, $access_secret);
	}

	protected function set_jss($service, $access_key, $access_secret) {
		$this->access_key = $access_key;
		$this->access_secret = $access_secret;
		$this->service = $service;
	}

	public function is_object_exist($bucket, $key) {
		try {
			$this->service->head_object($bucket, $key);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function get_object_contents($bucket, $key) {
		try {
			$tmpfile = tmpfile();
			$this->service->get_object($bucket, $key, $tmpfile);
			fseek($tmpfile, 0);
			$contents = '';
			while (!feof($tmpfile)){
				$contents .= fread($tmpfile, 8192);
			}
			return $contents;
		} catch (Exception $e) {
			return false;
		}
	}

	public function put_object_contents($bucket, $key, $contents) {
		try {
			$tmpfile = tmpfile();
			fwrite($tmpfile, $contents);
			fseek($tmpfile, 0);
			$this->service->put_object($bucket, $key, $tmpfile);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function down_object_contents($bucket, $key, $url) {
		$contents = $this->nikbobo_get_contents($url);
		if (false <> $contents) {
			$this->put_object_contents($bucket, $key, $contents);
			return true;
		} else {
			return false;
		}
	}

	protected function nikbobo_get_contents($url) {
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$contents = curl_exec($ch);
			curl_close($ch);
			return $contents;
		} elseif (function_exists('file_get_contents')) {
			return file_get_contents($url);
		} else {
			return false;
		}
	}

}
