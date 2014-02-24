<?php
if (!file_exists("data")) {
	mkdir("data");
}
$_DATABASE = array();
class database {
	function create($name) {
		global $api;
		$name = strtolower($name);
		if (file_exists("data/".$name.".db")) {
			$api-log(" [DB] {$name} already exists! Delete it or load it!");
			return false;
		} else {
			global $_DATABASE;
			$_DATABASE[$name] = array();
			return true;
		}
	}
	function insert($name,$values) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			$_DATABASE[$name][] = $values;
			return true;
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}
	}
	function update($name,$values,$cond = false) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			if ($cond) {
				// Conditional supplied
				$r = 0;
				foreach ($_DATABASE[$name] as $a_index => $a_value) {
					// Cycle each array in the database
					// cycle each value 
					foreach ($cond as $c_index => $c_value) {
						// cycle each conditional
						$preg = "/^".str_replace("%","(.*)",$c_value)."$/i";
						if (isset($a_value[$c_index])) {
							// Does the database array even have the cond index?
							if (preg_match($preg,$c_value)) {
								// So fun.
								$_DATABASE[$name][$c_index] = $values[$c_index];
								$r++;
							} else {
								echo "No match for '{$preg}' in {$c_value}!\n";
							}
						} else {
							echo "No such index as {$c_index}\n";
						}
					}
				}
				if ($r == 0) {
					$api->log(" [DB] No data was updated, nothing matched!");
				}
				return true;
			} else {
				// Update everything.
			}
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}
	}
	function replace($name,$data) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			$_DATABASE[$name] = $data;
			return true;
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}
	}
	function remove($name,$values,$cond = false) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			if ($cond) {
				// Search Stuff
				$return = array();
				$r = 0;
				foreach ($_DATABASE[$name] as $col => $row) {
					// Now the fun begins.
					foreach ($cond as $col => $val) {
						$preg = "/^".str_replace("%","(.*)",$val)."$/i";
						if (isset($row[$col])) {
							if (preg_match($preg,$row[$col])) {
								// Matches. Remove the whle row.
								unset($_DATABASE[$name][$col]);
								$r++;
							}
						}
					}
				}
				if ($r == 0) {
					$api->log(" [DB] Removed no data in {$name}, nothing matched!");
				}
				return true;
			} else {
				// Return everything.
				return $_DATABASE[$name];
			}
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}
	}
	function select($name,$cond = false) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			if ($cond) {
				// Search Stuff
				$return = array();
				foreach ($_DATABASE[$name] as $col => $row) {
					// Now the fun begins.
					foreach ($cond as $col => $val) {
						$preg = "/^".str_replace("%","(.*)",$val)."$/i";
						if (isset($row[$col])) {
							if (preg_match($preg,$row[$col])) {
								if (!isset($row['index'])) {
									$row['index'] = $col;
								}
								$return[] = $row;
							}
						}
					}
				}
				return $return;
			} else {
				// Return everything.
				return $_DATABASE[$name];
			}
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}	
	}
	function delete($name) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			if (file_exists("data/{$name}.db")) {
				unlink("data/{$name}.db");
			}
			unset($_DATABASE[$name]);
			return true;
		} else {
			$api->log(" [DB] Unable to modify data in {$name}! Database not loaded.");
			return false;
		}
	}
	function save($name) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			file_put_contents("data/{$name}.db",json_encode($_DATABASE[$name]));
			return true;
		} else {
			$api->log(" [DB] Unable to save {$name}! Database not loaded!");
			return false;
		}
	}
	function saveall() {
		global $api,$_DATABASE;
		$success = 0;
		foreach ($_DATABASE as $name => $val) {
			$name = strtolower($name);
			if (isset($_DATABASE[$name])) {
				file_put_contents("data/{$name}.db",json_encode($_DATABASE[$name]));
				$success++;
			} else {
				$api->log(" [DB] Unable to save {$name}! Database not loaded!");
			}
		}
		if ($success === count($_DATABASE)) {
			return true;
		} else {
			$err = (count($_DATABASE) - $success);
			$api->log(" [DB] Error saving {$err} databases!");
			return false;
		}
	}
	function load($name) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (!file_exists("data/".$name.".db")) {
			$api->log(" [DB] Unable to load {$name}! Database doesn't exist!");
			return false;
		} else {
			if (isset($_DATABASE[$name])) {
				unset($_DATABASE[$name]);
			}
			$_DATABASE[$name] = json_decode(file_get_contents("data/".$name.".db"),true);
			return true;
		}
	}
	function unload($name) {
		global $api,$_DATABASE;
		$name = strtolower($name);
		if (isset($_DATABASE[$name])) {
			unset($_DATABASE[$name]);
			return true;
		} else {
			$api->log(" [DB] Unable to unload {$name}! Database not loaded!");
			return false;
		}
	}
}
$db = new database();
?>