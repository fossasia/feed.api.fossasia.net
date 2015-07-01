FOSSASIA Feed Merger
===========
RSS feed merger for FOSSASIA API communities

[![Join the chat at https://gitter.im/fossasia/api.fossasia.net](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/fossasia/api.fossasia.net?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Setup

* Clone the repo :

	```sh
	git clone https://github.com/fossasia/feed.api.fossasia.net.git
	cd feed.api.fossasia.net
	```

* Create `config.json` from `config.json.sample`, and modify it as you need.

	```sh
	cp config.json.sample config.json
	```

* Create a `cache` folder to store cached feeds

* Run `feed.php` (or access it from a php web server)

	```sh
	php feed.php
	```



## History

Our goal is to collect information about Open Source Communities and Hackspaces all over Asia. This information will be used to aggregate contact data, locations, news feeds and events.
We adopted this API from the Hackerspaces and Freifunk API, invented years before to collect decentralized data.

At the Wireless Community Weekend 2013 in Berlin there was a first meeting to relaunch freifunk.net. To represent local communities without collecting and storing data centrally, a way had to be found. Another requirement was to enable local communities to keep their data up to date easily.

Based on the Hackerspaces API (http://hackerspaces.nl/spaceapi/) the idea of the freifunk API was born: Each community provides its data in a well defined format, hosted on their places (web space, wiki, web servers) and contributes a link to the directory. This directory only consists of the name and an url per community. First services supported by our freifunk API are the global community map and a community feed aggregator.

The freifunk API is designed to collect metadata of communities in a decentral way and make it available to other users. It's not designated to be a freifunk node database or a directory of individual community firmware settings.

## Contribute

Issues & Pull Requests are highly appreciated. Check out our issues for contribution opportunities. 