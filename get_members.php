<?php
require_once('phpQuery-onefile.php');
require_once('config.php');

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
	$j--;
	$k = 0;
	$max = count($data_members);
	for ($i = 0; $i < $max; $i++) {
		$k = array_push($data_members[$i], 0);
	}
	$k--;
	foreach ($results as $rank => $result) {
		$i = $members[$result['name']];
		$datas[$i][$j] = $result['point'];
		$i = array_search($result['name'], $key_members);
		$data_members[$i][$k] = $result['point'];

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

$html = file_get_contents($base_url);
$doc = phpQuery::newDocument($html);

$round = 0;
$gamesList = $doc->find(".p-gamesResult");
foreach ($gamesList as $key => $geme) {
	if ($key < 1) continue;
	$geme = pq($geme);
	$date = cdate($geme->find('.p-gamesResult__date')->text());

	// 期間チェック 2021レギュラーシーズン
	list($st, $ed) = $regular_term;
	if (strtotime($date) < strtotime($st)) continue;
	if (strtotime($date) > strtotime($ed)) continue;

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
	$arr[] = $count == 0 ? '0.0000' : sprintf('%.4f', round(1- $array_members[$i]['count_ranks'][3] / $count, 4));
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
$file = __DIR__ . "/${term_nm}_team_scores.json";
file_put_contents($file, $json);

$json = json_encode($data_members);
$file = __DIR__ . "/${term_nm}_personal_scores.json";
file_put_contents($file, $json);

$json = json_encode($key_members);
$file = __DIR__ . "/${term_nm}_members.json";
file_put_contents($file, $json);

$json = json_encode($ranking_members);
$file = __DIR__ . "/${term_nm}_ranking.json";
file_put_contents($file, $json);

$json = json_encode($ranking_teams);
$file = __DIR__ . "/${term_nm}_ranking_teams.json";
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
$csv_heder = array('#', 'チーム', 'Pt', '選手成績', '', '', '', '', '', '', '');
array_unshift($ranking_teams, $csv_heder);
$file = sprintf('%s/%d-%s-tm.csv', __DIR__, $season_year, $term_nm);
csvoutput($file, $ranking_teams);

// 個人成績
$csv_heder = array('#' ,'選手名' ,'チーム' ,'半荘数' ,'Pt' ,'最高スコア' ,'4着回避率' ,'1着' ,'2着' ,'3着' ,'4着');
array_unshift($ranking_members, $csv_heder);
$file = sprintf('%s/%d-%s-indiv.csv', __DIR__, $season_year, $term_nm);
csvoutput($file, $ranking_members);
