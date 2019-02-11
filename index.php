<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once('vendor/autoload.php');

use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use Phpfastcache\Core\phpFastCache;


// Setup File Path on your config files
CacheManager::setDefaultConfig(new Config([
  "path" => sys_get_temp_dir(),
  "itemDetailedDate" => false
]));

// we cache the downloaded songs into files on our filesystem.
$InstanceCache = CacheManager::getInstance('files');


$configs = include('config.php');
$client = new Spatie\Dropbox\Client($configs->API_KEY);

if (isset($_GET['name']))
{
	$tmpfile = $_GET['name'];

	switch($_GET['type'])
	{

	case "HTML":
		include_once('set2html.php');
		break;
	case "JSON":
		include_once('set2json.php');
		break;
	case "PDF":
		include_once('set2pdf.php');
		break;
	case "PPT":
		include_once('set2ppt.php');
		break;
	default:
		include_once('set2html.php');
		break;
	}
}else{
	$result = $client->listFolder($configs->setpath);
	$ent=$result['entries'];

	echo <<<XML
<html>
<head>
<style>
body
{
font-family:arial;
margin:0px;
background:#ddd;
}
table
{
border-collapse:collapse;
}
tr
{
	background:#fff;
	border-bottom:1px solid grey;
}
tr:hover
{
	background:#ddd;
}
th
{
	border-top:1px solid grey;
	border-bottom:2px solid grey;
}
td
{
padding:8px;
}
.col
{
	border-left: 1px solid black;
	border-right: 1px solid black;
	background: #fff;
	width:60%;
	height:100%;
	margin-left:20%;
	padding:2em;
}
</style>
</head>
<body>
XML;
	echo "<div class='col'><H1>Opensong SetDump for ".$configs->church."</h1>";
	echo "<p>Please select a setfile and an action to perform.</p>";
	echo "<table>";
	echo "<th>Set name</th><th>Size</th><th>Age</th><th>Convert</th>";
	foreach($ent as $f=>$k){
		if ($k[".tag"]=="file"){
			echo "<tr><td>";
			echo $k['name'];
			echo "</td><td>";
			echo get_size($k['size']);
			echo "</td><td>";
			echo get_reltime($k['server_modified']);
			echo "</td><td>";

			echo "<a href='?type=HTML&name=".urlencode($k['name'])."'>HTML</a>&nbsp;";
			echo "<a href='?type=JSON&name=".urlencode($k['name'])."'>JSON</a>&nbsp;";
			echo "<a href='?type=PPT&name=".urlencode($k['name'])."'>PPT</a>&nbsp;";
			echo "<a href='?type=PDF&name=".urlencode($k['name'])."'>PDF</a>&nbsp;";
			echo "</td></tr>";
		}

	}
	echo "</table></div></body></html>";

}

//print filesize in closest unit.
function get_size($p)
{
	$i = 0;
	while ($p>900)
	{
		$p = $p/1024;
		$i++;
	}
	$v = array('B','kB','MB','GB','TB','PB','EB');
	return sprintf("%3.1f %s",$p,$v[$i]);
}

//print relative time duration in 2 units.
//accepts string containing ISO 8601 timestamp
function get_reltime($t)
{

	$d = new DateTime($t);
	$n = new DateTime('NOW');
	$diff = $d->diff($n);
	$v = array("y"=>"year","m"=>"month","d"=>"day","h"=>"hour","i"=>"minute","s"=>"second");
	$vs = array("y"=>"years","m"=>"months","d"=>"days","h"=>"hours","i"=>"minutes","s"=>"seconds");
	$ret='';
	$i=0;
	foreach ($v as $key => $value) {
		if ($diff->$key > 0)
		{
			if ($diff->$key == 1){
				$fmt = '%'.$key.' '.$value;	//singular
			}else{
				$fmt = '%'.$key.' '.$vs[$key];	//plural
			}	
			$ret = $ret.$diff->format($fmt);
			$i++;
			if ($i>=2)
			{
				break;
			}
			$ret=$ret.', ';
		}
	}
	return $ret;
}
?>
