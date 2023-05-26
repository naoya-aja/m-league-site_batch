<?php
/**
 * Mリーグ
 */
$base_url = 'https://m-league.jp/games';

/**
 * 2021 シーズン
 */
$season_year = 2021;

// $term_nm = 'regular';
// $term_nm = 'semifinal';
$term_nm = 'final';
$regular_term = ['2021-10-04', '2022-03-11'];
$semifinal_term = ['2022-03-21', '2022-04-08'];
$final_term = ['2022-04-18', '2022-04-26'];
$this_term = ${"${term_nm}_term"};

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

// regular
$teams = array_keys($team_members);
$initial_datas = array_fill(0, count($teams), array());
if ($term_nm == 'semifinal') {
	// 進出チーム
	$teams = array(
		'風林火山',
		'サクラナイツ',
		'麻雀格闘倶楽部',
		'ABEMAS',
		'PHOENIX',
		'Pirates',
	);
	// 持越分 * 100
	$initial_datas = array(
		array(9220),	// 風林火山
		array(8850),	// サクラナイツ
		array(12450),	// 麻雀格闘倶楽部
		array(16410),	// ABEMAS
		array(9210),	// PHOENIX
		array(18860),	// Pirates
	);
}
if ($term_nm == 'final') {
	// 進出チーム
	$teams = array(
		'サクラナイツ',
		'麻雀格闘倶楽部',
		'ABEMAS',
		'PHOENIX',
	);
	// 持越分 * 100
	$initial_datas = array(
		array(12430),	// サクラナイツ
		array(9250),	// 麻雀格闘倶楽部
		array(6200),	// ABEMAS
		array(6010),	// PHOENIX
	);
}

$members = [];
foreach ($teams as $i => $tnm) {
	if (empty($team_members[$tnm])) continue;
	$mems = $team_members[$tnm];
	foreach ($mems as $mem) {
		$members[$mem] = $i;
	}
}
// var_dump($teams);
// var_dump($members);
