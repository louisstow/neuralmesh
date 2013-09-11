<?php
class Controller {
	
	public static $root;
	public static $instance;
	
	function __construct() { 
		set_exception_handler(array("Controller","exception_handle"));
		self::$instance = $this;
		self::$root = str_replace("\\","/",dirname(__FILE__))."/";
		require(self::$root."../nm-settings.php");
		require(self::$root."model.class.php");
		$this->model = new Model;
	}
	
	function evaluate($matches) {
		if(preg_match("/\s*import\s*:\s*/",$matches[1]) != false) {
			$filename = preg_replace("/\s*import\s*:\s*/","",$matches[1]);
			return $this->compile(file_get_contents(self::$root."templates/$filename.template.html"));
		} else {
			@eval("\$var = \$this->model->$matches[1];");// or die("ERROR");
			return isset($var) ? $var : "";
		}
	}
	
	function compile($code) {
		return preg_replace_callback("/\{!(\S*)\}/",array( &$this, 'evaluate'),$code);
	}
	
	function display($template,$cache=false,$expiry=300) {
		$page = self::$root."cache/".md5($template.$_SERVER['SCRIPT_NAME']).".cache";
		if($cache == true && file_exists($page) && (time()-$expiry) < filemtime($page)) {
			include($page);
		} else {
			$content = $this->compile(file_get_contents(self::$root."templates/$template.template.html"));
			echo $content;
			if($cache == true) $this->write_cache($content,$page);
		}
	}
		
	function write_cache($content,$page) {
		file_put_contents($page,$content);
	}
	
	function assign($name,$value) {
		$this->model->{$name} = $value;
	}
	
	function map($data) {
		foreach($data as $var=>$value)
			$this->assign($var,$value);
	}
	
	function load($name,$safename=NULL) {
		$safename = (is_null($safename)) ? strtolower($name) : $safename;
		require("proxy/".$name.".class.php");
		eval("\$this->model->$safename = new $name;");
	}
	
	function inc($name) {
		require("proxy/".$name.".class.php");
	}
	
	function getInstance() {
		if(Controller::$instance === null) Controller::$instance = new Controller;
		return Controller::$instance;
	}
	
	static function exception_handle($exception) {
		ob_end_clean(); //clear what has been output
		$app = Controller::$instance;
		$app->assign("error",$exception->getMessage());
		$app->display("error");
		die();
	}
}
?>