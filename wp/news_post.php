<?php
/**
 * ニュース記事を公開する
 * author_id
 * 	ABEMA TIMES:	3
 * 	Mリーグ公式:	2
 * 	麻雀ウォッチ:	4
 * 	キンマweb:		5
 */
require_once(__DIR__ . '/lib.php');
require_once('/home/xxxx/www/m-league/wp-load.php');
echo "start ****\n";

//さらに使いたい機能に応じて必要なファイルをinclude
// require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
// require_once(ABSPATH . 'wp-admin/includes/file.php');

$args_org = array(
	'orderby' => 'date',
	'order'   => 'ASC',
	'post_type' => 'news',
	'post_status' => 'pending',
	'author' => 3,	// ABEMA TIMES
	'posts_per_page' => -1,	// ページングなし
);

$args = $args_org;
$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();

		$category = get_post_meta( $post_id, 'category', true );
		if ( $category != '麻雀' ) continue;

		if ( !has_post_thumbnail() ) {
			$set_featured_image = false;
			// 画像
			$enclosure = get_post_meta( $post_id, 'enclosure', true );
			$image_url = explode("\n", $enclosure)[0];
			if ( !empty($image_url) ) {
				$image_ext = pathinfo(
					basename(parse_url($image_url, PHP_URL_PATH)),
					PATHINFO_EXTENSION
				);
				$tmp_path = download_url( $image_url );
				if ( !is_wp_error( $tmp_path ) ) {
					set_featured_image($post_id, $tmp_path, $image_ext);
					$set_featured_image = true;
				}
			}
			if ( !$set_featured_image ) {
				// ABEMA TIMES ロゴ画像を設定
				set_post_thumbnail($post_id, 1418);	// abema times ロゴ画像
			}
		}

		// 公開
		wp_update_post(array(
			'ID' => $post_id,
			'post_status' => 'publish',
		));

		printf("id:%d publish *** ABEMA TIMES ***\n", $post_id);
	}
	wp_reset_postdata();
}

// 古い記事を削除
$i = 0;
$args = $args_org;
$args['order'] = 'DESC';
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();
		$i++;
		if ($i < 200) continue;
		wp_delete_post($post_id, true);

		printf("id:%d delete ***\n", $post_id);
	}
	wp_reset_postdata();
}

// 各記事を公開にする
$user_logos = array_combine([2, 4, 5], [444, 1540, 2993]);
$args = $args_org;
unset($args['author']);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post_id = get_the_ID();

		$userid = get_the_author_meta('ID');
		if (!array_key_exists($userid, $user_logos))	continue;
		if (!empty($user_logos[$userid])) {
			$thumbnail_id = $user_logos[$userid];
			set_post_thumbnail($post_id, $thumbnail_id);	// ロゴ画像
		}
		wp_update_post(array(
			'ID' => $post_id,
			'post_status' => 'publish',
		));

		printf("postid:%d, userid:%d publish ***\n", $post_id, $userid);
	}
	wp_reset_postdata();
}

echo "end ****\n";
