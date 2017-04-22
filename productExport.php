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

//メッセージ領域
$messege = null;

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

$selectSql = "SELECT * FROM smaregi_product_trn ORDER BY product_id asc";

try {
	//クエリ実行
	$stmt = $pdo ->query($selectSql);

	$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
	exit('<br>'.'DBへの検索に失敗しました。'.$e->getMessage());
	$pdo->rollBack();
} finally {
	$pdo = null;
	$stmt = null;
}

// ファイル名
//現在日時
$today = date("Ymd");

//ファイル名 sumareji_shohin_YYYYMMDD.txt
$filename = setting::EXPORT_FILE_NAME.$today.setting::FILE_TYPE_TXT;

try{
	$csvstr = "";

	foreach($row as $r){
		$csvstr .= $r['product_id'] . ",";
		$csvstr .= $r['dept_id'] . ",";
		$csvstr .= "\"".$r['product_code'] . "\",";
		$csvstr .= "\"".$r['product_name'] . "\",";
		$csvstr .= "\"".$r['product_name_kana'] . "\",";
		$csvstr .= $r['product_unit_cost'] . ",";
		$csvstr .= $r['tax_type'] . ",";
		$csvstr .= $r['cost'] . ",";
		$csvstr .= $r['group_code'] . ",";
		$csvstr .= $r['terminal_display'] . ",";
		$csvstr .= $r['sales_type'] . "\r\n";
	}

	//CSV出力
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename='.$filename);
	echo mb_convert_encoding($csvstr, "SJIS", "UTF-8"); //Shift-JISに変換したい場合のみ
	exit();

	}catch(ErrorException $ex){
		exit('<br>'.'ファイルのダウンロードに失敗しました。'.$e->getMessage());
	}

?>