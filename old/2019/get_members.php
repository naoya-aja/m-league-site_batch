<?php
require_once('phpQuery-onefile.php');

// $penalties = array(
// 	144 => array(
// 		'沢崎誠' => 20
// 	)
// );

$teams = array(
	'サクラナイツ',
	'ABEMAS',
	'PHOENIX',
	'Pirates',
);

$members = array(
	'内川幸太郎' => 0,
	'岡田紗佳' => 0,
	'沢崎誠'	=> 0,
	'多井隆晴' => 1,
	'白鳥翔' => 1,
	'松本吉弘' => 1,
	'日向藍子' => 1,
	'魚谷侑未' => 2,
	'近藤誠一' => 2,
	'茅森早香' => 2,
	'和久津晶' => 2,
	'小林剛' => 3,
	'朝倉康心' => 3,
	'石橋伸洋' => 3,
	'瑞原明奈' => 3,
);
$key_members = array_keys($members);

// $datas = array_fill(0, count($teams), array());
// 持越分 * 100
$datas = array(
	array(14240),
	array(3200),
	array(8600),
	array(-170),
);
$data_members = array_fill(0, count($members), array());

function setpoint($results) {
	global $datas, $members, $data_members, $key_members;
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
	foreach ($results as $result) {
		$i = $members[$result['name']];
		$datas[$i][$j] = $result['point'];
		$i = array_search($result['name'], $key_members);
		$data_members[$i][$k] = $result['point'];
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
    $date = cdate($geme->find('.p-gamesResult__date')->text());

		if (strtotime($date) < strtotime('2020-06-14')) continue;
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

// penalties
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

// var_dump($datas);
// var_dump($data_members);

$json = json_encode($datas);
$file = __DIR__ . '/final_team_scores.json';
file_put_contents($file, $json);

$json = json_encode($data_members);
$file = __DIR__ . '/final_personal_scores.json';
file_put_contents($file, $json);

$json = json_encode($key_members);
$file = __DIR__ . '/final_members.json';
file_put_contents($file, $json);
