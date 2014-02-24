<?php
// FBOT CORE API
$pitc_log = array();
class core {
	// Needs updating to FBOT instead of PITC
	public function internal($text,$type = "CORE") {
		global $pitc_log;
		if ($type == "CORE") {
			$str = "  [{$type}] ".$text;
		} else {
			$str = "  [{$type}] ".$text;
		}
		$pitc_log[] = $str;
		//ob_end_clean();
		echo $str."\n";
		//ob_start();
		$this->writeLog();
	}
	// Needs updating to FBOT instead of PITC
	public function lang($item) {
		global $lng;
		if (isset($lng[$item])) {
			return $lng[$item];
		} else {
			return false;
		}
	}
	// Needs updating to FBOT instead of PITC
	public function writeLog($raw = false) {
		global $pitc_log,$rawlog;
		// Our Log Stamp
		$stamp = date('d-m-Y');
		
		// load in the previous log.
		if ($raw) {
			if (file_exists("logs/{$stamp}-raw.log")) {
				$log = explode("\n",file_get_contents("logs/{$stamp}-raw.log"));
			} else {
				$log = array();
			}
		} else {
			if (file_exists("logs/{$stamp}.log")) {
				$log = explode("\n",file_get_contents("logs/{$stamp}.log"));
			} else {
				$log = array();
			}
		}
		
		// Merge old data with new. IF there is any old.
		$data = array();
		if (count($log) >= 1) {
			foreach ($log as $old) {
				$data[] = trim($old);
			}
		}
		if ($raw) {
			foreach ($rawlog as $new) {
				$data[] = trim($new);
			}
		} else {
			foreach ($pitc_log as $new) {
				if (preg_match("/\[(.*)\] \[[0-9]+;[0-9]+m (.*)\[0m/",$new,$matches)) {
					$str = trim("[{$matches[1]}]  {$matches[2]}");
				} else {
					$str = trim($new);
				}
				$data[] = trim($str,"");
			}
		}
		
		if (!file_exists("logs") || !is_dir("logs")) {
		
			if (!file_exists("logs/")) {
				$dir = "logs";
				mkdir("logs/");
			} else if (!is_dir("logs/")) {
				$dir = "logs_pitc";
				if (!file_exists("logs_pitc/")) {
					mkdir("logs/");
				} else if (is_file("logs_pitc/")) {
					echo "Unable to init log!\n";
				}
			}
			
		} else {
			$dir = "logs";
		}
		if ($raw) {
			$rawlog = array();
			file_put_contents($dir."/".$stamp."-raw.log",implode("\n",$data));
		} else {
			$pitc_log = array();
			file_put_contents($dir."/".$stamp.".log",implode("\n",$data));
		}
	}
	public function spoon() {
		return "There is no spoon.";
	}
	// Needs updating to FBOT instead of PITC
	public function userlist($chan) {
		$userlist = new userlist($chan);
		return $userlist;
	}
	// Needs updating to FBOT instead of PITC
	public function user($nick) {
		return new users($nick);
	}
}
$core = new core;
// Needs updating to FBOT instead of PITC
class userlist {
	public $channel = null;
	function __construct($chan) {
		global $api;
		$api->log("Construct called");
		$this->channel = $chan;
	}
	public function append($channel,$user) {
	
	}
	public function remove($channel,$user) {
	}
	public function clear($channel,$user) {
	
	}
	public function get() {
		return array("channel"=>$this->channel);
	}
}
// Needs updating to FBOT instead of PITC
class users {
	public $username = null;
	function __construct($uname) {
		global $_FBOT;
		$this->username = $uname;
		if (isset($_FBOT['users'][md5(strtolower($uname))])) {
			foreach ($_FBOT['users'][md5(strtolower($uname))] as $var => $val) {
				echo "Setting $"."this->{$var} to {$val}\n";
				if (!is_array($val)) {
					echo "$var is str\n";
					eval('$this->'.$var.' = "'.$val.'";');
				} else {
					echo "$var is arry\n";
					eval('$this->'.$var.' = json_decode(\''.json_encode($val).'\',true);');
				}
			}
		}
	}
	function add($data = array()) {
		global $_FBOT;
		$data['nick'] = $this->username;
		$_FBOT['users'][md5(strtolower($this->username))] = $data;
	}
	function hostmask($host = false) {
		global $_FBOT;
		if (!$host) {
			// Return mask
			$host = $_FBOT['users'][md5(strtolower($this->username))]['host'];
			return $host;
		} else {
			// Set mask
			$_FBOT['users'][md5(strtolower($this->username))]['host'] = $host;
			return true;
		}
	}
}
$_FBOT['users'] = array();

// PITCBots API
class pitcapi {
	public function log($text = false) {
		global $core,$cserver;
		if (!$text) {
			die("{$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} LOG");
		}
		else {
			$core->internal($text,"API");
		}
	}
	public function getHost($nick = false) {
		global $_FBOT,$cnick;
		if ($nick) {
			$nick = strtolower($nick);
		} else {
			$nick = strtolower($cnick);
		}
		if (isset($_FBOT['hosts'][$nick])) {
			return $_FBOT['hosts'][$nick];
		} else {
			return false;
		}
	}
	public function setHost($nick = false,$host = false) {
		global $_FBOT;
		if ($nick) {
			$nick = strtolower($nick);
		} else {
			$nick = strtolower($cnick);
		}
		if (!$host) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} HOST {$core->lang('API_INFUNC')} SETHOST");
		}
		update_hostname($nick,$host);
	}
	public function delHost($nick = false) {
		global $_FBOT;
		if ($nick) {
			$nick = strtolower($nick);
		} else {
			$nick = strtolower($cnick);
		}
		update_hostname($nick);
	}
	public function addCommand($command = false,$function = false) {
		global $core;
		$core->internal(" ".$core->lang('API_ERROR_COMMAND'));
	}
	public function addTextHandler($function = false) {
		global $core,$api_messages,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDTEXTHANDLER");
		}
		else {
			$api_messages[] = strtolower($function);
		}
	}
	public function addConnectHandler($function = false) {
		global $core,$api_connect,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDCONNECTHANDLER");
		}
		else {
			$api_connect[] = strtolower($function);
		}
	}
	public function addActionHandler($function = false) {
		global $core,$api_actions,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDACTIONHANDLER");
		}
		else {
			$api_actions[] = strtolower($function);
		}
	}
	public function addStartHandler($function = false) {
		global $core,$api_start,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDSTARTHANDLER");
		}
		else {
			$api_start[] = strtolower($function);
		}
	}
	public function addShutDownHandler($function = false) {
		global $core,$api_stop,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDSHUTDOWNHANDLER");
		}
		else {
			$api_stop[] = strtolower($function);
		}
	}
	public function addJoinHandler($function = false) {
		global $core,$api_joins,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDJOINHANDLER");
		}
		else {
			$api_joins[] = strtolower($function);
		}
	}
	public function addPartHandler($function = false) {
		global $core,$api_parts,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDPARTHANDLER");
		}
		else {
			$api_parts[] = strtolower($function);
		}
	}
	public function addTickHandler($function = false) {
		global $core,$api_tick,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDTICKHANDLER");
		}
		else {
			$api_tick[] = strtolower($function);
		}
	}
	public function addRawHandler($function = false) {
		global $core,$api_raw,$active;
		if (!$function) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} ADDRAWHANDLER");
		}
		else {
			$api_raw[] = strtolower($function);
		}
	}
	// Now we add the commands.
	public function pecho($text = false,$window = false) {
		global $core,$active;
		if (!$text) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} PECHO");
		}
		else {
			// PITCBots unlike PITC lacks windows and only has one window, The Terminal
			$core->internal($text);
		}
	}
	public function msg($channel = false,$text = false) {
		global $core,$log_irc, $sid, $cfg, $xmpp;
		if (!$channel) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} MSG");
		}
		else if (!$text) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} MSG");
		}
		else {
			//if ($sid) {
				$xmpp->message($channel, $body=$text, $type="chat");
				if ($log_irc) {
					$core->internal($channel.": <".$cfg->get("core","realname")."> ".$text);
				}
			//}
			//else {
			//	$core->internal(" ERROR. PBOT is not CONNECTED to XMPP. Cannot MSG.");
			//}
		}
	}
	public function action($channel = false,$text = false) {
		global $core,$log_irc, $colors, $sid, $cfg, $xmpp;
		if (!$channel) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} ACTION");
		}
		else if (!$text) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} ACTION");
		}
		else {
			//if ($sid) {
				$xmpp->message($channel, $body="*".$text."*", $type="chat");
				if ($log_irc) {
					$core->internal($channel.": * {$cfg->get("core","realname")} {$text} *");
				}
			//}
			//else {
			//	$core->internal(" ERROR. You cannot send an ACTION to XMPP when you're not connected!");
			//}
		}
	}
	public function quit() {
		global $core,$sid,$api_stop;
	
		// START Handler/Hook
		$x = 0;
		while ($x != count($api_stop)) {
			$args = array(); // Empty for now
			call_user_func($api_stop[$x],$args);
			$x++;
		}
		if ($sid) {
			$xmpp->disconnect();
		}
		$api->log($core->lang('CLOSING'));
		die();
	}
	public function raw($text = false) {
		global $core,$sid,$scrollback,$xmpp;
		if ($sid) {
			// Sends RAW XMPP Data
			$xmpp->send($text);
		}
		else {
			$core->internal(" ERROR. Unable to send RAW Data, not connected to XMPP!");
		}
	}
	
	// Facebook removed formatting :c
	public function bold($text) {
		return "_".$text."_";
	}
	public function italic($text) {
		return "/".$text."/";
	}
	
	// Channel API is now part of the main API
	
	// Needs rewriting to return a list of users online.
	public function users($chan) {
		global $core,$userlist;
		$chan = strtolower(getWid($chan));
		if ($chan) {
			if (isset($userlist[$chan])) {
				return $userlist[$chan];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	// Needs rewriting to check if a users online.
	public function ison($user,$chan = false) {
		return true;
	}
}
// Give it a Variable
$api = new pitcapi();

// Timer Function. Doesn't need editing for FBOT
class timer {
	public function addtimer($delay = false,$rep = false,$function = false ,$args = false) {
		global $core,$timers,$scrollback;
		if ($delay == false | $function == false) {
			if (!$delay) {
				$core->internal(" {$core->lang('API_ERROR_MISSING')} DELAY {$core->lang('API_INFUNC')} TIMER->ADDTIMER");
			}
			else {
				$core->internal(" {$core->lang('API_ERROR_MISSING')} FUNCTION {$core->lang('API_INFUNC')} TIMER->ADDTIMER");
			}
			return false;
		}
		else {
			$dat = array();
			$dat['delay'] = $delay;
			$dat['rep'] = $rep;
			$dat['function'] = $function;
			$dat['args'] = $args;
			$dat['next'] = $this->calcnext($delay);
			$timers[] = $dat;
			$core->internal(" {$core->lang('API_ADDED')} '{$function}' {$core->lang('API_TMER_ADDED')} {$delay}");
			end($timers); 
			return $timers[key($timers)]; 
		}
	}
	public function deltimer($id) {
		// Deletes a timer with the specified ID.
		global $core,$timers;
		if (!$id) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} ID {$core->lang('API_INFUNC')} TIMER->DELTIMER");
		}
		else {
			if (isset($timers[$id])) {
				unset($timers[$id]);
				$core->internal(" Timer {$id} Removed.");
				return true;
			}
			else {
				$core->internal(" Timer {$id} not found!");
				return false;
			}
		}
	}
	public function checktimers() {
		global $core,$timers;
		foreach ($timers as $id => $tmr) {
			if ($tmr['next'] == time()) {
				// Trigger timer.
				call_user_func($tmr['function'], $tmr['args']);
				// Update Next Call.
				$timers[$id]['next'] = $this->calcnext($tmr['delay']);
				if ($tmr['rep'] != false) {
					// Not continuous.
					$timers[$id]['rep']--;
					if ($timers[$id]['rep'] == 0) {
						// Remove - Actually a Debug Line I never removed but in this case Its good.
						$core->internal(" Unset timer {$id} running function '{$tmr['function']}'");
						unset($timers[$id]);
					}
				}
			}
		}
	}
	public function texttosec($text) {
		global $core,$scrollback;
		// Returns the contents of $text in seconds, e.g. 1m = 60 Seconds
		if (!$text) {
			$core->internal(" {$core->lang('API_ERROR_MISSING')} TEXT {$core->lang('API_INFUNC')} TIMER->TEXTOSEC");
		}
		else {
		if (is_numeric($text)) {
			return $text;
		}
		else {
			$text = strtolower($text);
			$num = substr($text, 0, -1);
			if (substr($text,-1) === "s") {
				// Seconds
				return $num;
			}
			elseif (substr($text,-1) === "m") {
				// Mins
				return (60*$num);
			}
			elseif (substr($text,-1) === "h") {
				// Hours
				return ((60*$num)*60);
			}
			elseif (substr($text,-1) === "d") {
				// Days?!
				return (((60*$num)*60)*24);
			}
			elseif (substr($text,-1) === "w") {
				// Weeks - Really now?
				return ((((60*$num)*60)*24)*7);
			}
			else {
				// Just seconds then.
				return $num;
			}
			
		}
		}
	}
	private function calcnext($text) {
		// Calculated the next time a timer will go off.
		$sec = 0;
		$time = explode(" ",$text);
		foreach ($time as $t) {
			$sec += $this->texttosec($t);
		}
		return time()+$sec;
	}
}
$timer = new timer();
?>