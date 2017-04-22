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

//本取込処理 登録
try {

	//wk2の削除クエリ実行
	$deleteSql = "DELETE FROM smaregi_product_wk2";
	$stmt = $pdo -> prepare($deleteSql);
	$stmt -> execute();

	//wk2への登録クエリ実行
	//JANコードは、ＰＫと同一で登録。その後、設定されているものを上書き
	$insertSelectSql = "INSERT INTO smaregi_product_wk2
							(product_id,
							dept_id,
							product_code,
							product_name,
							product_name_kana,
							product_unit_cost,
							tax_type,
							cost,
							group_code,
							terminal_display,
							sales_type,
							per_tax_dept_code,
							sales_class_code)
						SELECT
							product_id,
							dept_id,
							product_id as product_code,
							product_name,
							product_name_kana,
							product_unit_cost,
							tax_type,
							cost,
							group_code,
							terminal_display,
							sales_type,
							per_tax_dept_code,
							sales_class_code
						   FROM smaregi_product_wk";
	$stmt = $pdo -> prepare($insertSelectSql);
	$stmt -> execute();

	//wk2への更新クエリ実行
	$update = "UPDATE smaregi_product_wk2 A
			        	INNER JOIN smaregi_product_trn B
						ON A.product_id = B.product_id
                    SET
					A.product_code = B.product_code,
					A.suggested_retail_price = B.suggested_retail_price,
					A.temp_cost_1 = B.temp_cost_1,
					A.temp_percentage_1 = B.temp_percentage_1,
					A.temp_cost_2 = B.temp_cost_2,
					A.temp_percentage_2 = B.temp_percentage_2,
					A.temp_cost_3 = B.temp_cost_3,
					A.temp_percentage_3 = B.temp_percentage_3,
					A.temp_cost_4 = B.temp_cost_4,
					A.temp_percentage_4 = B.temp_percentage_4,
					A.temp_cost_5 = B.temp_cost_5,
					A.temp_percentage_5 = B.temp_percentage_5
					WHERE A.product_id = B.product_id";
	$stmt = $pdo -> prepare($update);
	$stmt -> execute();

	//trnの削除クエリ実行
	$deleteSql2 = "DELETE FROM smaregi_product_trn";
	$stmt = $pdo -> prepare($deleteSql2);
	$stmt -> execute();

	//trnの登録クエリ実行
	$insertSelectSql2 = "INSERT INTO smaregi_product_trn
 						SELECT * FROM smaregi_product_wk2";
	$stmt = $pdo -> prepare($insertSelectSql2);
	$stmt -> execute();


	$pdo->commit();
	$messege = "弥生販売の商品を取り込みました。";
} catch (Exception $e) {
	exit('DBへの登録に失敗しました。'.$e->getMessage());
	$pdo->rollBack();
} finally {
	$pdo = null;
	$stmt = null;
}
?>
<!doctype html>
<html>
    <head>
            <meta charset="UTF-8">
            <title>商品取込完了</title>
    </head>
    <body>
    <fieldset>
      <legend>商品取込完了</legend>
      <?php
       echo '<a>' .$messege.'</a><br>';
      ?>
      <form action="menu.php">
      <input type="submit" name="submit" value="メニュー戻る" onClick="form.action='menu.php';return true">
      </form>
    </fieldset>
    </body>
</html>