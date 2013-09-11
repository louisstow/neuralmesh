<?php
class Model {

	public static $OUTPUT = 0;
	public static $LAYER = 1;
	public static $INPUT = 2;
	public static $NEURON = 3;
	public static $NETWORK = 4;
	public static $SET = 5;
	public static $PATTERN = 6;

	function __construct() {
		// Plugins should be imported here:
		require_once(Controller::$root."proxy/mysqli.class.php");
		require_once(Controller::$root."proxy/navigation.class.php");
		require_once(Controller::$root."proxy/network.class.php");
		require_once(Controller::$root."proxy/train.class.php");
		require_once(Controller::$root."proxy/validation.class.php");
		require_once(Controller::$root."proxy/users.class.php");
		// But not past here :)
		mysql::init(); //specify XML files
		$this->nav = new Navigation;
		$this->assets = array("global.css");
		$this->network = new network;
		$this->train = new train;
		$this->val = new validation;
		$this->users = new users;
		$this->year = date("Y");
		if(isset($_SESSION['name'])) $this->user = $_SESSION['name'];
	}
	
	static function direct($url=NULL) {
		if(is_null($url)) $url = $_SERVER['HTTP_REFERER'];
		header("Location: ".$url);
	}
	
	function encode($nn) {
		return gzcompress(serialize($nn));
	}
	
	function decode($nn) {
		return unserialize(gzuncompress($nn));
	}
	
	function loadAssets() {
		$output = "";
		foreach($this->assets as $file) {
			$ext = substr($file, strrpos($file, '.') + 1);
			if($ext == "js") {
				$output .= "<script type='text/javascript' src='assets/$file'></script>\n";
			} else if($ext == "css") {
				$output .= "<link href='assets/$file' type='text/css' rel='stylesheet' />\n";
			}
		}
		return $output;
	}
	
	function getCache($id) {
		$q = mysql::query("cache.get",array("id"=>$id));
		if($q->num_rows) {
			$data = $q->fetch_array();
			return $data[0];
		}
		return null;
	}
	
	function saveCache($hash,$id,$data) {
		mysql::query("cache.save",array("id"=>$hash,"network"=>$id,"data"=>$data));
	}
	
	/**
	 * Update the cache with the new outputs
	 * @param $id ID of the network
	 * @param $data Training data
	 */
	function update_cache($id,$data,$nn) {
		foreach($data as $set) {
			$inputs = str_split(trim($set['pattern']));
			$outputs = $nn->run($inputs);
			mysql::query("cache.save",array("id"=>$id.trim($set['pattern']),"network"=>$id,"data"=>implode("|",$outputs)));
		}
	}
	
	function clearCache() {
		mysql::query("cache.clear",array("n"=>CACHE_LIFE),true);
		mysql::query("networks.clear",array("n"=>UNMANAGED_LIFE),true);
	}
	
	/**
	 * Quickly caches one pattern and output from the nmesh cache
	 * @param $id Network ID
	 * @param $input Input string
	 * @param $data Output array
	 */
	function quick_cache($id,$input,$data) {
		mysql::query("cache.save",array("id"=>$id.trim($input),"network"=>$id,"data"=>implode("|",$data)));
	}
}
?>