<?php
include_once("mergedrss.php");
include_once("JsonpHelper.php");
if ( ! empty($_GET["category"]) ) {
	$category = $_GET["category"];
} else {
	$category = "blog";
}

$configs = file_get_contents("config.json");
$configs = json_decode($configs, true);
$communities = $configs['ffGeoJsonUrl'];
$urls = array();

//load combined api file
$api = file_get_contents($communities);
$json = json_decode($api, true);
$geofeatures = $json['features'];

// place our feeds in an array for categories with static feeds
switch ($category) {
	case "blog":
		$feeds = array(
		);
		break;
	case "podcast":
		$feeds = array(
		);
		break;
	default:
		$feeds = array();
}
		

foreach($geofeatures as $feature)
{
	if ( ! empty($feature['properties']['feeds'] ) ) {
		foreach($feature['properties']['feeds'] as $feed )
		{
			if ( ! array_key_exists($feature['properties']['url'], $urls) && ! empty($feed['category']) && $feed['category'] == $category && !empty($feed['type']) && $feed['type'] == "rss" ) {
				$feeds[$feature['properties']['shortname']] = array($feed['url'],$feature['properties']['name'], $feature['properties']['url']);
				$urls[$feature['properties']['url']] = "1";
			}
		}
	}
}

// set the header type
header("Content-type: text/xml");
// set an arbitrary feed date
$feed_date = date("r", mktime(10,0,0,9,8,2010));

// Create new MergedRSS object with desired parameters
$MergedRSS = new MergedRSS($feeds, "Fossasia Community Feeds", "http://www.fossasia.net/", "This the merged RSS feed of RSS feeds of our community", $feed_date);

//Export the first 10 items to screen
$result = $MergedRSS->export(true, false, (array_key_exists('limit', $_GET) ? $_GET['limit'] : 1), (array_key_exists('source', $_GET) ? $_GET['source'] : 'all'));

JsonpHelper::outputXML($result);
