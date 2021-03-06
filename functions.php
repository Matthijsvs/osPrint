<?php


function get_file($fname)
{
	global $InstanceCache;
	global $client;
	global $configs;

	$key = sha1($fname);

	$CachedString = $InstanceCache->getItem($key);

	if (is_null($CachedString->get())) {
		//download songfile from dropbox
		$result=stream_get_contents($client->download($fname));
		$CachedString->set($result)->expiresAfter(3600);
		$InstanceCache->save($CachedString);
		return $result;
	}else{
		//this file has been downloaded before.
		return $CachedString->get();
	}
}

function dump_set($fname){
	//add your own access token in key.php
	global $configs;
	$set = array();
	$setfile = simplexml_load_string(get_file($configs->setpath.$fname));
	if ($setfile){
		foreach ($setfile->slide_groups->slide_group as $grp) {
			switch ((string) $grp['type']){
			case 'song':
				$file =(string)$grp['name'];
				$path =$grp['path'];
				$verses= $grp['presentation'];
				$songs = parse_song($file,$path,$verses);
				$song=array();
				foreach ($songs as $k=>$s){
					$song[$k]=$s;
				}
				$set[] = array ( 'title' => $file, 'type' => "song", 'contents' => $song);
			break;

			// for now we we treat scripture and text slides the same
			case 'custom':
			case 'scripture':
				$title =  $grp->title;
				$body = array();
				$i=0;
				//todo split by line
				foreach ($grp->slides->slide as $txt) {
					$body[] = (string)$txt->body;
					$i++;
				}
				$set[] = array ( 'title' => $title, 'type' => "scripture", 'contents' => $body);
			break;
			}
		}
	}
	return array('title'=>(String)$setfile['name'],'slides' => $set);
}


function parse_song($file,$path,$verses) {
	// select only verses from setfile
	$ret = array();
	$verse = get_verses("/Beamer (1)/Songs/$path$file");
	if ($verses == ""){
		//opensong uses the order from the songfile.
		//should use the default presentation order?
		return $verse;
	}else{
		$list = explode(" ",$verses);
		foreach($list as $v){
			if (array_key_exists($v,$verse)){
				$ret[$v]=$verse[$v];
			}
		}
		return $ret;
	}
}

function get_verses($fname){
	//return an associative array with all verses in songfile
	$verses=array();
	$songfile=simplexml_load_string(get_file($fname));
	if ($songfile)
	{
		$lines = string_to_lines($songfile->lyrics);
		$currentv = Null;
		$versetext = "";
		foreach($lines as $line){
			# regex for [V_]
			#todo handle !-- slide end character
			if (preg_match("/\[([^)]+)\]/", $line, $matches))
			{
				if ($currentv != Null)
				{
					$verses[$currentv]=$versetext;
					$versetext="";
				}
				$currentv = $matches[1];
			}else{
				//todo strip newline from file?
				$versetext=$versetext.$line;
			}
		}
		$verses[$currentv]=$versetext;
		$versetext="";

	}
	return $verses;
}

function string_to_lines($strg)
{
	//https://stackoverflow.com/questions/1483497/how-to-put-string-in-array-split-by-new-line
	return explode("\n",str_replace(array("\r\n","\n\r","\r"),"\n",$strg));
}

?>

