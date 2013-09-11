<?php
require("lib/controller.class.php");
$app = new Controller;
$app->display("header");
?>
<table id="tabdata">
<tr><th>Iterations</th><th>Start MSE</th><th>End MSE</th><th>Date</th><th class="end"><abbr title="In seconds">Exec Time</abbr></th></tr>
<?php
$app->model->train->listHistory($_GET['s']);
?>
</table>

<?php
$app->display("footer");
?>