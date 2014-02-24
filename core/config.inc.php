<?php
// PITCBots shiny new configuration system.
class configuration {
	// PITCBots v1.5+ Configuration Class
	// Read the commenting and returns to figure out how it works
	// We've included our own code for PITC's configuration which
	// can be looked at.
	// Please check for a FALSE return as NO ERRORS are displayed.
	// Return Types: FALSE, TRUE, ARRAY
	function create($name,$data = array()) {
		global $_FBOT;
		$name = strtolower($name);
		// Creates the config in memory.
		// Also useful to clear a config.
		$_FBOT['CFG'][$name] = $data;
		return true;
	}
	function set($name,$item,$value) {
		global $_FBOT;
		$name = strtolower($name);
		$item = strtolower($item);
		if (isset($_FBOT['CFG'][$name])) {
			// Set the value and return True as its worked
			$_FBOT['CFG'][$name][$item] = $value;
			return false;
		} else {
			// Configuration NOT loaded.
			return false;
		}
	}
	function exists($name) {
		global $_FBOT;
		// Lazy way to check if the config file exists on disk.
		$name = strtolower($name);
		if (file_exists("configs/{$name}.cfg")) {
			return true;
		} else {
			return false;
		}
	}
	function get($name,$item = false) {
		global $_FBOT;
		$name = strtolower($name);
		if ($item) {
			$item = strtolower($item);
		}
		// This is difficult if you're using true/false values.
		// Your best bet is to getthe whole config as an array and directly read values.
		// However this works FINE for String data.
		// This function is also useful to check if a configuration is loaded.
		if (isset($_FBOT['CFG'][$name])) {
			if (!$item) {
				// Return the whole config instead.
				return $_FBOT['CFG'][$name];
			} else {
				if (isset($_FBOT['CFG'][$name][$item])) {
					// Item Exists
					return $_FBOT['CFG'][$name][$item];
				} else {
					// No such iten.
					return false;
				}
			}
		} else {
			// Config not loaded.
			return false;
		}
	}
	function load($name) {
		global $_FBOT;
		$name = strtolower($name);
		// Load the configuration into file.
		// Supports PITC v1 Configs an JSON
		// You could use this as a DB System if you're crazy.
		if (file_exists("configs/{$name}.cfg")) {
			$dat = file_get_contents("configs/{$name}.cfg");
			if ($dat[0] == "{" || $dat[0] == "[") {
				// JSON Configuration. EASY :D!
				$config = json_decode($dat,true);
				if (is_array($config)) {
					// Decoded and loaded fine.
					$_FBOT['CFG'][$name] = $config;
					return true;
				} else {
					// Bad format?
					return false;
				}
			} else {
				// Old Clunky PITC Format. Kill with fire.
				$config = array();
				$dat = explode("\n",$dat);
				foreach ($dat as $str) {
					if (trim($str) != "") {
					$pos = strpos($str,"=");
						if ($pos != FALSE) {
							// It is data.
							$tmp = explode("=",$str);
							$var = strtolower(trim($tmp[0])); // Lazy way :3
							$val = trim(substr($str,$pos+1));
							// Check if the value is JSON
							if ($val[0] == "[" || $val[0] == "{") {
								// It's possibly JSON.
								$tmp = json_decode($val,true);
								if (is_array($tmp)) {
									$val = $tmp;
								}
							}
							if ($val != "") {
								$config[$var] = urldecode($val);
							}
						} else {
							// Bad line or comment
							// Ignore it.
						}
					}
				}
				$_FBOT['CFG'][$name] = $config;
				return true;
			}
		} else {
			// Doesnt exist.
			return false;
		}
	}
	function save($name,$human = false) {
		// Saves a config to disk.
		// human is wether its humanly easy to edit
		// This is the OLD PITC format, push FALSE to use JSON
		// By default its JSON
		global $_FBOT;
		$name = strtolower($name);
		if (isset($_FBOT['CFG'][$name])) {
			if ($human) {
				$stack = array();
				foreach ($_FBOT['CFG'][$name] as $var => $val) {
					if (!is_array($val)) {
						// Improved formatting fixes the requirement for URLENCODE
						$stack[] = "{$var}={$val}";
					} else {
						$stack[] = "{$var}=".json_encode($val);
					}
				}
				$dat = implode("\n",$stack);
				$res = file_put_contents("configs/{$name}.cfg",$dat);
				if ($res) {
					// Written
					return true;
				} else {
					// Couldn't write to file.
					return false;
				}
			} else {
				$res = file_put_contents("configs/{$name}.cfg",json_encode($_FBOT['CFG'][$name]));
				if ($res) {
					return true;
				} else {
					// Unable to write to the file.
					return false;
				}
			}
		} else {
			// Configuration is not loaded.
			return false;
		}
	}
	function unload($name) {
		global $_FBOT;
		$name = strtolower($name);
		// Deletes the config from memory.
		// REGARDLESS of changes!
		if ($name != "core") {
			if (isset($_FBOT['CFG'][$name])) {
				unset($_FBOT['CFG'][$name]);
				// Done
				return true;
			} else {
				// Didn't work.
				return false;
			}
		} else {
			// Not allowed to unload the core configuration!
			// You may load it from disk but not unload it as it
			// may cause PITCBots to crash.
			return false;
		}
	}
	function delete($name) {
		// Deletes from DISK not from memory.
		// Use unload() for that.
		global $_FBOT;
		$name = strtolower($name);
		if ($name != "core") {
			if (file_exists("configs/{$name}.cfg")) {
				$un = unlink("configs/{$name}.cfg");
				if ($un) {
					// Unload any loaded data.
					$this->unload($name);
					return true;
				} else {
					// Unable to delete the file.
					return false;
				}
			}
		} else {
			// You CANNOT unload/delete the core config file
			// While PITCBots is running!
			return false;
		}
	}
	function replace($name,$data = array()) {
		// Replaces ALL the data in the configs memory.
		// Supply no 2nd param to clear the config.
		global $_FBOT;
		$name = strtolower($name);
		if (isset($_FBOT['CFG'][$name])) {
			$_FBOT['CFG'][$name] = $data;
			return true;
		} else {
			// Not loaded.
			return false;
		}
	}
	function def($name,$data) {
		// This is a clever system allowing you to have a default config.
		// Set a name and give default data and it will be written to a file.
		$name = strtolower($name);
		$res = file_put_contents("configs/{$name}.def.cfg",json_encode($data));
		if ($res) {
			return true;
		} else {
			// Couldn't write to the file.
			return false;
		}
	}
	function getdef($name) {
		// Retrieves default data.
		$name = strtolower($name);
		if (file_exists("configs/{$name}.def.cfg")) {
			// Return the data.
			$data = file_get_contents("configs/{$name}.def.cfg");
			$data = json_decode($data,true);
			if (is_array($data)) {
				return $data;
			} else {
				// Corrupt JSON Format!
				return false;
			}
		} else {
			// Don't even exist.
			return false;
		}
	}
	function applydefault($name,$data) {
		// This function doesnt change configs in memory.
		// You pass the name of the config you want to load default data
		// for, providing it exists. See getdef() to check if it exists.
		// Then supply config data for PITCBots to match where missing data is replaced
		// with default data.
		// DATA is existing data, NAME is the config name aka NAME.def.cfg
		$name = strtolower($name);
		if (file_exists("configs/{$name}.def.cfg")) {
			$default = $this->getdef($name);
			if (is_array($default)) {
				foreach ($default as $var => $val) {
					if (!isset($data[$var])) {
						$data[$var] = $val;
					}
				}
				// Return modified data.
				return $data;
			} else {
				// Couldn't load defaults.
				// Fall back to existing data.
				return $data;
			}
		} else {
			// No default data was found.
			// We'll be kind and return the data untouched.
			echo "No file\n";
			return $data;
		}
	}
}
$cfg = new configuration();
$_FBOT['CFG'] = array();
?>