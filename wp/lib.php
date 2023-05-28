<?php
require_once(dirname(__DIR__) . '/lib/OpenGraph.php');

function get_og_image($url) {
  $graph = OpenGraph::fetch($url);
  $value = $graph->image;
  $value_check = mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');

  if (mb_detect_encoding($value_check) == 'UTF-8') {
    $value = $value_check; // 文字化け解消
  }

  $detects = array(
    'ASCII','EUC-JP','SJIS', 'JIS', 'CP51932','UTF-16', 'ISO-8859-1'
  );

  // 上記以外でもUTF-8以外の文字コードが渡ってきてた場合、UTF-8に変換する
  if (mb_detect_encoding($value) != 'UTF-8') {
    $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, $detects, true));
  }
  return $value;
}

// アイキャッチ画像設定
function set_featured_image($post_id, $tmp_path, $ext) {
	$filename = pathinfo($tmp_path, PATHINFO_FILENAME) . '.' . $ext;
	$upload_dir = wp_upload_dir();
	if (wp_mkdir_p($upload_dir['path'])) {
		$file = $upload_dir['path'] . '/' . $filename;
	} else {
		$file = $upload_dir['basedir'] . '/' . $filename;
	}
	rename($tmp_path, $file);

	$filetype = wp_check_filetype( $filename, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( $filename ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	set_post_thumbnail( $post_id, $attach_id );
}
