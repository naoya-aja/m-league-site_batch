<?php
/**
 * 試合結果一覧CSV出力
 * TablePress 連携
 */
require_once('phpQuery-onefile.php');
require_once('config.php');

// 試合結果一覧日付リンク生成
function dateurl($date) {
	$ts = strtotime($date);
	$week = date("w", $ts);
	$week = ['日', '月', '火', '水', '木', '金', '土'][$week];
	$strdate = date("Y/m/d({$week})", $ts);
	return sprintf('<a href="https://m-league.aja0.com/game-%s/">%s</a>', date("Y-m-d", $ts), $strdate);
}

function game_yield() {
	global $base_url, $regular_term, $teams, $members;

	$html = file_get_contents($base_url);
	$doc = phpQuery::newDocument($html);
	
	$round = 0;
	$gamesList = $doc->find(".p-gamesResult");
	foreach ($gamesList as $key => $geme) {
		if ($key < 1) continue;	// 調整
		$geme = pq($geme);
		$date = cdate($geme->find('.p-gamesResult__date')->text());
	
		// 期間チェック レギュラーシーズン
		list($st, $ed) = $regular_term;
		if (strtotime($date) < strtotime($st)) continue;
		if (strtotime($date) > strtotime($ed)) continue;
		// echo $date."\n";

		$strdateurl = dateurl($date);
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
				yield array($round, $strdateurl, $round2, $rank, $name, $tname, number_format($point/100, 1));
			}
		}
	}
}

$csv_heder = array('', '日付', '回戦', '順位', '選手', 'チーム', 'Pt');
$results_regular = array($csv_heder);
foreach (game_yield() as $arr) {
	$results_regular[] = $arr;
}
// var_dump($results_regular);

$file = sprintf('%s/%dregular.csv', __DIR__, $season_year);
$f = fopen($file, "w");
if ( $f ) {
	foreach($results_regular as $line){
		fputcsv($f, $line);
	} 
}
fclose($f);
