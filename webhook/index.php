<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	file_put_contents('/tmp/webhook_log',"File request\n",FILE_APPEND);
	include('key.php');
	include_once('upload.php');
	$resp = file_get_contents('php://input');
	#$list = json_decode($resp,true);			//if we need to support multiple users, keep track of users in response

	$cursor = file_get_contents("/tmp/cursor", false);	//cursor is needed to continue file listing from previous state
	if ($cursor === False)	//no scan  has been done
	{

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
		file_put_contents("/tmp/cursor",$list['cursor']);
	}else{

		$url = 'https://api.dropboxapi.com/2/files/list_folder/continue';
		$data = array('cursor' => $cursor);

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
		file_put_contents("/tmp/cursor",$list['cursor']);

		$ent=$list['entries']; // changed files
		foreach($ent as $f=>$k){
			if ($k[".tag"]=="file"){
				file_put_contents('/tmp/webhook_log',"json: ".print_r($k,true)."\n",FILE_APPEND);
				upload_from_web(urlencode($k[name]));
			}
		}
	}
	fclose($log);
}else{
	//for verification we must support challenge-response
	if (isset($_GET['challenge']))
	{
		header('Content-Type: text/plain');
		header('X-Content-Type-Options: nosniff');
		echo $_GET['challenge'];
	}
}
?>
