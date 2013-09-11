<?php
/**
 * Edit training set patterns
 * @author Louis Stowasser
 */
 
require("lib/controller.class.php");
$app = new Controller;
$app->display("header");

$app->map($_GET); //take the vars from URL for the template
$app->display("set");
?>



<table id="tabdata">
<tr><th>Input</th><th></th><th>Output</th><th></th></tr>
<?php
echo $app->model->train->listPatterns($_GET['s']);
?>
</table>

<?php
$app->display("footer");
?>