<?php
date_default_timezone_set('Asia/Tokyo');
$filepath_in = __DIR__ . '/csv_master/schedule.csv';
$filepath_out = __DIR__ . '/schedule.csv';

// 現在日付取得 timestamp
$today = getdate();
$ts_today = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);

$records = array();
$file = new SplFileObject($filepath_in); 
$file->setFlags(SplFileObject::READ_CSV); 
foreach ($file as $row) {
	list($date) = $row;
	$date = substr($date, 0, 10);
	$ts = strtotime($date);
	if ($ts === false) continue;
	if ($ts < $ts_today) continue;

	$records[] = $row;

	if (count($records) >= 4) break;
}

$csv_heder = array('日付', '対戦チーム', '', '', '');
array_unshift($records, $csv_heder);

$file = new SplFileObject($filepath_out, 'w');
foreach ($records as $line) {
	$file->fputcsv($line);
}
