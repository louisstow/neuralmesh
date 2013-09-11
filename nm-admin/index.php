<?php
require("lib/controller.class.php");
$app = new Controller;

if(isset($_POST['user']) && isset($_POST['pass'])) {
	if($app->model->users->login($_POST['user'],$_POST['pass'])) {
		Model::direct("nm-main.php");
	} else throw new Exception("User not found!");
}
$app->assign("label","Login");
$app->display("login");
?>
