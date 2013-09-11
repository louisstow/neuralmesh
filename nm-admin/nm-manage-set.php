<?php
require("lib/controller.class.php");
$app = new Controller;

switch($_GET['action']) {
	case "remove":
		train::validate($_GET['s']);
		mysql::query("pattern.remove",array("id"=>$_GET['s']));
		break;
		
	case "add":
		$app->model->val->run("trainingset",$_POST);
		mysql::query("pattern.add",array("pattern"=>$_POST['input'],
										 "id"=>$_POST['id'],
										 "output"=>$_POST['output']));
		break;
		
	case "rename":
		$app->model->val->run("setrename",$_POST);
		mysql::query("train.update",array("label"=>$_POST['label'],"id"=>$_POST['id']));
		break;
		
	case "delete":
		train::validate($_GET['s']);
		
		mysql::query("train.remove",array("id"=>$_GET['s']));
		break;
		
	case "new":
		$app->model->val->run("newset",$_POST);
		mysql::query("train.add",array("id"=>$_POST['n'],"label"=>$_POST['label']));
		break;
		
}
Model::direct($_SERVER['HTTP_REFERER']);
?>
