<?php
session_start();
include_once('key.php');
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
	//echo "setbestand wordt opgeslagen..<br>";
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

	echo "<html>";
	echo "<body style='font-family:arial'>";
	echo "<H1>Opensong SetDump</h1>";
	echo "<p>Selecteer een setfile uit de beamteam dropbox</p>";
	echo "<table style='background:#dddddd'>";
	echo "<th>Naam</th><th>groote</th><th>datum</th><th>Converteer</th>";
	foreach($ent as $f=>$k){
		if ($k[".tag"]=="file"){
			#$now = new DateTime;
			#$ago = new DateTime($k[server_modified]);
			#$diff = $now->diff($ago);

			echo "<tr><td>";
			echo $k[path_display];
			echo "</td><td>";
			echo $k[size]." bytes";
			echo "</td><td>";
			echo $k[server_modified];
			echo "</td><td>";

			echo "<a href='?type=HTML&id=".$k[id]."&name=".$k[name]."'>HTML</a>&nbsp;";
			echo "<a href='?type=JSON&id=".$k[id]."&name=".$k[name]."'>JSON</a>&nbsp;";
			echo "<a href='?type=PPT&id=".$k[id]."&name=".$k[name]."'>PPT</a>&nbsp;";
			echo "<a href='?type=PDF&id=".$k[id]."&name=".$k[name]."'>PDF</a>&nbsp;";
			echo "</td></tr>";
		}

	}
	echo "</table>";

}
?>
