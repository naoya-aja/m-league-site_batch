<?php
/**
 * キンマWEB 試合結果記事を関連記事に埋め込む処理
 */
require_once(dirname(__DIR__) . '/config.php');
require_once('/home/xxxx/www/m-league/wp-load.php');

// さらに使いたい機能に応じて必要なファイルをinclude
// require_once(ABSPATH . 'wp-admin/includes/media.php');
// require_once(ABSPATH . 'wp-admin/includes/image.php');
// require_once(ABSPATH . 'wp-admin/includes/file.php');

// $season_year = 2021;	// configを参照するようにする
$season_start = $season_year . '/10/04';

// プロ麻雀リーグ「朝日新聞Mリーグ2020」のセミファイナル5日目、第2戦は
// プロ麻雀リーグ「朝日新聞Mリーグ2021」の開幕戦となる本日、第1戦は松本吉弘（渋谷ABEMAS）が
// プロ麻雀リーグ「朝日新聞Mリーグ2021」の開幕戦となる本日、第2戦は鈴木たろう（赤坂ドリブンズ）が
// プロ麻雀リーグ「朝日新聞Mリーグ2021」の開幕戦となる本日
// プロ麻雀リーグ「大和証券Mリーグ2021」の開幕戦となる本日
// プロ麻雀リーグ「大和証券Mリーグ2021」の開幕90日目
// プロ麻雀リーグ「大和証券Mリーグ2021」の開幕90日目、第2戦
// $pattern = "/【(\d{1,2}\/\d{1,2}) +Mリーグ${season_year}(-${season_next_yy})? +(第)?(\d)(試合|戦目)結果】/";
// $pattern = "/【(\d{1,2}\/\d{1,2}) +Mリーグ(\d{4})(-\d{2})? +(第)?(\d)(試合|戦目)結果】/";
// プロ麻雀リーグ「朝日新聞Mリーグ2021」の第2日目、第2戦
// プロ麻雀リーグ「大和証券Mリーグ2021」の開幕第4日目
// プロ麻雀リーグ「大和証券Mリーグ2021」の開幕5日目、第1戦
// プロ麻雀リーグ「朝日新聞Mリーグ2021」の開幕第4日目、第1戦は松ヶ瀬隆弥
// プロ麻雀リーグ「朝日新聞Mリーグ2021-22 セミファイナルシリーズ」の4日目、第2戦は
// プロ麻雀リーグ「朝日新聞Mリーグ2021セミファイナルシリーズ」の5日目、第1戦は
// プロ麻雀リーグ「朝日新聞Mリーグ2021-22 セミファイナルシリーズ」の2日目、第1戦
// プロ麻雀リーグ「朝日新聞Mリーグ2021-2022 セミファイナルシリーズ」の1日目、第2戦
// $pattern = "/プロ麻雀リーグ「(大和証券|朝日新聞) *Mリーグ2021」の(開幕)?(第)?(\d{1,2})日目、第(\d)戦/";
$pattern = "/プロ麻雀リーグ「(大和証券|朝日新聞) *Mリーグ(\d{4})(-\d{2,4})? *(セミファイナルシリーズ)?」の(開幕)?(第)?(\d{1,2})日目、第(\d)戦/";
$pattern_day_index = 7;
$pattern_round_index = 8;
$term_no = 2;	// regular: 1, semifinal: 2, final: 3

$file_finish_date = __DIR__ . '/' . basename(__FILE__, '.php') . '_finish_date.txt';
$file_errors_log = dirname(__DIR__) . "/log/" . basename(__FILE__, '.php') . '_errors.log';
$file_schedule = dirname(__DIR__) . '/csv_master/schedule.csv';

$date_list = array();
$file = new SplFileObject($file_schedule);
$file->setFlags(SplFileObject::READ_CSV);
$before_ts = 0;
$i = 0;
foreach ($file as $row) {
	list($no, $date) = $row;
	if ($no != $term_no) continue;
	$ts = strtotime($date);
	if ($ts === false || $before_ts >= $ts) {
		error_log(sprintf(
			'[%s]:日付の正当性、順序不正 schedule.csv%s', date('c'), PHP_EOL),
			3, $file_errors_log
		);
		exit;	// schedule.csv 不正
	}
	$date_list[] = $date;
}

/**
 * $the_slug: 例） game-2022-03-11
 * $urls: 第1試合と第2試合のURLとタイトル
 */
function update_game_post($the_slug, $urls) {
	global $html_format;
	if (count($urls) != 2) return false;

	$format = '<li>キンマweb 第%d試合「<a href="%s">%s</a>」</li>';
	$insert_html = '';
	foreach ($urls as $index => $arr) {
		list($url, $title) = $arr;
		$title = mb_strimwidth($title, 0, 66, '…', 'utf8');
		$insert_html .= sprintf($format, $index + 1, $url, $title);
	}

	// スラッグから投稿を取得
	$args=array(
		'name'				=> $the_slug,
		'post_type'			=> 'post',
		'post_status'		=> 'publish',
		'posts_per_page'	=> 1
	);
	$my_posts = get_posts($args);
	if (!$my_posts) return false;

	$post_id = $my_posts[0]->ID;
	$content = $my_posts[0]->post_content;

	// $search = "関連記事</h2>\n<!-- /wp:heading -->";
	$search = "<!-- wp:list -->\n<ul><li>ABEMA TV";
	$replace = sprintf("<!-- wp:list -->\n<ul>%s<li>ABEMA TV", $insert_html);
	$new_content = str_replace($search, $replace, $content);

	$post = array(
		'ID'			=> $post_id,
		'post_content'	=> $new_content,
	);
	// var_dump($new_content);
	wp_update_post($post);
	return true;
}

$after_date = file_get_contents($file_finish_date);
if (empty($after_date)) {
	var_dump('ファイルがありません => '. $file_finish_date);
	exit;
} else {
	$after_date = unserialize($after_date);
}
$args = array(
	'date_query' => array(
		array(
			'after'     => $after_date,
			'inclusive' => false,	// 指定された日付ぴったりを含めるかどうか
		),
	),
	'orderby' => 'date',
	// 'orderby' => 'ID',
	'order' => 'ASC',  //昇順 or 降順の指定
	'post_status' => 'publish',
	'post_type' => 'news',
	'author_name' => 'kinmaweb',
	// 'posts_per_page' => 50,
	'nopaging' => true
);
$the_query = new WP_Query($args);

if ( $the_query->have_posts() ) {
	$save_news = [];
	$finish_date = '';
	$finish_day = 0;
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();
		$content = get_the_content();
		$title = get_the_title();

		preg_match($pattern, $content, $date_match);
		if (empty($date_match))	continue;

		$match_title = $date_match[0];
		$day = intval($date_match[$pattern_day_index]);
		$round = intval($date_match[$pattern_round_index]);
		// var_dump($match_title);

		if ($finish_day >= $day) {
			$msg = sprintf('[%s]: 順序エラー: %d,%d %s%s', date('c'), $finish_day, $day, $match_title, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			continue;
		}
		if ($finish_day > 0 && ($finish_day + 1) != $day) {
			$msg = sprintf('[%s]: 順序飛び警告: %d,%d %s%s', date('c'), $finish_day, $day, $match_title, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
		}

		// 日目から日付をGETする スケジュールcsvより
		if (empty($date_list[$day - 1])) continue;
		$date = $date_list[$day - 1];
		$ts = strtotime($date);
		$date = date("Y-m-d", $ts);
		$the_slug ='game-' . $date;
		if (!in_array($round, [1, 2])) {
			$msg = sprintf('[%s]: Round No Error: %s,%d%s', date('c'), $the_slug, $round, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			continue;
		}

		$url = get_permalink();
		if (empty($save_news)) {
			$save_news = compact('the_slug', 'round', 'url', 'title');
			continue;
		}
		if ($save_news['the_slug'] != $the_slug) {
			$msg = sprintf('[%s]: 日付エラー: %s,%d - %s,%d%s', date('c'),
				$save_news['the_slug'], $save_news['round'], $the_slug, $round, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			$save_news = compact('the_slug', 'round', 'url', 'title');
			continue;
		}

		if ($round == 1 && $save_news['round'] == 2) {
			$urls = [[$url, $title], [$save_news['url'], $save_news['title']]];
		} elseif ($round == 2 && $save_news['round'] == 1) {
			$urls = [[$save_news['url'], $save_news['title']], [$url, $title]];
		} else {
			$msg = sprintf('[%s]: Roundエラー: %d日目 %s (%d,%d)%s', date('c'),
				$day, $the_slug, $save_news['round'], $round, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			$save_news = [];
			continue;
		}
		var_dump($day . '日目 : ' . $the_slug);
		if (!update_game_post($the_slug, $urls)) {
			$msg = sprintf('[%s]: %s: update_game_post ERROR%s', date('c'), $the_slug, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			break;	// 更新できない場合は終了
		}
		$save_news = [];
		$finish_date = $date;
		$finish_day = $day;
		ob_flush();
		// break;
	}
	if (!empty($finish_date)) file_put_contents($file_finish_date, serialize($finish_date));
}
