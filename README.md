# wordpress-hatenab-webhook
はてブとWordPressを連動する簡易プラグイン

<b>設定方法</b><br>
<ol>
<li>はてブの設定画面を開いて 外部サイト連携 の キー値をコピーする<br><a href="http://blog.tf-web.jp/wp-content/uploads/2015/07/43cebd2a4af8fb7414da23c86c457d1c.png"><img src="http://blog.tf-web.jp/wp-content/uploads/2015/07/43cebd2a4af8fb7414da23c86c457d1c-300x106.png" alt="スクリーンショット 2015-07-16 9.24.49" width="300" height="106" class="alignnone size-medium wp-image-400" /></a><br></li>
<li>github から wordpress-hatenab-webhook をダウンロードする<br><a href="https://github.com/tfuru/wordpress-hatenab-webhook/archive/master.zip">wordpress-hatenab-webhook一式[zip]</a></li>
<li>hatenab-webhook.php をテキストエディタで開いて はてブキー値 の書き換えを行う<br><pre class="code">private $hatenab_key = 'ここを1でコピーした文字列に書き換える';</pre></li>
<li>WordPress設置デレクトリに hatenab-webhook.php と hatenab_logデレクトリをアップロードする</li>
<li>hatenab_logディレクトリのパーミッションを 700 等に設定する</li>
<li>はてブの設定画面を開いて 外部サイト連携 部分に hatenab-webhook.php にアクセスするURLを設定する<pre class="code">例)http://blog.example.com/hatenab-webhook.php</pre></li>
<li> はてブすると記事が連動して自動で投稿される</li>
</ol>

<b>参考</b><br>
<a href="http://tf-web.jp/post-398/">はてブとWordPressを連動する簡易プラグインを作ってみた</a><br>
<a href="http://tf-web.jp/post-398/">はてブとWordPressを連動する簡易プラグインを作ってみた</a><br>

