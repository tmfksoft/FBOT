<?php
// DEPRECATED!
// Old Functions for the sake of functionality.

function text_split($in) {
	$len = count($in);
	$a = substr($in,0,$len/2);
	$b = substr($in,0,(0-($len/2)));
	return array($a,$b);
}

// OLD!
function update_hostname($nick,$host = false) {
	global $_PITC;
	$nick = strtolower($nick);
	if ($host != false) {
		$_PITC['hosts'][$nick] = $host;
	} else {
		unset($_PITC['hosts'][$nick]);
	}
}

// OLD!
function shutdown($message = "Shutdown",$isexec = false) {
	global $sid,$api_stop,$api,$core,$xmpp;
	if (isset($api) && $api != NULL) {
		$api->log($core->lang('CLOSING'));
	} else {
		echo "FBOT is closing.";
	}
	// START Handler/Hook
	$x = 0;
	while ($x != count($api_stop)) {
		$args = array(); // Empty for now
		call_user_func($api_stop[$x],$args);
		$x++;
	}
	global $cfg;
	// Save any changes to the config
	$cfg->save("core");
	if (isset($sid)) {
		system("stty sane");
	}
	//$xmpp->disconnect(); /* Just Dying may work */
	if ($isexec) {
		die(shell_exec($message));
	}
	else {
		die($message."\n");
	}
}

function pitc_raw($text,$sock = false) {
	global $sid,$rawlog,$core,$xmpp;
	if ($sock) { $fp = $sock; }
	else { $fp = $sid; }
	$rawlog[] = "C: {$text}";
	$core->writeLog(true);
	return $xmpp->send($text);
}
function load_script($file) {
	global $core;
	if (file_exists($file)) {
		$res = include($file);
		if ($res) {
			$core->internal(" = Loaded script '".$file."' = ");
		}
		else {
			$core->internal(" = Error loading script '".$file."' = - ".$res);
		}
	}
	else {
		$core->internal(" = Error loading script '".$file."' = - File does not exist.");
	}
}
function version() {
	global $_FBOT;
	return "FBOT{$_FBOT['append']} v".$_FBOT['version']." by Thomas Edwards";
}
function isHighlight($text,$nick) {
	if (is_array($text)) { $text = implode(" ",$text); }
	$nick = preg_quote($nick);
	return preg_match("/".$nick."/i", $text);
}
function pitcEval($text) {
	return $text;
}
function string_duration($a,$b) {
	$uptime = $a - $b;
	$second = floor($uptime%60);
	$minute = floor($uptime/60%60);
	$hour = floor($uptime/3600);
	$day = floor($uptime/86400);
	$week = floor($uptime/604800);
	$month = floor($uptime/2419200);
	$year = floor($uptime/31536000);
	$uptime = "{$second}seconds";
	if ($minute) { $uptime = "{$minute}minutes " . $uptime; }
	if ($hour) { $uptime = "{$hour}hours " . $uptime; }
	if ($day) { $uptime = "{$day}days " . $uptime; }
	if ($week) { $uptime = "{$week}weeks " . $uptime; }
	if ($month) { $uptime = "{$month}months " . $uptime; }
	if ($year) { $uptime = "{$year}years " . $uptime; }
	return $uptime;
}
// OLD.
function data_get($url = false) {
	if ($url) {
		$data = file_get_contents($url);
		$array = json_decode($data);
		return $array;
	}
	else {
		return false;
	}
}
// Old. Checks if you have an internet connection.
function is_connected() {
    $connected = @fsockopen("google.com",80); //website and port
    if ($connected) {
        $is_conn = true; //action when connected
        socket_close($connected);
    }
	else {
        $is_conn = false; //action in connection failure
    }
    return $is_conn;
}

// Needs moving into core and made to tab facebook users names.
function nick_tab($nicks,$text,$tab = 0) {
	// Allows you to tab a nickname.
	// Get last letter or word.
	$data = explode(" ",$text);
	$data = $data[count($data)-1];
	
	$nicknames = array();
	foreach ($nicks as $name) {
		$nicknames[] = trim(strtolower($name));
	}
	$data = strtolower($data);
	$ret = preg_grep("/^{$data}.*/", $nicknames);
	if ($ret != FALSE) {
		reset($ret);
		$key = key($ret);
		$ret = array_values($ret);
		$ret = $ret[$tab];
		echo "I found {$ret}!\n";
		return trim($nicks[$key],"!~&@%+");
	}
	else {
		return false;
	}
}
?>