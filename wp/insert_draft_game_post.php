<?php
require_once(dirname(__DIR__) . '/config.php');
require_once('/home/xxxx/www/m-league/wp-load.php');

$temp_file = __DIR__ . '/insert_draft_game_post.tpl';
$csv_file = dirname(__DIR__) . '/csv_master/abematv.csv';

// $season_year = 2021;	// configを参照するようにする
// $series_titles = [
// 	'レギュラーシーズン',
// 	'セミファイナルシリーズ',
// 	'ファイナルシリーズ',
// ];
// $content_title = 'Mリーグ%d %s %d日目/%d日';
// $url1 = 'https://m-league.aja0.com/';
// $url2 = 'https://m-league.aja0.com/category/game/';
// $url3 = 'https://m-league.aja0.com/news/';

/**
 * twitter URL生成関数
 *
 * @param timestamp $ts	日付のタイムスタンプ
 * @param string $account twitterアカウント
 * @return string URL
 */
function get_twitter_url($ts, $account) {
	$format = 'https://twitter.com/search?f=live&q=(%s)%s&src=typed_query';
	// $format = 'https://twitter.com/search?q=(%s)%s&amp;f=live';
	$since = date('Y-m-d', $ts);
	$until = date('Y-m-d', strtotime('+1 day', $ts));
	$from = 'from:' . $account;
	$from = rawurlencode($from);
	$term = sprintf(' until:%s since:%s', $until, $since);
	$term = rawurlencode($term);
	return sprintf($format, $from, $term);
}

function check_post($the_slug) {
	// スラッグから投稿を取得
	$args=array(
		'name'				=> $the_slug,
		'post_type'			=> 'post',
		'post_status'		=> ['auto-draft', 'draft', 'publish'],
		'posts_per_page'	=> 1
	);
	$posts = get_posts($args);
	if (empty($posts)) return false;
	return true;
}

function insert_draft_post($date, $title, $content) {
	$ts = strtotime($date);
	if ($ts === false) return;
	$date = date('Y-m-d 23:59:00', $ts);
	$slug = 'game-' . date('Y-m-d', $ts);
	if (check_post($slug)) {
		var_dump('投稿が存在します -> ' . $slug);
		return;
	}

	// 投稿オブジェクトを作成
	$post = array(
		'post_date'		=> $date,
		'post_name'		=> $slug,
		'post_title'	=> $title,
		'post_content'	=> $content,
		'post_status'	=> 'draft',
		'post_author'	=> 1,	// naoya
		'post_category'	=> array(6)	// gameカテゴリー
	);
	// var_dump($date, $slug, $title, $content);

	// 投稿をデータベースへ追加
	wp_insert_post( $post );
	var_dump($slug);
}

function create_draft_post($series_no, $day_no, $date, $url1, $url2, $sum_day_no) {
	global $temp_file, $season_year;
	$series_no = intval($series_no);
	$day_no = intval($day_no);
	$sum_day_no = intval($sum_day_no);
	$series_titles = [
		'レギュラーシーズン',
		'セミファイナルシリーズ',
		'ファイナルシリーズ',
	];
	$season_to_y = ($season_year + 1) % 100;
	$content_title = 'Mリーグ%d-%02d %s %d日目/%d日';
	$title = '試合結果 %s'; // 例) 試合結果 2022/03/22

	$ts = strtotime($date);
	if ($ts === false) return;
	$title = sprintf($title, date('Y/m/d', $ts));
	$i = $series_no - 1;
	if (empty($series_titles[$i])) return;
	$t = $series_titles[$i];
	$content_title = sprintf($content_title, $season_year, $season_to_y, $t, $day_no, $sum_day_no);
	$twitter_url1 = get_twitter_url($ts, 'm_league_jikkyo');
	$twitter_url2 = get_twitter_url($ts, 'm_league_');
	$content = file_get_contents($temp_file);
	$content = sprintf($content, $content_title, $url1, $url2, $twitter_url1, $twitter_url2);

	insert_draft_post($date, $title, $content);
}

try {
	$timezone = new DateTimeZone('Asia/Tokyo');
	$now = new DateTime('now', $timezone);
	// $now = new DateTime('2022/10/06', $timezone);
	$now->setTime(0, 0);
} catch (Exception $e) {
	var_dump($e->getMessage());
	exit(1);
}

$tg_row = [];
$sum_day_no = 0;
try {
	$file = new SplFileObject($csv_file);
	$file->setFlags(SplFileObject::READ_CSV);
} catch (Exception $e) {
	var_dump($e->getMessage());
	exit(1);
}
foreach ($file as $index => $row) {
	if ($index <= 0) continue;	// 先頭行はヘッダー
	list($series_no, $day_no, $date, $url1, $url2) = $row;
	if (!empty($tg_row) && $tg_row[0] != $series_no) break;
	try {
		$date = new DateTime($date, $timezone);
	} catch (Exception $e) {
		continue;
	}
	if ($now == $date) {
		$tg_row = $row;
	}
	$sum_day_no = $day_no;
	// var_dump($date->format('Y/m/d H:i:sP'));
}

if (!empty($tg_row)) {
	$tg_row[] = $sum_day_no;
	create_draft_post(...$tg_row);
}
