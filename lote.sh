#!/usr/local/bin/bash

# https://wiki.ducca.org/wiki/ファイルをローテーションさせるシェルスクリプト
#
# 最新の TARGET_FILE が TARGET_FILE.1 となり、
# これまで TARGET_FILE.1 だったものは TARGET_FILE.2 にリネームされる。
# i=n で指定した回数を超えたものは削除される。


DIR=csv

for TARGET_FILE in $DIR/*.csv; do

  # TARGET_FILE が見つからなければ終了。
  if [ -e $TARGET_FILE ]; then
    :
  else
    echo "** ERROR ** File does not exist: $TARGET_FILE"
    continue
  fi

  i=5
 
  while [ $i -gt 1 ]
  do
    if [ -e $TARGET_FILE.`expr $i - 1` ]; then
      mv $TARGET_FILE.`expr $i - 1` $TARGET_FILE.$i 2>&1 1>/dev/null
    fi
    i=`expr $i - 1`
  done
 
  if [ -e $TARGET_FILE ]; then
    mv $TARGET_FILE $TARGET_FILE.1 2>&1 1>/dev/null
  fi

done

# EOF
