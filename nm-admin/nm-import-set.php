<?php
/**
 * Import training set
 * @author Louis Stowasser
 */
 
require("lib/controller.class.php");
$app = new Controller;
$data = $app->model->val->add($_POST,$_FILES);
$app->model->val->run("import",$data);

$content = file_get_contents($_FILES['file']['tmp_name']);
$data = explode("\n",$content);
foreach($data as $line) {
	$input = substr($line,0,strpos($line,":"));
	$output = substr($line,strpos($line,":")+1,strlen($line));
	if(validation::is_binary($input) && validation::is_binary($output))
		mysql::query("pattern.add",array("id"=>$_POST['id'],"pattern"=>$input,"output"=>$output));
}
Model::direct();
?>
