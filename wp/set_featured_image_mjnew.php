<?php
/**
 * News 麻雀ウォッチ アイキャッチ画像の設定を行う。
 * 先頭の画像をダウンロードし設定する。
 */
require_once(dirname(__DIR__) . '/config.php');
require_once(__DIR__ . '/lib.php');
require_once('/home/xxxx/www/m-league/wp-load.php');
require_once(dirname(__DIR__) . '/lib/phpQuery-onefile.php');

// さらに使いたい機能に応じて必要なファイルをinclude
// require_once(ABSPATH . 'wp-admin/includes/media.php');
// require_once(ABSPATH . 'wp-admin/includes/image.php');
// require_once(ABSPATH . 'wp-admin/includes/file.php');

$file_finish_date = __DIR__ . '/' . basename(__FILE__, '.php') . '_finish_date.txt';
$file_errors_log = dirname(__DIR__) . "/log/" . basename(__FILE__, '.php') . '_errors.log';

$after_date = file_get_contents($file_finish_date);
if ( empty($after_date) ) {
	$after_date = date("Y-m-d", strtotime('2010-01-01'));
	// var_dump('ファイルがありません => '. $file_finish_date);
	// exit;
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
	'order' => 'ASC',  //昇順 or 降順の指定
	'post_status' => 'publish',
	'post_type' => 'news',
	'author_name' => 'mj-new',
	// 'posts_per_page' => 20,
	'nopaging' => true
);
$the_query   = new WP_Query($args);

if ( $the_query->have_posts() ) {
	$finish_date = '';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();
		$title = get_the_title();
		$content = get_the_content();
		$thumbnail_id = get_post_thumbnail_id();
		$date = get_the_date();

		if ( !empty($thumbnail_id) && $thumbnail_id != 1540 ) continue;

		var_dump($date);
		$doc = phpQuery::newDocument($content);
		$image_url = $doc->find("img:eq(0)")->attr("src");
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
			set_post_thumbnail($post_id, 1540);	// abema times ロゴ画像
		} else {
			set_featured_image($post_id, $tmp_path, $image_ext);
		}
		$finish_date = date("Y-m-d", strtotime('-1 day', strtotime($date)));	// 途中切れの場合があるため、1日前にする。
		ob_flush();
	}
	if (!empty($finish_date)) file_put_contents($file_finish_date, serialize($finish_date));
}
