<?php
/**
 * Lists training sets
 * @author Louis Stowasser
 */

require("lib/controller.class.php");
$app = new Controller;
$app->display("header",false);
?>
<div id="tools">
	<fieldset>
	<legend>New Training Set</legend>
	<form action="nm-manage-set.php?action=new" method="post">
	<input type="text" name="label" />
	<input type="hidden" name="n" value="<?php echo $_GET['n']; ?>" />
	<input type="submit" value="Add" />
	</form>
	</fieldset>
</div>

<table id="tabdata">
<tr><th>Training Set Name</th><th></th></tr>
<?php 
$app->model->train->listTrainingSets($_GET['n']); //list sets in network
?>
</table>

<?php
$app->display("footer");
?>