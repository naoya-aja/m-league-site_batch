<?php
/**
 * Mリーグ
 */
$base_url = 'https://m-league.jp/games';

/**
 * 2021 シーズン
 */
$season_year = 2021;

$regular_term = ['2021-10-04', '2022-03-11'];
$semifinal_term = ['2022-03-21', '2022-04-08'];
$final_term = ['2022-04-18', '2022-04-26'];

$term_nm = 'regular';

// チームメンバー
$team_members = [
	'ドリブンズ' => [
		'園田賢',
		'村上淳',
		'鈴木たろう',
		'丸山奏子',
	],
	'風林火山' => [
		'二階堂亜樹',
		'勝又健志',
		'松ヶ瀬隆弥',
		'二階堂瑠美',
	],
	'サクラナイツ' => [
		'内川幸太郎',
		'岡田紗佳',
		'沢崎誠',
		'堀慎吾',
	],
	'麻雀格闘倶楽部' => [
		'佐々木寿人',
		'高宮まり',
		'伊達朱里紗',
		'滝沢和典',
	],
	'ABEMAS' => [
		'多井隆晴',
		'白鳥翔',
		'松本吉弘',
		'日向藍子',
	],
	'PHOENIX' => [
		'魚谷侑未',
		'近藤誠一',
		'茅森早香',
		'東城りお',
	],
	'雷電' => [
		'萩原聖人',
		'瀬戸熊直樹',
		'黒沢咲',
		'本田朋広',
	],
	'Pirates' => [
		'小林剛',
		'朝倉康心',
		'石橋伸洋',
		'瑞原明奈',
	],
];

$teams = array_keys($team_members);

$members = [];
foreach ($team_members as $tnm => $mems) {
	foreach ($mems as $mem) {
		$members[$mem] = array_search($tnm, $teams);
	}
}

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
