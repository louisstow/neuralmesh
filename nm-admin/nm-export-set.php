<?php
/**
 * Exports training set
 * @author Louis Stowasser
 */
require("lib/controller.class.php");
$app = new Controller;

set_time_limit(300);
if(!$_POST['file']) die("No Post Data!");

$app->display("header");

$q = mysql::query("pattern.getAll",array("id"=>$_POST['id']));
file_put_contents("lib/cache/".$_POST['file'].".nms",""); //clear file
while($row = $q->fetch_assoc()) {
	//Append data to file
	file_put_contents("lib/cache/".$_POST['file'].".nms",
					  $row['pattern'].":".$row['output']."\n",
					  FILE_APPEND);
}
$app->assign("file",$_POST['file']);
$app->display("export");
$app->display("footer");
?>