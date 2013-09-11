<?php
//TODO: Delete N month unmanaged networks
//TODO: Clear cache after N time

require("nm-admin/lib/controller.class.php");
$app = new Controller;

//Clear Meta Cache
$meta_cache = scandir("nm-admin/lib/cache");
$root = "nm-admin/lib/cache/";
foreach($meta_cache as $file) {
	if((strtotime("now") - strtotime(filemtime($root.$file))) / 86400 > META_CACHE_LIFE)
		@unlink($root.$file); //delete file
}

$app->model->clearCache();
?>