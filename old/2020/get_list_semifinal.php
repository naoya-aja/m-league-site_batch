<?php
require_once('phpQuery-onefile.php');

$teams = array(
	'ドリブンズ',
	'風林火山',
	'サクラナイツ',
	'麻雀格闘倶楽部',
	'ABEMAS',
	'PHOENIX',
	'雷電',
	'Pirates',
);

$members = array(
	'園田賢' => 0,
	'村上淳'	=> 0,
	'鈴木たろう'	=> 0,
	'丸山奏子' => 0,
	'二階堂亜樹' => 1,
	'滝沢和典' => 1,
	'勝又健志' => 1,
	'内川幸太郎' => 2,
	'岡田紗佳' => 2,
	'沢崎誠'	=> 2,
	'堀慎吾' => 2,
	'佐々木寿人'	=> 3,
	'高宮まり' => 3,
	'前原雄大' => 3,
	'藤崎智' => 3,
	'多井隆晴' => 4,
	'白鳥翔' => 4,
	'松本吉弘' => 4,
	'日向藍子' => 4,
	'魚谷侑未' => 5,
	'近藤誠一' => 5,
	'茅森早香' => 5,
	'和久津晶' => 5,
	'萩原聖人' => 6,
	'瀬戸熊直樹' => 6,
	'黒沢咲' => 6,
	'小林剛' => 7,
	'朝倉康心' => 7,
	'石橋伸洋' => 7,
	'瑞原明奈' => 7,
);

// 日付編集
function cdate($get_date) {
	$year = 2020;
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

function cpoint($point) {
	$str_minus = '▲';
	$point = trim($point);
	$point = mb_ereg_replace('pt', '', $point);
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
	return $point;
}

$base_url = 'https://m-league.jp/games';
$html = file_get_contents($base_url);
$doc = phpQuery::newDocument($html);

$csv_heder = array('', '日付', '回戦', '順位', '選手', 'チーム', 'Pt');
$results_regular = array($csv_heder);
$round = 0;
$gamesList = $doc->find(".p-gamesResult");
foreach ($gamesList as $key => $geme) {
	if ($key < 1) continue;
	$geme = pq($geme);
	$date = cdate($geme->find('.p-gamesResult__date')->text());

	// セミファイナル 2021/04/12 – 2021/04/30
	if (strtotime($date) < strtotime('2021-04-12')) continue;
	if (strtotime($date) > strtotime('2021-05-01')) continue;

	// echo $date."\n";
	$ts = strtotime($date);
	$week = date("w", $ts);
	$week = ['日', '月', '火', '水', '木', '金', '土'][$week];
	$strdate = date("Y/m/d({$week})", $ts);
	$strdateurl = sprintf('<a href="https://m-league.aja0.com/game-%s/">%s</a>', date("Y-m-d", $ts), $strdate);
	$round2 = 0;

	$lists = $geme->find('.p-gamesResult__rank-list');
	foreach ($lists as $list) {
		$round2++;
		$round++;
		$list = pq($list);
		$items = $list->find('.p-gamesResult__rank-item');
		$rank = 0;
		foreach ($items as $item) {
			$rank++;
			$item = pq($item);
			$name = trim($item->find('.p-gamesResult__name')->text());
			$point = cpoint($item->find('.p-gamesResult__point')->text());

			$tname = $teams[$members[$name]];
			$results_regular[] = array($round, $strdateurl, $round2, $rank, $name, $tname, number_format($point/100, 1));
		}
	}
}

// var_dump($results_regular);

$file = __DIR__ . '/2020semifinal.csv';
$f = fopen($file, "w");
if ( $f ) {
	foreach($results_regular as $line){
		fputcsv($f, $line);
	} 
}
fclose($f);
