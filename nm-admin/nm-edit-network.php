<?php
require("lib/controller.class.php");
$app = new Controller;

if($_POST && $app->model->val->run("network.edit",$_POST)) {
	$app->model->network->updateNetwork($_POST['id'],$_POST['label'],$_POST['lr'],
									 	$_POST['mse'],$_POST['epoch'],$_POST['momentum']);
	Model::direct("nm-network.php?n=".$_POST['id']);
}

$app->display("header");
$data = $app->model->network->get($_GET['n']);
?>
<form action="nm-edit-network.php?n=<?php echo $_GET['n']; ?>" method="post">
<table id="tabform">
<tr><th>Name:</th><td><input type="text" name="label" value="<?php echo $data['networkName'] ?>" /></td></tr>
<tr><td colspan="2"><strong>Training</strong></td></tr>
<tr><th>Learning Rate:</th><td><input type="text" name="lr" value="<?php echo $data['learningrate'] ?>" /></td></tr>
<tr><th>Target MSE:</th><td><input type="text" name="mse" value="<?php echo $data['targetmse'] ?>" /></td></tr>
<tr><th>Epoch Max:</th><td><input type="text" name="epoch" value="<?php echo $data['epochmax'] ?>" /></td></tr>
<tr><td colspan="2"><strong>Advanced</strong></td></tr>
<tr><th>Momentum Rate:</th><td><input type="checkbox" name="enablem" <?php if($data['momentumrate'] > 0) echo 'checked="checked"'; ?> class='cb' /> <input type="text" name="momentum" value="<?php echo $data['momentumrate'] ?>" class='cb' /></td></tr>
</table>

<input type="hidden" name="id" value="<?php echo $_GET['n']; ?>" />
<input type="submit" value="Update" class="button" />
</form>

<?php
$app->display("footer");
?>