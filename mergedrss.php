<?php
class MergedRSS {
	private $myFeeds = null;
	private $myTitle = null;
	private $myLink = null;
	private $myDescription = null;
	private $myPubDate = null;
	private $myCacheTime = null;
	private $fetch_timeout = null; //timeout for fetching urls in seconds (floating point)

	// create our Merged RSS Feed
	public function __construct($feeds = null, $channel_title = null, $channel_link = null, $channel_description = null, $channel_pubdate = null, $cache_time_in_seconds = 3600, $fetch_timeout = '1.2') {
		// set variables
		$this->myTitle = $channel_title;
		$this->myLink = $channel_link;
		$this->myDescription = $channel_description;
		$this->myPubDate = $channel_pubdate;
		$this->myCacheTime = $cache_time_in_seconds;
		$this->fetch_timeout = $fetch_timeout;

		// initialize feed variable
		$this->myFeeds = array();

		if (isset($feeds)) {
			// check if it's an array.  if so, merge it into our existing array.  if it's a single feed, just push it into the array
			if (is_array($feeds)) {
				$this->myFeeds = array_merge($feeds);
			} else { 
				$this->myFeeds[] = $feeds;
			}
		}
	}

	// exports the data as a returned value and/or outputted to the screen
	public function export($return_as_string = true, $output = false, $limit = null) { 
		// initialize a combined item array for later
		$items = array();	

		// loop through each feed
		foreach ($this->myFeeds as $feed_array) {
			$feed_url = $feed_array[0];
			// determine my cache file name.  for now i assume they're all kept in a file called "cache"
			$cache_file = "cache/" . $this->__create_feed_key($feed_url);

			// determine whether or not I should use the cached version of the xml
			$use_cache = false;
			if (file_exists($cache_file)) { 
				if (time() - filemtime($cache_file) < $this->myCacheTime) { 
					$use_cache = true;
				}
			}

			if ($use_cache) {
				// retrieve cached version
				$sxe = $this->__fetch_rss_from_cache($cache_file); 
				$results = $sxe->channel->item;
			} else { 
				// retrieve updated rss feed
				$sxe = $this->__fetch_rss_from_url($feed_url);
				if ( is_object($sxe) ) {
					$results = $sxe->channel->item;
				}

				if (!isset($results)) { 
					// couldn't fetch from the url. grab a cached version if we can
					if (file_exists($cache_file)) { 
						$sxe = $this->__fetch_rss_from_cache($cache_file); 
						$results = $sxe->channel->item;
					}
				} else { 
					// we need to update the cache file
					//$sxe->asXML($cache_file);
				}
			}

			if (isset($results)) { 
				// add each item to the master item list
				foreach ($results as $item) {
					if (trim($item->title) == '') {
						continue;
					}
					//convert title to utf-8 (i.e. from facebook feeds)
					$item->title = html_entity_decode($item->title, ENT_QUOTES,  'UTF-8');
					$source = $item->addChild('source', '' . $feed_array[1]);
					$source->addAttribute('url', $feed_array[2]);
					$items[] = $item;
				}
			}
		}


		// set all the initial, necessary xml data
		$xml =  "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\" xmlns:itunes=\"http://www.itunes.com/DTDs/Podcast-1.0.dtd\" >\n";
		$xml .= "<channel>\n";
		if (isset($this->myTitle)) { $xml .= "\t<title>".$this->myTitle."</title>\n"; }
		$xml .= "\t<atom:link href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."\" rel=\"self\" type=\"application/rss+xml\" />\n";
		if (isset($this->myLink)) { $xml .= "\t<link>".$this->myLink."</link>\n"; }
		if (isset($this->myDescription)) { $xml .= "\t<description>".$this->myDescription."</description>\n"; }
		if (isset($this->myPubDate)) { $xml .= "\t<pubDate>".$this->myPubDate."</pubDate>\n"; }

		// if there are any items to add to the feed, let's do it
		if (sizeof($items) >0) { 

			// sort items
			usort($items, array($this,"__compare_items"));		
	
			// if desired, splice items into an array of the specified size
			if (isset($limit)) { array_splice($items, intval($limit)); }

			// now let's convert all of our items to XML	
			for ($i=0; $i<sizeof($items); $i++) { 
				$xml .= $items[$i]->asXML() ."\n";
			}
			

		}
		$xml .= "</channel>\n</rss>";

		// if output is desired print to screen
		if ($output) { echo $xml; }
		
		// if user wants results returned as a string, do so
		if ($return_as_string) { return $xml; }
		
	}


	// compares two items based on "pubDate"	
	private function __compare_items($a,$b) {
		return strtotime($b->pubDate) - strtotime($a->pubDate);
	}

	// retrieves contents from a cache file ; returns null on error
	private function __fetch_rss_from_cache($cache_file) { 
		if (file_exists($cache_file)) { 
			return simplexml_load_file($cache_file);
		}
		return null;
	}

	// retrieves contents of an external RSS feed ; implicitly returns null on error
	private function __fetch_rss_from_url($url) {
		// Create new SimpleXMLElement instance
		try {
			//set user agent, i.e. facebook.com doesn't deliver feeds to unknown browsers
			ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36');
			$fp = fopen($url, 'r', false , stream_context_create(array('http' => array('timeout' => $this->fetch_timeout))));

			if ($fp) {
				$sxe = simplexml_load_string(stream_get_contents($fp));
			} else {
				$sxe = false;
			}
			return $sxe;
		} catch (Exception $e) {
			return null;
		}
	}

	// creates a key for a specific feed url (used for creating friendly file names)
	private function __create_feed_key($url) { 
		return preg_replace('/[^a-zA-Z0-9\.]/', '_', $url) . 'cache';
	}

}

