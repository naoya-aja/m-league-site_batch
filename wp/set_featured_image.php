<?php
/**
 * News 麻雀ウォッチ、キンマweb、M.LEAGUE 公式サイト のアイキャッチ画像の設定を行う。
 * og:imageの画像をダウンロードし設定する。
 */
require_once(dirname(__DIR__) . '/config.php');
require_once(__DIR__ . '/lib.php');
require_once('/home/xxxx/www/m-league/wp-load.php');

// 定数
$file_finish_date = __DIR__ . '/' . basename(__FILE__, '.php') . '_finish_date.txt';
$file_errors_log = dirname(__DIR__) . "/log/" . basename(__FILE__, '.php') . '_errors.log';

/**
 * 各サイトのロゴ画像のID
 * 	abema times		1418
 *  麻雀ウォッチ	1540
 *  キンマweb			2993
 *  M.LEAGUE 公式	444
 * author_id
 * 	ABEMA TIMES:	3
 * 	Mリーグ公式:	2
 * 	麻雀ウォッチ:	4
 * 	キンマweb:		5
 */
function get_default_thumbnail_id($author_id) {
	$thumbnail_id = 0;
	switch ($author_id) {
		case 2: // M.LEAGUE 公式サイト
			$thumbnail_id = 444;
			break;
		case 3: // abema times
			$thumbnail_id = 1418;
			break;
		case 4:	// 麻雀ウォッチ
			$thumbnail_id = 1540;
			break;
		case 5: // キンマweb
			$thumbnail_id = 2993;
			break;
	}
	return $thumbnail_id;
}

function get_after_date($file_finish_date) {
	$after_date = file_get_contents($file_finish_date);
	if ( empty($after_date) ) {
		// $after_date = date("Y-m-d", strtotime('2010-01-01'));
		var_dump('ファイルがありません => '. $file_finish_date);
		exit;
	} else {
		$after_date = unserialize($after_date);
	}
	return $after_date;
}

$after_date = get_after_date($file_finish_date);
$args = array(
	'date_query' => array(
		array(
			'after'     => $after_date,
			'inclusive' => false,	// 指定された日付ぴったりを含めるかどうか
		),
	),
	'orderby' => 'date',
	'order' => 'ASC',  //昇順 or 降順の指定
	'post_status' => 'publish',
	'post_type' => 'news',
	'author' => [2, 5],	// m-league-official:2, kinmaweb:5, mj-new:4
	'posts_per_page' => 100,
	// 'nopaging' => true
);
$the_query   = new WP_Query($args);

if ( $the_query->have_posts() ) {
	$finish_date = '';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();
		$thumbnail_id = get_post_thumbnail_id();
		$date = get_the_date();
		$author_id = get_the_author_meta('ID');
		$default_thumbnail_id = get_default_thumbnail_id($author_id);

		// 既にアイキャッチ画像が設定されている場合はスキップ
		if ( !empty($thumbnail_id) && $default_thumbnail_id != $thumbnail_id ) continue;

		// og:imageを取得
		$url = get_post_meta( $post_id, 'syndication_permalink', true );
		$image_url = get_og_image($url);

		// M.LEAGUE ロゴ画像
		$mlogo_urls = [
			'https://m-league.jp/wp/wp-content/uploads/2019/05/main.png',
			'https://kinmaweb.jp/wp-content/uploads/2019/11/1101_main.jpg',
		];
		if ( in_array($image_url, $mlogo_urls) ) {
			set_post_thumbnail($post_id, 444); // M.LEAGUE
			continue;
		}

		var_dump($date);
		// var_dump($image_url);
		// var_dump($author_id);
		// var_dump(get_the_title());

		if ( empty($image_url) ) continue;
		$image_ext = pathinfo(
			basename(parse_url($image_url, PHP_URL_PATH)),
			PATHINFO_EXTENSION
		);
		$tmp_path = download_url( $image_url );
		if ( is_wp_error( $tmp_path ) ) {
			// download failed, handle error
			$msg = sprintf('[%s]: download failed, handle error: post_id => %d%s', date('c'), $post_id, PHP_EOL);
			error_log($msg, 3, $file_errors_log);
			if (!empty($thumbnail_id)) continue;
			set_post_thumbnail($post_id, $default_thumbnail_id);	// デフォルトロゴ画像
		} else {
			set_featured_image($post_id, $tmp_path, $image_ext);
		}
		$finish_date = date("Y-m-d", strtotime('-1 day', strtotime($date)));	// 途中切れの場合があるため、1日前にする。
		ob_flush();
		flush();
	}
	if (!empty($finish_date)) file_put_contents($file_finish_date, serialize($finish_date));
}
