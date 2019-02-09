<?php
function upload_from_web($fname){
	//store an file into the dropbox. the timeout is 5 minutes, so we can generate on the fly.
	$configs = include('key.php');
	$url = 'https://api.dropboxapi.com/2/files/save_url';
	$data = array('path' => "/Beamer (1)/Pdf/$fname.pdf",
				"url" => $configs->serverurl."/index.php?type=PDF&name=".$fname);
	
	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
		    'header'  => "Authorization: Bearer ".$$configs->API_KEY."\r\n".
						 "Content-Type: application/json\r\n",
		    'method'  => 'POST',
		    'content' => json_encode($data)
		)
	);

	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { echo "error";/* Handle error */ }

	$list = json_decode($result,true);
}
?>
