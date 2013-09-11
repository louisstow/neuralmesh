<?php
require("lib/controller.class.php");
$app = new Controller;

if(isset($_POST['user']) && isset($_POST['pass'])) {
	$app->model->val->run("users.add",$_POST);//validation
	$app->model->users->create($_POST['user'],$_POST['pass'],$_GET['n']);
	Model::direct("nm-users.php?n=".$_GET['n']);
}

$app->display("header");
$app->assign("label","Register");
$app->display("user");
$app->display("footer");
?>