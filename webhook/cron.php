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

// Setup File Path on your config files
CacheManager::setDefaultConfig(new Config([
  "path" => sys_get_temp_dir(), 
]));

$InstanceCache = CacheManager::getInstance('files');

$configs = include('../config.php');

//$client = new Spatie\Dropbox\Client($configs->API_KEY);

//format delaytime setting as DateInterval, add to the saved time and check if is in the past.
$interval = sprintf("PT%dM",$configs->delaytime);
$timer = $InstanceCache->getItem("timer")->get();
$timer->add(new DateInterval($interval));
$now = new DateTime('NOW');

if ($now>$timer)
{
	$CachedString = $InstanceCache->getItemsByTag("todo");
	foreach($CachedString as $cs)
	{
		print $cs->get()."<br>";
		print $cs->getKey()."<br>";
	}
}



?>
