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

		//検索商品ID
		$detailProductId = $_GET['product_id'];
 		include('./setting.php');

		$messege = "";
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

		$selectSql = "SELECT * FROM smaregi_product_trn
						WHERE product_id = :product_id
						ORDER BY product_id asc";

		try {
			//クエリ実行
			$stmt = $pdo -> prepare($selectSql);

			$stmt -> bindParam(':product_id', $detailProductId, PDO::PARAM_INT);

			$stmt -> execute();
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (Exception $e) {
			exit('<br>'.'DBへの検索に失敗しました。'.$e->getMessage());
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
            <title>商品詳細</title>
    </head>
    <body>
        <?php
       echo '<a>' .$messege.'</a><br>';
    ?>
        <br>
    <fieldset>
      <legend>メニュー画面に戻る</legend>
      <form action="menu.php">
      <input type="submit" name="submit" value="メニューに戻る">
      </form>
    </fieldset>
    <br>
        <form action="productUpdate.php" method="POST">
            <fieldset>
                <legend>商品詳細フォーム</legend>
                      <table>
                          <?php
          if(isset($row)){
            foreach($row as $r){
            	echo '<tr><td><label for="product_id">商品コード</label></td>';
            	echo '<td>',$r['product_id'],'</td></tr>';
            	echo '<input type="hidden" name ="product_id" id="product_id" value="',$r['product_id'],'" ?></tr>';
            	echo '<tr><td><label for="dept_id">部門ＩＤ</label></td>';
            	echo '<td>',$r['dept_id'],'</td>';
            	echo '<tr><td><label for="janCode">JANコード</label></td>';
            	echo '<td>',$r['product_code'],'</td></tr>';
            	echo '<tr><td><label for="product_name">商品名</label></td>';
            	echo '<td>',$r['product_name'],'</td></tr>';
            	echo '<tr><td><label for="product_name_kana">商品名カナ</label></td>';
            	echo '<td>',$r['product_name_kana'],'</td></tr>';
            	echo '<tr><td><label for="product_unit_cost">商品単価</label></td>';
            	echo '<td>',$r['product_unit_cost'],'</td></tr>';
            	echo '<tr><td><label for="tax_type">税区分</label></td>';
            	echo '<td>',$r['tax_type'],'</td></tr>';
            	echo '<tr><td><label for="cost">原価</label></td>';
            	echo '<td>',$r['cost'],'</td></tr>';
            	echo '<tr><td><label for="group_code">グループコード</label></td>';
            	echo '<td>',$r['group_code'],'</td></tr>';
            	echo '<tr><td><label for="terminal_display">端末表示</label></td>';
            	echo '<td>',$r['terminal_display'],'</td></tr>';
            	echo '<tr><td><label for="sales_type">売上区分</label></td>';
            	echo '<td>',$r['sales_type'],'</td></tr>';
            	echo '<tr><td><label for="sales_class_code">種別</label></td>';
            	echo '<td>',$r['sales_class_code'],'</td></tr>';
            	echo '<tr><td><label for="suggested_retail_price">希望小売価格</label></td>';
            	echo '<td>',$r['suggested_retail_price'],'</td></tr>';
            	echo '<tr><td><label for="yobiPrice1">予備１ 単価</label></td>';
            	echo '<td>',$r['temp_cost_1'],'</td>';
            	echo '<td>',$r['temp_percentage_1'],'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice2">予備２ 単価</label></td>';
            	echo '<td>',$r['temp_cost_2'],'</td>';
            	echo '<td>',$r['temp_percentage_2'],'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice3">予備３ 単価</label></td>';
            	echo '<td>',$r['temp_cost_3'],'</td>';
            	echo '<td>',$r['temp_percentage_3'],'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice4">予備４ 単価</label></td>';
            	echo '<td>',$r['temp_cost_4'],'</td>';
            	echo '<td>',$r['temp_percentage_4'],'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice5">予備５ 単価</label></td>';
            	echo '<td>',$r['temp_cost_5'],'</td>';
            	echo '<td>',$r['temp_percentage_5'],'%<td></tr>';
            }
          }
          ?>
           </table>
                <input type="submit" id="update" name="update" value="商品更新">
            </fieldset>
        </form>
        <br>
    </body>
</html>