<?php
require_once('functions.php');
$set = dump_set($tmpfile);
echo json_encode($set);
?>

