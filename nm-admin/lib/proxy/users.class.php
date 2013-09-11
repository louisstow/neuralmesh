<?php
class users {
	
	private $whitelist = "index";
	private $secure_folder = "nm-admin";
	
	//Constructor deals with session management
	function __construct() {
		session_start();
		$this->check();
		//restrict users from seeing other networks
		if(isset($_GET['n']) && !network::validate($_GET['n'])) throw new Exception("Network not found!");
	}
	
	function check() {
		$current = $_SERVER['SCRIPT_FILENAME'];
		if(strpos($current,$this->secure_folder) !== false) {
			$whitelist = explode(",",$this->whitelist);
			$flag = true; //assume we have to check
			foreach($whitelist as $page) {
				if(strpos($current,$page.".php")) { $flag = false; break; }
			}
			if($flag && !isset($_SESSION['id'])) {
				Model::direct("index.php");
				die();
			}
		}
	}
	
	function login($user,$pass) {
		$q = mysql::query("users.login",array("user"=>$user,"pass"=>$pass));
		if($q->num_rows) {
			$data = $q->fetch_array();
			$_SESSION['id'] = $data[0];
			$_SESSION['name'] = $user;
		}
		return ($q->num_rows);
	}
	
	function create($name,$pass,$network) {
		mysql::query("users.add",array("name"=>$name,"pass"=>$pass));
		$id = mysql::last_id();
		mysql::query("users.link",array("user"=>$id,"network"=>$network));
	}
	
	function remove($id) {
		mysql::query("users.remove",array("id"=>$id));
	}
	
	function show($network) {
		$q = mysql::query("users.list",array("n"=>$network));
		if($q->num_rows) {
			$data = mysql::fetch_all($q);
			foreach($data as $row) {
				echo "<tr><td>".$row['userName']."</td>";
				echo "<td><a href='nm-user-manage.php?action=delete&u={$row['userID']}&n=$network'>
				<img src='images/cross.png'/></a></td></tr>";
			}
		} else {
			echo "<tr colspan='2'><td>No users found!</td></tr>";
		}
	}
	
	function link($user,$network) {
		mysql::query("users.link",array("user"=>$user,"network"=>$network));
	}
	
	function delink($user,$network) {
		mysql::query("users.delink",array("user"=>$user,"network"=>$network));
		$q = mysql::query("users.linked",array("user"=>$user));
		$data = $q->fetch_array();
		return $data[0];
	}
	
	static function find($user,$hash=false) {
		$name = ($hash) ? "users.hashfind" : "users.find";
		$q = mysql::query($name,array("name"=>$user));
		if($q->num_rows) {
			$data = $q->fetch_array();
			return $data[0];	
		}
		return false;
	}
}
?>