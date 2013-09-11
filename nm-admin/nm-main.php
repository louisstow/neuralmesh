<?php
/**
 * Main admin page
 * @author Louis Stowasser
 */
require("lib/controller.class.php");
$app = new Controller;

$app->assign("nlist",$app->model->network->listNetworks());
$data = $app->model->network->getStats($_SESSION['id']);
$app->map($data);
$app->assign("auth",md5($_SESSION['name']));
$app->display("main"); 
?>
