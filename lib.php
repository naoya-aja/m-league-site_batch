<?php
// 日付編集
function cdate($get_date) {
	global $season_year;
	$year = $season_year;
	$get_date = trim($get_date);
	$ret_date = $get_date;
	$len = mb_strpos($get_date, '(');
	$wdate = mb_substr($get_date, 0, $len);
	[$month, $day] = explode('/', $wdate);
	$month = intval($month);
	$day = intval($day);
	if ($month < 9) $year++;
	if (checkdate($month, $day, $year)) {
		$ts = mktime(0, 0, 0, $month, $day, $year);
		$ret_date = date('Y-m-d', $ts);
	}
	return $ret_date;
}

// ポイント取得
// 2022/01/03 魚谷侑未 ペナルティ 65.5(▲20)pt の対応
function cpoint($point) {
	$str_minus = '▲';
	$point = trim($point);
	$point = mb_ereg_replace('pt', '', $point);
	$point = str_replace(')', '', $point);
	$points = explode('(', $point, 2);
	$sum = 0;
	foreach ($points as $point) {
		if (($start = mb_strpos($point, $str_minus)) !== false) {
			$point = mb_ereg_replace($str_minus, '', $point);
			$point = '-' . $point;
		}
		if (is_numeric($point)) {
			// $point = intval(bcmul($point, '100'));	// さくらレンタルサーバーでは使えない`bcmul`
			$point = intval((string)($point * 100));
		} else {
			$point = 0;
		}
		$sum += $point;
	}
	return $sum;
}
