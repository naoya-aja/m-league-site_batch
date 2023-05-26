<?php
/**
 * 試合結果一覧CSV出力
 * TablePress 連携
 */
require_once(__DIR__ . '/phpQuery-onefile.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/config.php');

// 試合結果一覧日付リンク生成
function dateurl($date) {
	$ts = strtotime($date);
	$week = date("w", $ts);
	$week = ['日', '月', '火', '水', '木', '金', '土'][$week];
	$strdate = date("Y/m/d({$week})", $ts);
	return sprintf('<a href="https://m-league.aja0.com/game-%s/">%s</a>', date("Y-m-d", $ts), $strdate);
}

function game_yield() {
	global $base_url, $this_term, $teams, $members;

	$html = file_get_contents($base_url);
	$doc = phpQuery::newDocument($html);

	$round = 0;
	$gamesList = $doc->find(".p-gamesResult");
	foreach ($gamesList as $key => $geme) {
		if ($key < 1) continue;	// 調整
		$geme = pq($geme);
		$date = cdate($geme->find('.p-gamesResult__date')->text());

		// 期間チェック
		list($st, $ed) = $this_term;
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
			$sv_point = 0;
			$sv_rank = 1;
			foreach ($items as $item) {
				$rank++;
				$item = pq($item);
				$name = trim($item->find('.p-gamesResult__name')->text());
				$point = cpoint($item->find('.p-gamesResult__point')->text());
				if ($sv_point != $point) {
					$sv_point = $point;
					$sv_rank = $rank;
				}
				$tname = $teams[$members[$name]];
				yield array($round, $strdateurl, $round2, $sv_rank, $name, $tname, number_format($point/100, 1));
			}
		}
	}
}

$csv_heder = array('', '日付', '回戦', '順位', '選手', 'チーム', 'Pt');
$results = array($csv_heder);
foreach (game_yield() as $arr) {
	$results[] = $arr;
}
// var_dump($results);

$file = sprintf('%s/%d%s.csv', __DIR__, $season_year, $term_nm);
$f = fopen($file, "w");
if ( $f ) {
	foreach($results as $line){
		fputcsv($f, $line);
	}
}
fclose($f);
