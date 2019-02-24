<?php
$debug=0;
if (isset($_GET['debug']))
{
	$debug = True;
	echo "Debug on\n";
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	error_reporting(-1);
}
require_once('../vendor/autoload.php');
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use Phpfastcache\Core\phpFastCache;

if ($_SERVER['REQUEST_METHOD'] === 'POST' or $debug) {
	
	file_put_contents('/tmp/webhook_log',"File request\n",FILE_APPEND);

	// Setup File Path on your config files
	CacheManager::setDefaultConfig(new Config([
	  "path" => sys_get_temp_dir(), 
	]));

	$InstanceCache = CacheManager::getInstance('files');

	$configs = include('../config.php');

	$client = new Spatie\Dropbox\Client($configs->API_KEY);

	include_once('upload.php');
	$resp = file_get_contents('php://input');
	#$list = json_decode($resp,true);			//if we need to support multiple users, keep track of users in response

	$CachedString = $InstanceCache->getItem('cursor');
	if (!$CachedString->isHit()) {
		//this directory has not been listed
		$result = $client->listFolder($configs->setpath);
		$CachedString->set($result['cursor'])->expiresAfter(0);
		$InstanceCache->save($CachedString);
		$ent=$result['entries'];
	}else{
		//there has been an update to this directory
		$result = $client->listFolderContinue($CachedString->get());
		$CachedString->set($result['cursor'])->expiresAfter(0);
		$InstanceCache->save($CachedString);
		$ent=$result['entries'];
	}

	$timer=$InstanceCache->getItem("timer")->set(new DateTime())->expiresAfter(0);
	$InstanceCache->save($timer);

	//we loop over all files in the listing and cache them with a Todo tag
	foreach($ent as $f=>$k){
		if ($k[".tag"]=="file"){
			$new=$InstanceCache->getItem(sha1($k['name']))->set($k['name'])->expiresAfter(0)->addTag("todo");
			$InstanceCache->save($new);
		}
	}

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
