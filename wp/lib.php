<?php
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
