<?php
/* * * * * * * * * * * *
 * FBOT v0.1 by Thomas Edwards (TMFKSOFT)*
 * * * * * * * * * * * * * 8 * * * * * * *
 * FBOT is a Facebook/XMPP Bot implementation
 * of PITC-Bots allowing you to run IRC Bot Scripts
 * on Facebook and other XMPP Servers instead of IRC!
 * * * *
 * PLEASE NOTE:
 * 	You CANNOT simply plonk a PITC Script into this framework
 * 	You MUST edit it to work with it. Certain features work differently
 *	Or have been removed Entirely!
*/

// v0.1 is being churned out as fast as humanly possbile so expect awful coding.

// Some PITC Related stuff

declare(ticks = 1);
@ini_set("memory_limit","16M"); // Ask for more memory
stream_set_blocking(STDIN, 0);
stream_set_blocking(STDOUT, 0);
error_reporting(0); // Shut PHP's Errors up so we can handle it ourselves.
set_error_handler("pitcError");
register_shutdown_function("pitcFatalError");

// Some Variables
$log_irc = true; // Log IRC to the main window as well?
$rawlog = array();
$start_stamp = time();
$rawlog = array();
$error_log = array();
$timers = array();
$_FBOT = array(); // Everything useful in this.

$_DEBUG = array(); // Used to set global vars for /dump from within functions.
$loaded = array();

 
// Some Variables we need.
$_FBOT['version'] = "0.1";
$_FBOT['append'] = "alpha";
 
echo "  [CORE] Starting FBOT-{$_FBOT['append']} v{$_FBOT['version']}\n";
 
// Include our vitals.
include 'core/xmpp/XMPP.php'; // Our XMPP Core
include 'core/api.inc.php'; // Our API
include 'core/facebook.inc.php'; // Our Facebook Core

// Load our Database and Configuration Class and ofc the Function class
if (file_exists($_SERVER['PWD']."/core/functions.inc.php")) {
	include($_SERVER['PWD']."/core/functions.inc.php");
}
else {
	die("Missing Functions.php! FBOT{$_FBOT['append']} CANNOT Function without this.");
}


if (file_exists($_SERVER['PWD']."/core/config.inc.php")) {
	include($_SERVER['PWD']."/core/config.inc.php");
}
else {
	shutdown("ERROR Loading config.inc.php!\n");
}

if (file_exists($_SERVER['PWD']."/core/database.inc.php")) {
	include($_SERVER['PWD']."/core/database.inc.php");
}
else {
	shutdown("ERROR Loading database.inc.php!\n");
}

if ($cfg->exists("core")) {
	$loaded = $cfg->load("core");
	if (!$loaded) {
		die("Unable to load configuration!");
	}
	// Fill in missing values.
	$cfg->applydefault("core",$cfg->getdef("core"));
} else {
	$cfg->create("core",$cfg->getdef("core"));
	$cfg->save("core");
	echo " [CORE] FBOT Configuration Missing!\n";
	echo " [CORE] Default configuration created please edit configs/core.cfg\n";
	shutdown();
}

// Language loading
if ($cfg->get("core","lang") !== FALSE) {
	$language = $cfg->get("core","lang");
}
else {
	$language = "en";
}
$lng = array();
// Load English as a default language.
if (file_exists("langs/en.lng")) {
		eval(file_get_contents("langs/en.lng"));
}
// Load other languages over the top of it.
if (file_exists("langs/".$language.".lng")) {
	eval(file_get_contents("langs/".$language.".lng"));
}
else {
	if (file_exists("langs/en.lng")) {
		eval(file_get_contents("langs/en.lng"));
	}
	else {
		die("Unable to load Specified Language or English Language!\n");
	}
}



// More stuffs

// Scripting interface/api
$api_commands = array();
$api_messages = array();
$api_actions = array();
$api_ctcps = array();
$api_joins = array();
$api_parts = array();
$api_connect = array();
$api_tick = array();
$api_raw = array();
$api_start = array();
$api_stop = array();

// FBOT Variables
$_FBOT['name'] = $cfg->get("core","realname");
$_FBOT['username'] = $cfg->get("core","username");
$_FBOT['address'] = $cfg->get("core","address");
$_FBOT['port'] = $cfg->get("core","port");

// Load scripts set to autoload
// Load auto scripts.
if (isset($loaded)) { $loaded = array(); }
if (file_exists($_SERVER['PWD']."/scripts/autoload")) {
	$scripts = explode("\n",file_get_contents($_SERVER['PWD']."/scripts/autoload"));
	for ($x=0;$x != count($scripts);$x++) {
		if ($scripts[$x] != "") {
			if ($scripts[$x][0] != ";") {
				$script = $_SERVER['PWD']."/scripts/".trim($scripts[$x]);
				if (file_exists($script)) {
					include_once($script);
					$loaded[] = $script;
				}
				else {
					$core->internal(" = {$lng['AUTO_ERROR']} '{$scripts[$x]}' {$lng['NOSUCHFILE']} =");
				}
			}
		}
	}
}

// START Handler/Hook
$x = 0;
while ($x != count($api_start)) {
	$args = array(); // Empty for now
	call_user_func($api_start[$x],$args);
	$x++;
}

/* Handle being terminated */
if (function_exists('pcntl_signal')) {
	/*
	 * Mac OS X (darwin) doesn't be default come with the pcntl module bundled
	 * with it's PHP install.
	 * Load it to take advantage of Signal Features.
	*/
	///* Currently broken
	pcntl_signal(SIGTERM, "shutdown");
	pcntl_signal(SIGINT, "shutdown");
	pcntl_signal(SIGHUP, "shutdown");
	pcntl_signal(SIGUSR1, "shutdown");
	//*/
}
else {
	$core->internal(" [INFO] Your installation of PHP lacks the PCNTL Module! Load it for the Shutdown handler. =");
}

// Loaded and started
$core->internal(" [INFO] FBOT{$_FBOT['append']} v{$_FBOT['version']} Started ".date('h:ia d-m-Y'));


#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$xmpp = new XMPPHP_XMPP($_FBOT['address'], $_FBOT['port'], $_FBOT['username'], $cfg->get("core","password"), "FBOT-{$_FBOT['append']}v{$_FBOT['version']}", $_FBOT['address'], $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
$xmpp->autoSubscribe();

try {
	echo "  [CORE] Connecting to the XMPP Server..\n";
    $xmpp->connect();
    while(!$xmpp->isDisconnected()) {
    	$payloads = $xmpp->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'vcard'));
    	foreach($payloads as $event) {
    		$pl = $event[1];
			if (isset($pl['from'])) {
				$ufrom = explode("@",$pl['from']);
				$ufrom = substr($ufrom[0],1);
				$username = $facebook->get_username($ufrom);
				$name = $facebook->get_name($ufrom);
			}
    		switch($event[0]) {
    			case 'message': 
    				echo " [CORE] [{$name}:{$username}] {$pl['body']}\n";
					if ($pl['body'] != "") {
						//$xmpp->message($pl['from'], $body="Hi {$name}, Thanks for sending me \"{$pl['body']}\".", $type=$pl['type']);
						//$cmd = explode(' ', $pl['body']);
						
						// Pass it onto the API
						// API TIME!
						$args = array();
						$args['nick'] = $username;
						$args['name'] = $name;
						$args['channel'] = $pl['from'];
						$args['text'] = $pl['body'];
						$args['text_array'] = explode(" ",$pl['body']);
						$x = 0;
						if (preg_match("/\*(.*)\*/i",$pl['body'],$matches)) {
							echo " Action supplied\n";
							$args['text'] = $matches[1];
							$args['text_array'] = explode(" ",$matches[1]);
							while ($x != count($api_actions)) {
								call_user_func($api_actions[$x],$args);
								$x++;
							}
						} else {
							echo " Message Supplied\n";
							while ($x != count($api_messages)) {
								call_user_func($api_messages[$x],$args);
								$x++;
							}
						}
					}
    				//if($cmd[0] == 'quit') $xmpp->disconnect();
    			break;
    			case 'presence':
					// Tells us whos online/offline
					if ($pl['show'] == "available") {
						$status = "Online";
					} else if ($pl['show'] == "unavailable") {
						$status = "Offline";
					} else {
						$status = $pl['show'];
					}
    				print "  [CORE] [STATUS] {$name} is now {$status}\n";
    				//print "  [CORE] [STATUS] {$name}/{$pl['from']} [{$status}]\n";
    			break;
    			case 'session_start':
    			    //print "Session Start\n";
			    	$xmpp->getRoster();
    				$xmpp->presence($status="Cheese! Because why not!");
    			break;
    		}
    	}
		// Check if any timers are being called.
		$timer->checktimers();
    }
} catch(XMPPHP_Exception $e) {
	echo "  [CORE] Error connecting to the XMPP Server.\n";
    shutdown($e->getMessage());
}


function pitcError($errno, $errstr, $errfile, $errline) {
	global $active,$core;
	// Dirty fix to supress connection issues for now.
	if ($errline != 171) {
		if (!isset($core)) {
			echo "FBOT PHP Error: (Line ".$errline.") [$errno] $errstr in $errfile\n";
		} else {
			$core->internal("FBOT PHP Error: (Line ".$errline.") [$errno] $errstr in $errfile");
		}
	}
}
function pitcFatalError() {
	global $core,$xmpp;
	// Dirty fix to supress connection issues for now.
	$error = error_get_last();
	if( $error !== NULL) {
		// Its a FATAL Error
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		if (!isset($core)) {
			echo "FBOT PHP FATAL Error: (Line ".$errline.") [$errno] $errstr in $errfile\n";
		} else {
			$core->internal("FBOT PHP FATAL Error: (Line ".$errline.") [$errno] $errstr in $errfile");
		}
		// Lets us perform vital stuff including API Calls before shutting down!
		shutdown("Fatal PITC Error! Please refer to your terminal.\n"); 
	} else {
		shutdown("Shutting down..");
	}
}
?>
