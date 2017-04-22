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
		$searchProductId = $_POST['searchProductId'];
		//検索商品名
 		$searchProductName = $_POST['searchProductName'];

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

		$selectSql = "SELECT * FROM smaregi_product_trn ";

		$whereSql = "WHERE ";
		$andWhereSql = "AND ";
		$idWhereSql = "product_id = :product_id ";
		$nameWhereSql = "convert(product_name using utf8) collate utf8_unicode_ci  LIKE :product_name ";
		$orderBySql = "ORDER BY product_id asc";

		if ($searchProductId != null && $searchProductName != null) {
			$selectSql = $selectSql.$whereSql.$idWhereSql.$andWhereSql.$nameWhereSql;
		} elseif ($searchProductId != null) {
			$selectSql = $selectSql.$whereSql.$idWhereSql;
		} elseif ($searchProductName != null) {
			$selectSql = $selectSql.$whereSql.$nameWhereSql;
		}

		$selectSql = $selectSql.$orderBySql;

		$bindProductName = '%'.$searchProductName.'%';

		try {
			//クエリ実行
			$stmt = $pdo -> prepare($selectSql);
			if ($searchProductId != null) {
				$stmt -> bindParam(':product_id', $searchProductId, PDO::PARAM_INT);
			}

			if ($searchProductName != null) {
				$stmt -> bindParam(':product_name', $bindProductName, PDO::PARAM_STR);
			}

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
            <title>商品検索</title>
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
    <fieldset>
      <legend>商品検索</legend>
      <a>商品を検索します。</a>
      <form action="productSearch.php" method="POST">
      <?php
      echo '商品コード：<input type="text" id="searchProductId" name="searchProductId" value="',$searchProductId,'" />';
      echo '商品名：<input type="text" id="searchProductName" name="searchProductName" value="',$searchProductName,'" />';
      ?>
      <input type="submit" name="submit" value="検索">
      </form>
                <br><br>
      <table border="1">
        <tr bgcolor="blue">
          <th align="left">商品コード</th>
          <th align="left">JANコード</th>
          <th align="left">名称</th>
          <th align="left">変更</th>
        </tr>
          <?php
          if(isset($row)){
            foreach($row as $r){
            	echo '<tr>';
            	echo '<form action="productUpdate.php" method="POST">';
            	echo '<td align="left" width="20%"><a href="./productDetail.php?product_id='.$r['product_id'].'">',$r['product_id'],'</td>';
            	echo '<td align="left" width="20%">',$r['product_code'],'</td>';
            	echo '<td align="left" width="50%">',$r['product_name'],'</td>';
            	echo '<td align="left" width="10%"> <input type="hidden" name="product_id" value="'.$r['product_id'].'">';
            	echo '<input type="submit" name="submit" value="変更"></td>';
            	echo '</form>';
            	echo '</tr>';
            }
          }
          ?>
      </table>
    </fieldset>
    </body>
</html>
