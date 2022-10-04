#!/usr/local/bin/bash

# 1日1回実行

cd `dirname $0`

# 関連記事挿入 キンマWEB
/usr/local/bin/php ./wp/update_game_post_kinmaweb.php

# 関連記事挿入 麻雀ウォッチ
/usr/local/bin/php ./wp/update_game_post_mjnew.php

# 本日分下書き投稿
/usr/local/bin/php ./wp/insert_draft_game_post.php
