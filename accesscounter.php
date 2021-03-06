<style type"text/css">

/* Webフォントの読み込み */
/* Generated by Font Squirrel (http://www.fontsquirrel.com) on October 17, 2015 */
@font-face {
	font-family: 'cartoon_blocks_christmasRg';
	src: url('/WebFonts/cartoon_blocks_christmas-webfont-t.eot');
	src: url('/WebFonts/cartoon_blocks_christmas-webfont-t.eot?#iefix') format('embedded-opentype'),
		url('/WebFonts/cartoon_blocks_christmas-webfont-t.woff2') format('woff2'),
		url('/WebFonts/cartoon_blocks_christmas-webfont-t.woff') format('woff'),
		url('/WebFonts/cartoon_blocks_christmas-webfont-t.ttf') format('truetype');
	font-weight: normal;
	font-style: normal;
}

.counter-num{
	letter-spacing: 0.1em;
	font-family: "cartoon_blocks_christmasRg";
	font-size: 40px;
	color: #00c9ff;
	text-align: center;
	margin: -0.32em 0em -0.32em -0.133em;	/* -0.24em */
	text-shadow: 0px 1px 0px rgba(0, 201, 255, 0.6),0px 2px 0px rgba(0, 201, 255, 0.3),0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0 5px #fff,0 0.08em 5px rgba(0,0,0,1),0 -0.04em 2px rgba(0,0,0,1);
}

.counter-ty{
	font-size: 9px;
	color: #03D;
	line-height: 0.8;
	text-align: center;
	padding: 0em 0em 0.5em 0em;
}

</style>

<?php
	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED); // Deprecated 抑制
	date_default_timezone_set('Asia/Tokyo');	//タイムゾーンを日本に設定しま～～～す
	ini_set( 'display_errors', 1 );

	//手動で配置したdropbox-sdkを読み込む。ファイルパスはphpの場所に応じて適宜書き換えてください。
	//require_once dirname(__FILE__)."/../../../lib/Dropbox/autoload.php";
	require('vendor/autoload.php');

	// \Dropbox を dbx としても記述できるように定義する
	use \Dropbox as dbx;
	
	//自作関数を入れるので新しいクラスを dbx\Client から継承して作る、継承むずかしいです！！
	class PanyaaExtends extends dbx\Client
	{
		//PanyaaExtendsをnewしたときに引数を書くとここに読み込まれる
		//引数にはアクセストークンを指定します
		public function __construct($accessToken,$AppName)
		{
			parent::__construct($accessToken,$AppName);
		}
		
		//phpのテンポラリを利用してdropboxからデータを読み込んで返す関数
		//たぶん $client->getFileContents("/data/a.dat") とかいう風にやれば読み込んだ文字列を返してくれる？
		//もうちょっと綺麗な場所に書きたいぞい……（ぼやき
		public function getFileContents($filename)
		{
			$stream = fopen('php://memory', 'w+');
			$this->getFile($filename, $stream);
			rewind($stream);
		$fileContents = stream_get_contents($stream);
			fclose($stream);
			return $fileContents;
		}
		
		//phpの○ンポラリに文字列を書き込んでファイルとしてアップロードしちゃう関数
		//ファイルパスと文字列を渡すだけでその名前でその内容のファイルがDropBoxに生成されます
		// dbx\WriteMode::force() を指定しているので、ファイルは上書き保存されます
		public function uploadFileContents($filename,$str)
		{
			$stream = fopen('php://memory', 'w+b');	//bオプションをつけないとアップロードしても0byteのファイルになっちゃう？のでbオプション必須
			rewind($stream);
			fputs($stream, $str);
			rewind($stream);	//めっちゃrewindしてるけどこうしないと正しい内容がアップロードされない
			$this->uploadFile($filename, dbx\WriteMode::force(), $stream);
			fclose($stream);
			return true;
		}
	}
	//クラスを変数に定義、アクセストークンはここで指定します。getenv()でheroku上に設定した環境変数から読み込んでいます。第二引数は適当な文字列で良いっぽいです
	$client = new PanyaaExtends(getenv('DROPBOX_ACCESS_TOKEN'),"panyaa");
	
	$countdata;
	$counts = [0];
	
	$ip = $_SERVER['REMOTE_ADDR'];	//アクセス元のIPアドレスを取得
	$ipdata;
	$iptable = [0];
	
	$path_countdata = __DIR__.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."countdata.dat";
	$path_ipdata = __DIR__.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."ipdata.dat";
	
	$flag = true;

	$countdata = $client->getFileContents($path_countdata);	//dropboxからファイル(これはアクセス数カウントデータ)を読み込んで文字列に返す
	
	$counts = split(" ", $countdata);	//保存データ分割（トータル、今日、昨日、日付）
	
	if(date("j") != $counts[3]) {	//日付確認（日付が変わった場合）
		$counts[3] = date("j");	//日付$count[3]を今日の日付に
		$counts[2] = $counts[1];	//昨日のカウント$count[2]を$count[1]に
		$counts[1] = 0;	//今日のカウント$count[1]を0に
		$ipdata = "512.512.512.512 512.512.512.512";	//仮データを設定
		$iptable = split(" ", $ipdata);	//仮データ分割して配列にぽいぽい
	}else{
		$ipdata = $client->getFileContents($path_ipdata);	//dropboxからファイル(これはipテーブル)を読み込んで文字列に返す
		$iptable = split(" ", $ipdata);
	}

	for($i=0;$i<100;$i++){
		if($ip === $iptable[$i]){
			$flag = false;
			break;
		}
	}

	if($flag){
		$counts[0]++;	//トータルカウント+1
		$counts[1]++;	//今日のカウント+1
		$countdata = join(" ", $counts);	//保存データ作成（半角スペースで結合）

		array_unshift($iptable,$ip);	//ipアドレスを配列の最初にプッシュする

		$ipdata = join(" ",$iptable);

		$client->uploadFileContents($path_countdata,$countdata);	//文字列をファイルに書き込んでアップロード
		$client->uploadFileContents($path_ipdata, $ipdata);	//文字列をファイルに書き込んでアップロード
	}

	echo "<div class=\"counter-num\">",sprintf("%06d", $counts[0]),"</div>";
	echo "<div class=\"counter-ty\"><br>今日：", $counts[1], "　昨日：", $counts[2], "</div>\n";

?>

