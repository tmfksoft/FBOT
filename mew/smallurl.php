<?php
class SmallURL {
	private $key = false;
	private $init = false;

	public function init($given) {
		$result = $this->validate_key($given,true);
		if ($result['res']) {
			$this->init = true;
			$this->key = $given;
			return true;
		}
		else {
			$res = array();
			$res['res'] = false;
			$res['msg'] = 'Unable to Init SmallURL! The provided key was rejected with the message: '.$result['msg'];
		}
	}
	public function shorten($url,$custom = false) {
		// Shortens our URL
		if ($custom != false) {
			$result = $this->push_request(array('url'=>$url,'custom'=>$custom));
		}
		else {
			$result = $this->push_request(array('url'=>$url));
		}
		if ($result['res']) {
			// It worked.
			$res = array();
			$res['result'] = true;
			$res['short'] = $result['short'];
			$res['full'] = "http://smallurl.in/".$result['short'];
			$res['url'] = $url;
			return $res;
		}
		else {
			$res = array();
			$res['result'] = false;
			$res['msg'] = $result['msg'];
		}
	}
	public function inspect($smallurl) {
		// Inspects a SmallURL
		$result = $this->push_request(array('action'=>'inspect','short'=>$smallurl));
		if ($result['res'] == true) {
			$res = array();
			$res['result'] = true;
			$res['long'] = "http://smallurl.in/".$result['short'];
			$res = array_merge($res,$result);
			unset($res['res']);
			return $res;
		}
		else {
			die($result['msg']);
		}
	}
	private function push_request($data = array('action'=>'shorten')) {
		// This simply pushes data to the API and retrieves it.
		global $api;
		$key = $this->key;
		if ($key != false) {
			// Key is preset, otherwise we override it with a validation.
			$data['key'] = $key;
		}
		$data['type'] = 'php';
		$query = array();
		foreach ($data as $key => $val) {
			$query[] = "{$key}=".urlencode(htmlentities($val));
		}
		$query = implode("&",$query);
		$query_url = "http://api.smallurl.in/?".$query;
		$api->log(" [SmallURL] Querying ".$query_url);
		$reply = file_get_contents($query_url);
		$api->log(" [SmallURL] ".$reply);
		return unserialize($reply);
	}
	private function validate_key($given,$sup = false) {
		// This simply checks if SmallURL likes us, Editing this code wont do anything.
		$result = $this->push_request(array('action'=>'check','key'=>$given));
		if ($result['res']) {
			// The keys right.
			return $result;
		}
		else {
			if (!$sup) {
				die('The API Key was refused by SmallURLs API with message: '.$result['msg']);
			}
			else {
				return $result;
			}
		}
	}
}
$SmallURL = new SmallURL();
?>