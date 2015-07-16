<?php
require_once( dirname( __FILE__ ).'/wp-load.php' );

/*
//投稿オブジェクトを作成
$post_data = array(
  'ID'             => [ <投稿 ID> ] // 既存の投稿を更新する場合に指定。
  'post_content'   => [ <文字列> ] // 投稿の全文。
  'post_name'      => [ <文字列> ] // 投稿のスラッグ。
  'post_title'     => [ <文字列> ] // 投稿のタイトル。
  'post_status'    => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | 登録済みカスタムステータス ] // 公開ステータス。デフォルトは 'draft'。
  'post_type'      => [ 'post' | 'page' | 'link' | 'nav_menu_item' | カスタム投稿タイプ ] // 投稿タイプ。デフォルトは 'post'。
  'post_author'    => [ <ユーザー ID> ] // 作成者のユーザー ID。デフォルトはログイン中のユーザーの ID。
  'ping_status'    => [ 'closed' | 'open' ] // 'open' ならピンバック・トラックバックを許可。デフォルトはオプション 'default_ping_status' の値。
  'post_parent'    => [ <投稿 ID> ] // 親投稿の ID。デフォルトは 0。
  'menu_order'     => [ <順序値> ] // 固定ページを追加する場合、メニュー内の並び順を指定。デフォルトは 0。
  'to_ping'        => // スペースまたは改行で区切った、ピンを打つ予定の URL のリスト。デフォルトは空文字列。
  'pinged'         => // スペースまたは改行で区切った、ピンを打った URL のリスト。デフォルトは空文字列。
  'post_password'  => [ <文字列> ] // 投稿パスワード。デフォルトは空文字列。
  'guid'           => // 普通はこれを指定せず WordPress に任せてください。
  'post_content_filtered' => // 普通はこれを指定せず WordPress に任せてください。
  'post_excerpt'   => [ <文字列> ] // 投稿の抜粋。
  'post_date'      => [ Y-m-d H:i:s ] // 投稿の作成日時。
  'post_date_gmt'  => [ Y-m-d H:i:s ] // 投稿の作成日時（GMT）。
  'comment_status' => [ 'closed' | 'open' ] // 'open' ならコメントを許可。デフォルトはオプション 'default_comment_status' の値、または 'closed'。
  'post_category'  => [ array(<カテゴリー ID>, ...) ] // 投稿カテゴリー。デフォルトは空（カテゴリーなし）。
  'tags_input'     => [ '<tag>, <tag>, ...' | array ] // 投稿タグ。デフォルトは空（タグなし）。
  'tax_input'      => [ array( <タクソノミー> => <array | string>, ...) ] // カスタムタクソノミーとターム。デフォルトは空。
  'page_template'  => [ <文字列> ] // テンプレートファイルの名前、例えば template.php 等。デフォルトは空。
);
*/

class HatenabWebhook{
    //はデブ Webhook のキー値
	private $hatenab_key = 'MZAKQT7SXUN8SQIR';
	
	//ログファイル出力先
	private $log_file_path = './hatenab_log/log.txt';
	
	//"[はてブ]" 等のタイトルに付与する共通文字列
	private $title_header = '[はてブ]';
	
	//投稿カテゴリーIDの一覧 (array)
	private $post_category = array(20);
    
	//投稿に設定するタグ一覧 (array)
	private $tags_input = array("bookmark");
	
	//サイトサムネイルのサイズ
	private site_thumbnail_size = 300;
	
	//ログ出力
	public function log(){
		ob_start();
		var_dump($_POST);
		$postData = ob_get_contents();
		ob_end_clean();
		file_put_contents($this->log_file_path, $postData);
	}
	
	//Webhook 
	public function main(){
	
		$key = $_POST['key'];
		if(strcmp($key, $this->hatenab_key) !== 0){
			//キーがあわない場合は投稿処理をしない
			 return;
		}
		
		$status  = $_POST['status'];
		if(strcmp($status, 'add')  === 0){
			//新規追加の場合
			$this->wp_add();
		}
		else if(strcmp($status, 'update')  === 0){
			//更新の場合
			$this->wp_update();
		}
		else if(strcmp($status, 'delete')  === 0){
			//削除
			$this->wp_delete();
		}
    }
	
	//URL画像をアプロードして サムネイルIDを取得する
	public function wp_insert_attachment($post_id,$file_type,$filename,$img_url){
		$wp_upload_dir = wp_upload_dir();
		//URLのファイルをダウンロードする
		$image_data = file_get_contents($img_url);
		$file = $wp_upload_dir['basedir'] . '/' . $filename;
		if( wp_mkdir_p( $wp_upload_dir['path'] ) ) {
			$file = $wp_upload_dir['path'] . '/' . $filename;
		}
		file_put_contents( $file, $image_data );
		
		//メディア投稿を実行
		$attachment = array(
			'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ), 
			'post_mime_type' => $file_type,
			'post_title' => sanitize_file_name($filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		return $attach_id;
    }
	
	//投稿
	private function wp_add(){
		$status  = $_POST['status'];
		$title   = $_POST['title'];
		$url     = $_POST['url'];
		$comment = $_POST['comment'];
		$uid     = $_POST['uid'];

		//投稿記事 内容
		$site_thumbnail = 'http://s.wordpress.com/mshots/v1/'.urlencode($url).'?w='.$this->site_thumbnail_size;
		$content_body =  '<a href="'.$url.'">'.$title.'</a><br>'."\n"
						  .'<img src="'.$site_thumbnail.'" style="width:300px"><br>'."\n"
						  .$comment;

		$post_data = array(
			   'post_status' => 'publish',
			   'post_content' => $content_body,
			   'post_title' => $this->title_header.' '.$title,
			   'post_name' => 'hatenab_'.$uid,
			   'tags_input' => $this->tags_input,
			   'post_category'=> $this->post_category
		);

		// 投稿をデータベースへ追加
		$post_id = wp_insert_post( $post_data );
		
		//アイキャッチ画像を設定
		$thumbnail_id = $this->wp_insert_attachment($post_id, $site_thumbnail);
		set_post_thumbnail($post_id,'image/jpeg','thumbnail_'.$uid.'.jpg',$thumbnail_id);
	}
	
	//更新の場合
	private function wp_update(){

	}
	
	//削除の場合
	private function wp_delete(){
		
	}
}

$obj = new HatenabWebhook();
$obj->log();
$obj->main();
//$obj->wp_insert_attachment(388,'image/jpeg','thumbnail_123456.jpg','http://s.wordpress.com/mshots/v1/http%3A%2F%2Fwww.wpexplorer.com%2Fwordpress-featured-image-url%2F?w=300');

