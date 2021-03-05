#!/usr/local/bin/bash

cd `dirname $0`

/usr/local/bin/curl --silent https://m-league.aja0.com?update_feedwordpress=1 2>&1 1>/dev/null

sleep 10 2>&1 1>/dev/null

/usr/local/bin/php ./wp/news_post.php 2>&1 1>/dev/null

