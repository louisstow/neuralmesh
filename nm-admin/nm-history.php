<?php
require("lib/controller.class.php");
$app = new Controller;
$app->display("header");
?>
<table id="tabdata">
<tr><th>Iterations</th><th>Start MSE</th><th>End MSE</th><th>Date</th><th><abbr title="In seconds">Exec Time</abbr></th><th class="end"><abbr title="yes/no">Supervised</abbr></th></tr>
<?php
$app->model->train->listAllHistory($_GET['n']);
?>
</table>

<?php
$app->display("footer");
?>