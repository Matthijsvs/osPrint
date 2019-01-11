<?php
session_start();
include('key.php');
if (isset($_GET['id']))
{
	$tmpfile = "/tmp/".substr($_GET['id'],3);
	$_SESSION["rawid"] = $_GET['id'];
	//$_SESSION["name"] = $_GET['name'];


	$url = "https://content.dropboxapi.com/2/files/download";
	$data = array('path' => $_SESSION['rawid']);
	$options = array(
		'http' => array(
		    'header'  => "Authorization: Bearer ".$API_KEY."\r\n".
						"Dropbox-API-Arg:".json_encode($data),
		    'method'  => 'POST'

		)
	);

	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);

	if ($result === FALSE) { echo "error";/* Handle error */ }

	$file = $_SESSION["id"];

	//flush();
	file_put_contents($tmpfile,$result);
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
	$url = 'https://api.dropboxapi.com/2/files/list_folder';
	$data = array('path' => '/Beamer (1)/sets/',
				"recursive" => False,
				"include_media_info" => False,
				"include_deleted" => False,
		"include_has_explicit_shared_members" => False);

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
		    'header'  => "Authorization: Bearer ".$API_KEY."\r\n".
						 "Content-Type: application/json\r\n",
		    'method'  => 'POST',
		    'content' => json_encode($data)
		)
	);

	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { echo "error";/* Handle error */ }


	$list = json_decode($result,true);

	$ent=$list['entries'];

	echo <<<XML
<html>
<head>
<style>
body
{
font-family:arial;
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
</style>
</head>
<body>
XML;
	echo "<H1>Opensong SetDump</h1>";
	echo "<p>Selecteer een setfile uit de beamteam dropbox</p>";
	echo "<table>";
	echo "<th>Naam</th><th>groote</th><th>datum</th><th>Converteer</th>";
	foreach($ent as $f=>$k){
		if ($k[".tag"]=="file"){
			#$now = new DateTime;
			#$ago = new DateTime($k[server_modified]);
			#$diff = $now->diff($ago);

			echo "<tr><td>";
			echo $k[name];
			echo "</td><td>";
			echo get_size($k['size']);
			echo "</td><td>";
			echo get_reltime($k[server_modified]);
			echo "</td><td>";

			echo "<a href='?type=HTML&id=".$k[id]."&name=".$k[name]."'>HTML</a>&nbsp;";
			echo "<a href='?type=JSON&id=".$k[id]."&name=".$k[name]."'>JSON</a>&nbsp;";
			echo "<a href='?type=PPT&id=".$k[id]."&name=".$k[name]."'>PPT</a>&nbsp;";
			echo "<a href='?type=PDF&id=".$k[id]."&name=".$k[name]."'>PDF</a>&nbsp;";
			echo "</td></tr>";
		}

	}
	echo "</table></body></html>";

}
function get_size($p)
{
	$i = 0;
	while ($p>900)
	{
		$p = $p/1024;
		$i++;
	}
	$v = array('B','kB','GB','TB','PD','EB');	
	return sprintf("%3.1f %s",$p,$v[$i]);
}
function get_reltime($t)
{
	$d = new DateTime($t);
	$n = new DateTime('NOW');
	$diff = $d->diff($n);
	$v = array("y"=>"years","m"=>"months","d"=>"days","h"=>"hours","i"=>"minutes");
	$ret='';
	foreach ($v as $key => $value) {
		if ($diff->$key > 0)
			$fmt = '%'.$key.' '.$value.',';
			$ret = $ret.$diff->format($fmt);
	}
	return $ret;
}
?>
