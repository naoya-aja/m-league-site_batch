<?php
/**
 * 麻雀ウォッチ試合結果記事を関連記事に埋め込む処理
 *
 */
require_once('../config.php');
require_once('/home/xxxx/www/m-league/wp-load.php');

// さらに使いたい機能に応じて必要なファイルをinclude
// require_once(ABSPATH . 'wp-admin/includes/media.php');
// require_once(ABSPATH . 'wp-admin/includes/image.php');
// require_once(ABSPATH . 'wp-admin/includes/file.php');

// $season_year = 2021;	// configを参照するようにする
$season_start = $season_year . '/10/01';
$season_next_yy = ($season_year + 1) % 100;
$pattern = "/【(\d{1,2}\/\d{1,2}) +Mリーグ${season_year}(-${season_next_yy})? +(第)?(\d)(試合|戦目)結果】/";
// $pattern = "/【(\d{1,2}\/\d{1,2}) +Mリーグ(\d{4})(-\d{2})? +(第)?(\d)(試合|戦目)結果】/";
$pattern_date_index = 1;
$pattern_round_index = 4;

$file_finish_date = basename(__FILE__, '.php') . '_finish_date.txt';
$file_errors_log = "../log/" . basename(__FILE__, '.php') . '_errors.log';

$html_format = <<<EOD
<!-- wp:embed {"url":"%s","type":"wp-embed","providerNameSlug":"麻雀ウォッチ"} -->
<figure class="wp-block-embed is-type-wp-embed is-provider-麻雀ウォッチ wp-block-embed-麻雀ウォッチ"><div class="wp-block-embed__wrapper">
%s
</div></figure>
<!-- /wp:embed -->
EOD;

/**
 * $the_slug: 例） game-2022-03-11
 * $urls: 第1試合と第2試合のURL
 */
function update_game_post($the_slug, $urls) {
	global $html_format;
	if (count($urls) != 2) return false;

	$insert_html = '';
	foreach ($urls as $index => $url) {
		if ($index > 0) $insert_html .= "\n\n";
		$insert_html .= sprintf($html_format, $url, $url);
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

	$search = "関連記事</h2>\n<!-- /wp:heading -->";
	$replace = $search . "\n\n" . $insert_html;
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
	// $after_date = date("Y-m-d", strtotime('-1 day', strtotime($season_start)));
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
	'author_name' => 'mj-new',
	// 'posts_per_page' => 20,
	'nopaging' => true
);
$the_query   = new WP_Query($args);

// $post_count  = $the_query->post_count;   //実際にそのページで取得した件数
// $found_posts = $the_query->found_posts;  //条件に当てはまる全件数
// var_dump($post_count);
// var_dump($found_posts);

if ( $the_query->have_posts() ) {
	$save_news = [];
	$finish_date = '';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();
		$title = get_the_title();

		preg_match($pattern, $title, $date_match);
		if (empty($date_match))	continue;

		// var_dump($title);
		$round = intval($date_match[$pattern_round_index]);
		if (!in_array($round, [1, 2])) {
			$msg = sprintf('[%s]: %s,%d: Round No Error: %s%s', date("Y-m-d H:i:s"),
				$the_slug, $round, $title, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			continue;
		}
		$date = sprintf('%d/%s', $season_year, $date_match[$pattern_date_index]);
		$ts = strtotime($date);
		if ($ts < strtotime($season_start)) $ts = strtotime('+1 year', $ts);
		if (!empty($finish_date) && $ts <= strtotime($finish_date)) {
			error_log(sprintf(
				'[%s]: %s,%d: 日付逆順エラー: %s', date("Y-m-d H:i:s"), $the_slug, $round, PHP_EOL),
				3, $file_errors_log
			);
			continue;
		}
		$date = date("Y-m-d", $ts);
		$the_slug ='game-' . $date;
		$url = get_permalink();
		if (empty($save_news)) {
			$save_news = compact('the_slug', 'round', 'url');
			continue;
		}
		if ($save_news['the_slug'] != $the_slug) {
			$msg = sprintf('[%s]: %s,%d - %s,%d: 日付エラー%s', date("Y-m-d H:i:s"),
				$save_news['the_slug'], $save_news['round'], $the_slug, $round, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			$save_news = compact('the_slug', 'round', 'url');
			continue;
		}

		if ($round == 1 && $save_news['round'] == 2) {
			$urls = [$url, $save_news['url']];
		} elseif ($round == 2 && $save_news['round'] == 1) {
			$urls = [$save_news['url'], $url];
		} else {
			$msg = sprintf('[%s]: %s (%d,%d): Roundエラー%s', date("Y-m-d H:i:s"),
				$the_slug, $save_news['round'], $round, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			$save_news = [];
			continue;
		}
		var_dump($the_slug);
		if (!update_game_post($the_slug, $urls)) {
			$msg = sprintf('[%s]: %s: update_game_post ERROR%s', date("Y-m-d H:i:s"), $the_slug, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			break;	// 更新できない場合は終了
		}
		$save_news = [];
		$finish_date = $date;
		ob_flush();
		// break;
	}
	if (!empty($finish_date)) file_put_contents($file_finish_date, serialize($finish_date));
	// wp_reset_postdata();
}