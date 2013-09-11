<?php
/*
 * Create a new network from this page
 * @author Louis Stowasser
 */

require("lib/controller.class.php");
$app = new Controller;

if($_POST) { //form action
	if($app->model->val->run("network.data",$_POST)) {
		$app->inc("nmesh");
		
		//Add empty network
		$authkey = sha1(uniqid());
		$momentum = isset($_POST['enablem']) ? $_POST['momentum'] : 0;
		$id = $app->model->network->add($_POST['label'],$authkey,"managed",$_POST['lr'],$momentum,$_POST['mse'],$_POST['epoch']);
		
		//Create the new network instance
		$nn = new nmesh($_POST['inputs'],$_POST['outputs'],$_POST['neurons'],$_POST['layers'],
						$_POST['bias'],$_POST['wrange']);
		
		$app->model->network->save($nn,$id); //insert the snapshot
		$app->model->users->link($_SESSION['id'],$id);
		Model::direct("nm-main.php");
	}
}
?>