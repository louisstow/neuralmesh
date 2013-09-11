<?php
require("lib/controller.class.php");
$app = new Controller;
$app->inc("nmesh");
$data = $app->model->network->get($_GET['n']);
$nn = $app->model->network->nn;
set_time_limit(0);

if($_POST) {	
	$app->model->val->run("run",$_POST);
	
	$data = $app->model->getCache($_GET['n'].$_POST['input']);
	
	if($data === null) {
		$input = str_split($_POST['input']);
		if(count($input) != $nn->inputs) die("Incorrect number of entries!");
		$outputs = $nn->run($input);
		//save into cache
		mysql::query("cache.save",array("id"=>$_GET['n'].$_POST['input'],"network"=>$_GET['n'],"data"=>implode("|",$outputs)));
	} else {
		$outputs = explode("|",$data); //get from cache
	}
	
	$return = "";
	$count = 1;	
	foreach($outputs as $output) {
		$return .= "Output $count - <strong>".$output."</strong><br />";
		$count++;
	}
}

$app->display("header");
?>

<form action="nm-run.php?n=<?php echo $_GET['n']; ?>" method="post">
<table>
<tr><th>Input:</th><td><input type="text" name="input" maxlength="<?php echo $nn->inputs; ?>" size="40" /><input type="submit" value="Run" /></td></tr>
</table>
</form>

<?php
if(isset($return) && strlen($return)) echo "<br>".$return;
$app->display("footer");
?>