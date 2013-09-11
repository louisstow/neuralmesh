<?php
if(!isset($_POST['request'])) die("No posted data");
require("nm-controller.php");

$action = $_SERVER['QUERY_STRING'];
$xml = new SimpleXMLElement(stripslashes($_POST['request'])) or die("Invalid XML");

//Create a network
if($action == "create") {
	//validate user
	if(users::find((string)$xml->auth,true) === false) die("Authkey invalid");
	$nn = NeuralMesh::createNetwork((int)$xml->inputs,(int)$xml->outputs,(int)$xml->hidden,(int)$xml->layers);
	echo "<response><authkey>{$nn->authkey}</authkey></response>"; exit;
} else {
	$nn = NeuralMesh::getNetwork($xml->auth);
}

switch($action) {

	case "destory":
		$nn->destroy();
		break;
	/**
	 * Run the network
	 */
	case "run":
		$outputs = $nn->run((string)$xml->input);
		echo "<response><outputs>";
		foreach($outputs as $output) {
			echo "<output>";
			echo number_format($output,ROUND_DECIMAL_PLACE);
			echo "</output>";
		}
		echo "</outputs></response>";
		break;
	/**
	 * Train the network based on a set
	 */
	case "train":
		$nn->train((string)$xml->pattern,(string)$xml->output,(int)$xml->epochs);
		break;
	
	/**
	 * Train the network based on multiple sets
	 */
	case "bulk":
	default:
		ob_start();
		header("Connection: close\r\n");
		header("Content-Encoding: none\r\n");
		header("Content-Length: 0"); 
		ob_end_flush();     // Strange behaviour, will not work
		flush();            // Unless both are called !
		ob_end_clean();
		
		$epochs = (int)$xml->epochs;

		foreach($xml->sets->set as $set) {
			$inputs = (string)$set->pattern;
			$outputs = (string)$set->output;

			$nn->train($inputs,$outputs,$epochs);	
		}
		break;
}
?>