<?php
/**
 * Validation class
 * @author Louis Stowasser
 */
class validation {
	
	/** Array of validation sets */
	private $set;
	/** Array of error messages */
	private $errors = array();
	/** Boolean of pass status */
	private $pass = true;
	
	/**
	* Constructor
	*/
	public function validation() {
		$vxml = simplexml_load_file(Controller::$root."data/validation.xml");

		foreach($vxml as $set) {
			$this->set[(string)$set['name']] = $set;
		}
	}
	
	/**
	* Merges arrays together
	* @param array[n] Arrays to merge
	* @returns Merged array
	*/
	static public function add() {
		$args = func_get_args();
		$data = array();
		foreach($args as $arg) {
			$data = array_merge($data, (array)$arg);
		}
		return $data;
	}

	/**
	* Wrapper for the load function. Handles error messaging
	* @param name Name of the validation set
	* @param data Associative array collection
	*/
	public function run($name,$data=array()) {
		$this->load($name,$data);
		if(count($this->errors)) {
			throw new Exception(implode("<br>",$this->errors));
			return false;
		} else return true;
	}
	
	/**
	 * Loads a validation set and records errors to an array
	 * @param name Name of the validation set
	 * @param data Form data to process
	 */
	public function load($name="default",$data=array()) {
		$set = $this->set[$name] or die("<b>Error:</b> validation set ".$name." not found");
		
		foreach($set as $index=>$rule) {
			if($index == "@attributes") continue;
			if(!isset($data[$index])) {
				if(isset($rule['required']) && $rule['required'] == "false") continue; //skip if not required
				$this->addError($rule, $index." does not exist");
				continue; //skip if failed this level
			}
			if($rule['type'] == "file") { //if type is file, perform extended validation
				$result = $this->fileValidate($data[$index],$rule);
				if(!is_null($result)) $this->addError($rule, $result);
			}
			if(!$this->validate($data[$index],$rule['type'])) { //test by type
				$this->addError($rule, $index." is not of type ".$rule['type']);
				continue; //skip if failed this level
			}
			if(isset($rule['range']) && $rule['type'] == "int") { //test against range
				$range = explode(",",$rule['range']); //create array from range attribute where 0=min and 1=max
				if(intval($data[$index]) < intval($range[0]) || intval($data[$index]) > intval($range[1])) { //if value is out of bounds
					$this->addError($rule, $index." is not between ".$range[0]." and ".$range[1]);
					continue; //skip if failed this level
				}
			}
			if(isset($rule['maxlen']) && strlen($data[$index]) > $rule['maxlen']) { //test against max length
				$this->addError($rule, $index." is too long. Must be less than ".$rule['maxlen']);
				continue; //skip if failed this level
			}
			if(isset($rule['minlen']) && strlen($data[$index]) < $rule['minlen']) { //test against min length
				$this->addError($rule, $index." is too short. Must be greater than ".$rule['minlen']);
				continue; //skip if failed this level
			}
			if(isset($rule['check'])) { //check by static function
				$class = substr($rule['check'],0,strpos($rule['check'],".")); //store the specified class
				$function = substr($rule['check'],strpos($rule['check'],".")+1,strlen($rule['check'])); //store the function
				
				eval("\$result = $class::$function(\$data[\$index]);"); //try run the assertion
				if(!$result) {
					$this->addError($rule, $index." is not valid");
					continue; //skip if failed this level
				}
			}
		}
	}
	
	/**
	* Add a new error message to the array
	* @param rule The rules array for the item being validate
	* @param msg If the message attribute does not exist, use this message
	*/
	private function addError($rule,$msg="") {
		$this->errors[] = (isset($rule['message'])) ? (string)$rule['message'] : $msg;
	}
	
	/**
	* Getter for the errors array
	* @returns array of errors
	*/
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	* Getter for the pass status
	* @returns array of errors
	*/
	public function passed() {
		return $this->pass;
	}
	
	/**
	* Print the errors in an unordered list
	* @returns The HTML error message
	*/
	public function displayErrors() {
		if(count($this->errors)) {
			$output = "<ul>";
			foreach($this->errors as $error) {
				$output .= "<li>".$error."</li>";
			}
			$output .= "</ul>";
			return $output;
		}
		return "";
	}
	
	/**
	* Extended function to further validate files
	* @param file File array or data in $_FILES
	* @param rules Array of the files rules
	* @returns Error message if there is an error
	*/
	private function fileValidate($file,$rules) {
		if(isset($rules['ext'])) { //validate against extension
			$ext = substr($file['name'],strrpos($file['name'],".")+1,strlen($file['name']));
			$valid = explode(",",$rules['ext']);
			if(!in_array($ext,$valid)) {
				return "The file ".$file['name']." is an invalid extension. Must have the following extension: ".$rules['ext'];
			}
		}
		if(isset($rules['media'])) { //validate against the media type
			$type = substr($file['type'],0,strrpos($file['name'],"/"));
			if(!strlen($type)) //unknown type, error
				return "The file ".$file['name']." is an unknown type. Must be of the following types: ".$rules['media'];
			
			$valid = explode(",",$rules['ext']);
			if(!in_array($type,$valid))
				return "The file ".$file['name']." is an invalid type. Must be in ".$rules['media'];
		}
		if(isset($rules['maxsize']) && ($file['size']/1024) > $rules['maxsize']) { //check file size
			return "The file provided must be less than ".$rules['maxsize']."kB";
		}
		if(isset($rules['minsize']) && ($file['size']/1024) < $rules['minsize']) { //check minimum file size
			return "The file provided must be greater than ".$rules['minsize']."kB";
		}
		//file passed validation
		return null;
	}
	
	/**
	 * Performs a validation on a value against a type
	 * @param value The variable to validate
	 * @param type The type of validation to perform
	 * @return Boolean if passed or failed validation
	 */
	private function validate($value,$type) {
		switch($type) {
			case "varchar":
				return self::is_alphanum($value);
				break;
			case "int":
				return self::is_num($value);
				break;
			case "binary":
				return self::is_binary($value);
				break;
			case "alpha":
				return self::is_alpha($value);
				break;
			case "signed":
				return self::is_signed($value);
				break;
			case "file":
				return true;
				break;
			case "alpha":
				return self::is_alpha($value);
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	* Check value contains only binary numbers
	*/
    static public function is_binary($value) {
    	if(!strlen($value)) return false;
		return !preg_match("/[^01]/",$value);
	}
	
	/**
	 * Checks if value is a signed integer
	 * @param $value Value to check
	 * @return Boolean if passed or failed
	 */
	static public function is_signed($value) {
		return !preg_match("/[^0-9]/",$value);
	}
	
	/**
	* Check value contains only letters, underscore and hyphen
	*/
	static public function is_alpha($value) {
		return !preg_match("/[^a-zA-Z_-\s]/",$value);
	}
	
	/**
	* Check value contains only integers, decimal or hyphen
	*/
	static public function is_num($value) {
		return preg_match("/^-?[0-9]+\.?[0-9]*$/",$value);
	}
	
	/**
	* Check value contains only alphanumeric characters and hyphen, space, underscore
	*/
	static public function is_alphanum($value) {
		return !preg_match("/[^a-zA-Z_-\s0-9]/",$value);
	}
	
	/**
	* Check value contains only alphanumeric characters
	*/
	static public function is_alphastrict($value) {
		return !preg_match("/[^a-zA-Z-0-9]/",$value);
	}
}
?>