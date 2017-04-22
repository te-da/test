
<?php
//エラー処理
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	// エラーを例外に変換する
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

	set_exception_handler(function($e) {
		// display_errorsの値によって処理を変更する
		if (ini_get('display_errors')) {
			echo '<pre>' . $e . '</pre>';
		} else {
			// エラーログに保存なりなんなりしてエラー画面表示
			// ...
			//readfile('path/to/error.html');
		}
	});

include('./setting.php');

$messege = null;

//締め日
$salsDate = $_POST['salsDate'];

// 4.1.0より前のPHPでは$FILESの代わりに$HTTP_POST_FILESを使用する必要があります。

//現在日時 YYYYMMDD
$today = date("Ymd");

//ファイル名 shohin_YYYYMMDD.txt
$filename = setting::SALAS_IMPORT_FILE_NAME.$today.setting::FILE_TYPE_CSV;

//ファイルパス
$uploadfile = setting::UPLOAD_DIR_PATH . $filename;

//ファイルのアップロード
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	$messege = "対象ファイルのアップロードに成功しました。".'<br>';
} else {
    exit('<br>'.'対象ファイルのアップロードに失敗しました。指定したファイルが存在するか確認してください。');
}

//エラー確認用のためコメントアウト
//print_r($_FILES);

//ホスト名
//DB接続
try {

	$db_url = 'mysql:host='. setting::DB_HOST . ';dbname=' . setting::DB_NAME .';character='. setting::DB_CHARSET;
	$pdo = new PDO($db_url, setting::DB_USER, setting::DB_PASS,
			array(PDO::ATTR_EMULATE_PREPARES => false));

	//トランザクション処理開始
	$pdo->beginTransaction();

} catch (PDOException $e) {
	exit('<br>'.'データベース接続失敗。'.$e->getMessage());
}

$deleteSql = "DELETE FROM sales_slip_trn";
try {
	//クエリ実行
	$stmt = $pdo -> prepare($deleteSql);
	$stmt -> execute();

} catch (Exception $e) {
	exit('<br>'.'DBへの削除に失敗しました。'.$e->getMessage());
	$pdo->rollBack();
}

//文字コード指定
setlocale(LC_ALL, 'ja_JP.UTF-8');

try {
	//ファイル取込
	$data = file_get_contents($uploadfile);

	//文字コード変換
	$data = mb_convert_encoding($data, 'UTF-8', 'sjis-win');

	//tempファイル作成
	$temp = tmpfile();

	//tempファイルのメタデータ作成
	$meta = stream_get_meta_data($temp);

	//データ書き込み
	fwrite($temp, $data);
	rewind($temp);

	//ファイル読み込み
	$file = new SplFileObject($meta['uri']);
	$file->setFlags(SplFileObject::READ_CSV);
	$csv  = array();
	foreach($file as $line) {
		$csv[] = $line;
		if (count($line) > 1 &&  $line[1] == "部門販売商品") {
			exit('<br>'.'対象売上ファイルに部門販売商品が登録されています。スマレジ
					の取引明細を変更して、再度やり直してください。');
		}
	}

	//ファイルクローズ
	fclose($temp);
	$file = null;
} catch (Exception $e) {
	exit('<br>'.'ファイルの読み込みに失敗しました。'.$e->getMessage());
	$pdo->rollBack();
}

$insertSql = "INSERT INTO sales_slip_trn (
				 slip_date,
				 customer_code,
				 line_no,
				 product_code,
				 any_item_6,
				 quantity,
				 amount)
				SELECT
					:slip_date,
					:customer_code,
					:line_no,
					product_id,
					:any_item_6,
					:quantity,
					:amount
				FROM smaregi_product_trn
				WHERE product_code = :product_code";

try {
	//ＣＳＶレコード単位で登録
	for($i = 0; $i < (count($csv)-1); ++$i) {

		if ($csv[$i][0] == "合計" || $csv[$i][0] == "商品コード"){
			continue;
		}

		try {
		//sql設定
		$stmt = $pdo -> prepare($insertSql);

		//固定値
		$customer_code = 9998;
		$line_no = $i;
		$amount = $csv[$i][8] - $csv[$i][9];

		//sqlに各値をバインド
		$stmt -> bindParam(':slip_date', $salsDate, PDO::PARAM_INT);
		$stmt -> bindParam(':customer_code', $customer_code);
		$stmt -> bindParam(':line_no', $line_no);
		$stmt -> bindParam(':product_code', $csv[$i][0]);
		$stmt -> bindParam(':any_item_6', $csv[$i][1]);
		$stmt -> bindParam(':quantity',$amount);
		$stmt -> bindParam(':amount',  $csv[$i][3]);


		//クエリ実行
		$stmt -> execute();

		$count = $stmt -> rowCount();
		if ($count == 0) {
			exit("登録されていないJANコードがあります。JANコード=".$csv[$i][0]."　商品名=".$csv[$i][1])
				.ファイルのJANコードを修正してやり直してください。;
		}

		} catch (Exception $e) {
			$messege = "取込に失敗しました。JANコード=".$csv[$i][0]."　商品名=".$csv[$i][1]
			           ."　内容=".$e->getMessage();
		}
	}
} catch (Exception $e) {
	exit('<br>'.'DBへの登録に失敗しました。'.$e->getMessage());
	$pdo->rollBack();
} finally {
	$pdo->commit();
}


?>
<!doctype html>
<html>
    <head>
            <meta charset="UTF-8">
            <title>スマレジ売上取込</title>
    </head>
    <body>
    <?php
       echo '<a>' .$messege.'</a><br>';
    ?>
    <br>
    <fieldset>
      <legend>弥生用スマレジ売上出力</legend>
      <a>弥生用スマレジ売上ファイルを出力しますか。</a>
      <form action="salasExport.php">
      <input type="submit" name="submit" value="出力" onClick="form.action='salasExport.php';return true">
      </form>
      <form action="menu.php">
      <input type="submit" name="submit" value="戻る" onClick="form.action='menu.php';return true">
      </form>
    </fieldset>
    </body>
</html>
