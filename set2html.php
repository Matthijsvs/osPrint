<html>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<Style>
body {
    font-family: Arial, Helvetica, sans-serif;
}
</style>
<body>
<?php
require_once('functions.php');
$set = dump_set($tmpfile);

#html part
echo "<h1>".$set['title']."</h1>";
foreach ($set['slides'] as $s){
	echo "<h2>".$s['title']."</H2>";
	foreach ($s['contents'] as $key=>$c){
		if ($s['type']=="song")	{
			echo "<h3>$key</h3>";
		}
		echo "<p>$c</p>";
	}
}


?>
</body>
</html>
