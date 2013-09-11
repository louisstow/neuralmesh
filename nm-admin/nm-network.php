<?php
/**
 * Network main page
 * @author Louis Stowasser
 */
require("lib/controller.class.php");
$app = new Controller;
$app->inc("nmesh");
$app->assign("assets",array("global.css","main.js"));
$app->display("header");
$data = $app->model->network->get($_GET['n']);
$nn = $app->model->network->nn;
?>

<div id="tree">
<?php
echo "<div>Inputs: <span class='number'>".$nn->inputs."</span><br />";
echo "Outputs: <span class='number'>".$nn->outputs."</span></div>";

$tree = $app->model->getCache($_GET['n']."tree");
echo ($tree===null) ? $app->model->network->buildTree($_GET['n'],$nn) : $tree;
?>
</div>

<?php
$app->assign("n",$_GET['n']);
$app->assign("authkey",$data['authkey']);
$app->assign("layer",model::$LAYER);
$app->assign("neuron",model::$NEURON);
$app->assign("input",model::$INPUT);
$app->assign("output",model::$OUTPUT);

$app->display("structure");
$app->display("footer"); 
?>