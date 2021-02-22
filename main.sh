#!/usr/local/bin/bash

cd `dirname $0`

/usr/local/bin/bash lote.sh 2>&1 1>/dev/null

/usr/local/bin/php get_list.php 2>&1 1>/dev/null
/usr/local/bin/php get_members.php 2>&1 1>/dev/null

JSON_DIR="/home/xxxx/www/m-league/chart"

mv -f *.json $JSON_DIR/ 2>&1 1>/dev/null
mv -f *.csv csv/ 2>&1 1>/dev/null

DIR=csv

for TARGET_FILE in $DIR/*.csv; do

  if [ -e $TARGET_FILE ]; then
    :
  else
    echo "** ERROR ** File does not exist: $TARGET_FILE"
    continue
  fi

  LAST_FILE="${TARGET_FILE}.1"

  # ファイル内容比較
  diff -q "$TARGET_FILE" "$LAST_FILE" > /dev/null 2>&1
  if [ $? -eq 0 ]; then
    # ファイル削除
    rm -f "$TARGET_FILE"
    echo "delete...."
  fi

done

