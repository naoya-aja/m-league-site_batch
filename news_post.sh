#!/usr/local/bin/bash

cd `dirname $0`

/usr/local/bin/curl --silent https://m-league.aja0.com?update_feedwordpress=1

sleep 10

/usr/local/bin/php ./wp/news_post.php
