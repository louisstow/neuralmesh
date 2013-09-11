<?php
class Navigation {

	private $struct;
	private $output = "";
	private $active = "";
	private $found = false;

	function __construct() {
		$this->struct = simplexml_load_file(Controller::$root."data/navigation.xml");
	}
	
	/**
	* Based on the navigation xml, build the nav
	* @param active The link to show as active
	* @param struct Document/structure to build
	* @param level Level of depth currently building
	*/
	function build($active=null,$struct=NULL,$level=0) {
	
		if(is_null($struct)) $struct = $this->struct;
		if(is_null($active)) $active = $this->getActive();
		$build_flag = false;
		
		$this->output .= "<ul id='lvl$level'>\n";
		foreach($struct as $child) {
			//if the href tag does not exist, find the first child that does
			$href = isset($child['href']) ? $child['href'] : $this->firstChild($child); 
			//if the href is an immediate child, set active class
			$active_class = ($active == $href) ? " class='active'" : "";
			
			$this->find($active,$child); //look for href in current children

			if($this->found) { //if found flag is raised
				$build_flag = $child;
				$active_class = " class='active'";
				$this->found = false; //reset
			}
			$qs = $this->addVars($child); //generate query string			
			$this->output .= "\t<li><a href='{$href}{$qs}'{$active_class}>".$child['name']."</a></li>\n";
			
		}
		$this->output .= "</ul>\n";
		if($build_flag) $this->build($active,$build_flag,$level+1); //build next level
	}

	/**
	* Returns the first child of a node
	* @param struct The document/structure to search for
	*/
	private function firstChild($struct) {
		if($struct->page[0]['href']) {
			return $struct->page[0]['href'];
		}
		return $this->firstChild($struct->page[0]);
	}

	/**
	 * Search for a HREF within a structs children
	 * @param href The page link to find
	 * @param struct The document/structure to search in
	 */
	private function find($href,$struct) {

		foreach($struct as $child) {
			if(isset($child['href']) && $child['href'] == $href) {
				$this->found = true;
			} else {
				$this->find($href,$child);
			}
		}
	}
	
	public function asList() {
		$this->build();
		return $this->output;
	}
	
	/**
	* Get the active page
	* @return the filename
	*/
	private function getActive() {
		$path = $_SERVER['SCRIPT_NAME'];
		return substr($path,strrpos($path,"/")+1,strlen($path));
	}
	
	private function addVars($page) {
		if(isset($page['var'])) {
			$vars = explode(",",$page['var']);
			$data = array(); //assoc array
			foreach($vars as $var) {
				if(isset($_GET[$var])) //check if the var exists in url
					$data[$var] = $_GET[$var];
			}
			return "?".http_build_query($data);
		}
		return "";
	}
}
?>
