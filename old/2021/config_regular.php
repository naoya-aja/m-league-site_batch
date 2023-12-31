<?php
/**
 * Mリーグ
 */
$base_url = 'https://m-league.jp/games';

/**
 * 2021 シーズン
 */
$season_year = 2021;

$term_nm = 'regular';
$this_term = ['2021-10-04', '2022-03-11'];
// $term_nm = 'semifinal';
// $this_term = ['2022-03-21', '2022-04-08'];
// $term_nm = 'final';
// $this_term = ['2022-04-18', '2022-04-26'];

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
