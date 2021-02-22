<?php
require_once('phpQuery-onefile.php');

// $penalties = array(
// 	144 => array(
// 		'沢崎誠' => 20
// 	)
// );

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
$key_members = array_keys($members);

$datas = array_fill(0, count($teams), array());
$data_members = array_fill(0, count($members), array());
$array_members = array_fill(0, count($members), array(
	'point' => 0,
	'count' => 0,
	'highest_score' => 0,
	'count_ranks' => array(0, 0, 0, 0)
));

function setpoint($results) {
	global $datas, $members, $data_members, $key_members, $array_members;
		$j = 0;
	$max = count($datas);
	for ($i = 0; $i < $max; $i++) {
		$j = array_push($datas[$i], 0);
	}
	$max = count($data_members);
	for ($i = 0; $i < $max; $i++) {
		$j = array_push($data_members[$i], 0);
	}
	$j--;
	foreach ($results as $rank => $result) {
		$i = $members[$result['name']];
		$datas[$i][$j] = $result['point'];
		$i = array_search($result['name'], $key_members);
		$data_members[$i][$j] = $result['point'];

		// トータルポイント
		$array_members[$i]['point'] += $result['point'];

		// 半荘数
		$array_members[$i]['count']++;

		// 最高スコア
		$rank_scores = array(50000, 10000, -10000, -30000);
		$score = $result['point'] * 10 + 30000 - $rank_scores[$rank];
		if ($array_members[$i]['highest_score'] < $score) $array_members[$i]['highest_score'] = $score;

		// 各順位カウント
		$array_members[$i]['count_ranks'][$rank]++;
	}
}

// 日付編集
function cdate($get_date) {
	$year = 2019;
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

$round = 0;
$gamesList = $doc->find(".p-gamesResult");
foreach ($gamesList as $key => $geme) {
	if ($key < 1) continue;
	$geme = pq($geme);
	// $date = cdate($geme->find('.p-gamesResult__date')->text());
	// echo $date."\n";

	$lists = $geme->find('.p-gamesResult__rank-list');
	foreach ($lists as $list) {
		$round++;
		$list = pq($list);
		$items = $list->find('.p-gamesResult__rank-item');
		$results = array();
		foreach ($items as $item) {
			$item = pq($item);
			$name = trim($item->find('.p-gamesResult__name')->text());
			$point = cpoint($item->find('.p-gamesResult__point')->text());
			$results[] = array('name' => $name, 'point' => $point);
		}
		setpoint($results);
	}
}

// penalties ペナルティ
// foreach ($penalties as $round => $arr) {
// 	$i = $round - 1;
// 	foreach ($arr as $name => $point) {
// 		$tid = $members[$name];
// 		$datas[$tid][$i] -= ($point * 100);
// 		$j = array_search($name, $key_members);
// 		$data_members[$j][$i] -= ($point * 100);
// 	}
// }

$imax = count($datas);
for ($i = 0; $i < $imax; $i++) {
	$jmax = count($datas[$i]);
	for ($j = 0; $j < $jmax; $j++) {
		if ($j <= 0) continue;
		$datas[$i][$j] = $datas[$i][$j - 1] + $datas[$i][$j];
	}
}
for ($i = 0; $i < $imax; $i++) {
	$jmax = count($datas[$i]);
	for ($j = 0; $j < $jmax; $j++) {
		$datas[$i][$j] /= 100;
	}
}

$imax = count($data_members);
for ($i = 0; $i < $imax; $i++) {
	$jmax = count($data_members[$i]);
	for ($j = 0; $j < $jmax; $j++) {
		if ($j <= 0) continue;
		$data_members[$i][$j] = $data_members[$i][$j - 1] + $data_members[$i][$j];
	}
}
for ($i = 0; $i < $imax; $i++) {
	$jmax = count($data_members[$i]);
	for ($j = 0; $j < $jmax; $j++) {
		$data_members[$i][$j] /= 100;
	}
}

$array_member_sort = array();
foreach ($array_members as $i => $member) {
	$array_member_sort[$i] = $member['point'];
}
arsort($array_member_sort);
$ranking_members = array();
$j = 0;
foreach ($array_member_sort as $i => $point) {
	$j++;
	$name = $key_members[$i];
	$count = $array_members[$i]['count'];
	$arr = array();
	$arr[] = $j;
	$arr[] = $name;
	$arr[] = $teams[$members[$name]];
	$arr[] = $count;
	$arr[] = number_format($point / 100, 1);
	$arr[] = number_format($array_members[$i]['highest_score']);
	$arr[] = sprintf('%.4f', round(1- $array_members[$i]['count_ranks'][3] / $count, 4));
	$arr[] = $array_members[$i]['count_ranks'][0];
	$arr[] = $array_members[$i]['count_ranks'][1];
	$arr[] = $array_members[$i]['count_ranks'][2];
	$arr[] = $array_members[$i]['count_ranks'][3];
	$ranking_members[] = $arr;
}

// チームランキング
$array_sort = array();
$data_ranking_teams = array();
foreach ($datas as $i => $data) {
	$j = array_key_last($data);
	$array_sort[$i] = $data[$j];
}
arsort($array_sort);
$ranking_teams = array();
$j = 0;
foreach ($array_sort as $i => $point) {
	$j++;
	$team_name = $teams[$i];
	$arr = array();
	$arr[] = $j;
	$arr[] = $team_name;
	$arr[] = number_format($point, 1);
	foreach ($array_member_sort as $mi => $point) {
		$member_name = $key_members[$mi];
		if ($i != $members[$member_name]) continue;
		$arr[] = $member_name;
		$arr[] = number_format($point / 100, 1);
	}
	$ranking_teams[] = array_pad($arr, 11, '');
}


// var_dump($datas);
// var_dump($data_members);
// var_dump($array_members);
// var_dump($key_members);
// var_dump($ranking_members);

$json = json_encode($datas);
$file = __DIR__ . '/team_scores.json';
file_put_contents($file, $json);

$json = json_encode($data_members);
$file = __DIR__ . '/personal_scores.json';
file_put_contents($file, $json);

$json = json_encode($key_members);
$file = __DIR__ . '/members.json';
file_put_contents($file, $json);

$json = json_encode($ranking_members);
$file = __DIR__ . '/ranking.json';
file_put_contents($file, $json);

$json = json_encode($ranking_teams);
$file = __DIR__ . '/ranking_teams.json';
file_put_contents($file, $json);

// TablePress 連携
// CSV出力
function csvoutput($file, $arr) {
	$f = fopen($file, "w");
	if ($f) {
		foreach($arr as $line){
			fputcsv($f, $line);
		} 
	}
	fclose($f);
}

// チーム成績
$csv_heder = array('順位', 'チーム', 'Pt', '選手成績', '', '', '', '', '', '', 'a');
array_unshift($ranking_teams, $csv_heder);
$file = __DIR__ . '/2020-regular-tm.csv';
csvoutput($file, $ranking_teams);

// 個人成績
$csv_heder = array('順位' ,'選手名' ,'チーム' ,'半荘数' ,'Pt' ,'最高スコア' ,'4着回避率' ,'1着' ,'2着' ,'3着' ,'4着');
array_unshift($ranking_members, $csv_heder);
$file = __DIR__ . '/2020-regular-indiv.csv';
csvoutput($file, $ranking_members);
