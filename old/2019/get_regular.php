<?php
require_once('phpQuery-onefile.php');

$penalties = array(
	144 => array(
		'沢崎誠' => 20
	)
);

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

function setpoint($results) {
  global $datas, $members, $data_members, $key_members;
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
  foreach ($results as $result) {
    $i = $members[$result['name']];
    $datas[$i][$j] = $result['point'];
		$i = array_search($result['name'], $key_members);
		$data_members[$i][$j] = $result['point'];
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

// penalties
foreach ($penalties as $round => $arr) {
	$i = $round - 1;
	foreach ($arr as $name => $point) {
		$tid = $members[$name];
		$datas[$tid][$i] -= ($point * 100);
		$j = array_search($name, $key_members);
		$data_members[$j][$i] -= ($point * 100);
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

// var_dump($datas);
// var_dump($data_members);

$json = json_encode($datas);
$file = __DIR__ . '/team_scores.json';
file_put_contents($file, $json);

$json = json_encode($data_members);
$file = __DIR__ . '/personal_scores.json';
file_put_contents($file, $json);

$json = json_encode($key_members);
$file = __DIR__ . '/members.json';
file_put_contents($file, $json);
