<?php
// The Facebook Class for the bot. This gets stuff regarding users.
class facebook {
	static function get_user($id) {
		$dat = file_get_contents("http://graph.facebook.com/".$id."/");
		return json_decode($dat,true);
	}
	static function get_name($id) {
		$dat = self::get_user($id);
		return $dat['name'];
	}
	static function get_first($id) {
		$dat = self::get_user($id);
		return $dat['first_name'];
	}
	static function get_username($id) {
		$dat = self::get_user($id);
		return $dat['username'];
	}
}
$facebook = new facebook();
?>