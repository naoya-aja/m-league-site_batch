#!/usr/local/bin/bash

cd `dirname $0`

/usr/local/bin/curl --silent https://m-league.aja0.com?update_feedwordpress=1

sleep 10

/usr/local/bin/php ./wp/news_post.php

sleep 5

/usr/local/php/7.4/bin/php ./wp/set_featured_image_mjnew.php
