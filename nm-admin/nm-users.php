<?php
require("lib/controller.class.php");
$app = new Controller;

$app->display("header");
?>
<table id="tabdata">
<tr><th>Username</th><th></th></tr>
<?php $app->model->users->show($_GET['n']); ?>
</table>
<?php $app->display("footer"); ?>