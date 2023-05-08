<?php
/**
 * Mリーグ
 */
$base_url = 'https://m-league.jp/games';

/**
 * 2022-23 シーズン
 */
$season_year = 2022;

// $term_nm = 'regular';
// $term_nm = 'semifinal';
$term_nm = 'final';
$regular_term = ['2022-10-03', '2023-03-21'];
$semifinal_term = ['2023-04-10', '2023-05-04'];
$final_term = ['2023-05-08', '2023-05-19'];
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
		'堀慎吾',
		'渋川難波',
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
		'瑞原明奈',
		'鈴木優',
		'仲林圭',
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
		'雷電',
		'Pirates',
	);
	// 持越分 * 100
	$initial_datas = array(
		array(29310),	// 風林火山
		array(-2070),	// サクラナイツ
		array(29610),	// 麻雀格闘倶楽部
		array(2780),	// ABEMAS
		array(-2120),	// 雷電
		array(-3060),	// Pirates
	);
}
if ($term_nm == 'final') {
	// 進出チーム
	$teams = array(
		'風林火山',
		'麻雀格闘倶楽部',
		'ABEMAS',
		'雷電',
	);
	// 持越分 * 100
	$initial_datas = array(
		array(14520),	// 風林火山
		array(8640),	// 麻雀格闘倶楽部
		array(13330),	// ABEMAS
		array(8690),	// 雷電
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
