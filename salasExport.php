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

		$selectSql = "SELECT * FROM sales_slip_trn ORDER BY line_no asc";

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
		$filename = setting::SALAS_EXPORT_FILE_NAME.$today.setting::FILE_TYPE_CSV;

		try{
			$csvstr = "";

			foreach($row as $r){
				$csvstr .= "\"".$r['delete_flg'] . "\",";
				$csvstr .= "\"".$r['close_flg'] . "\",";
				$csvstr .= "\"".$r['any_item_1'] . "\",";
				$csvstr .= "\"".$r['slip_date'] . "\",";
				$csvstr .= "\"".$r['any_item_2'] . "\",";
				$csvstr .= "\"".$r['slip_type'] . "\",";
				$csvstr .= "\"".$r['transaction_type'] . "\",";
				$csvstr .= "\"".$r['tax_inputation'] . "\",";
				$csvstr .= "\"".$r['amount_flaction_type'] . "\",";
				$csvstr .= "\"".$r['tax_flaction_type'] . "\",";
				$csvstr .= "\"".$r['customer_code'] . "\",";
				$csvstr .= "\"".$r['any_item_3'] . "\",";
				$csvstr .= "\"".$r['any_item_4'] . "\",";
				$csvstr .= "\"".$r['line_no'] . "\",";
				$csvstr .= "\"".$r['detail_type'] . "\",";
				$csvstr .= "\"".$r['product_code']. "\",";
				$csvstr .= "\"".$r['any_item_5'] . "\",";
				$csvstr .= "\"".$r['any_item_6'] . "\",";
				$csvstr .= "\"".$r['tax_type'] . "\",";
				$csvstr .= "\"".$r['any_item_7'] . "\",";
				$csvstr .= "\"".$r['any_item_8'] . "\",";
				$csvstr .= "\"".$r['any_item_9'] . "\",";
				$csvstr .= "\"".$r['any_item_10'] . "\",";
				$csvstr .= "\"".$r['quantity'] . "\",";
				$csvstr .= "\"".$r['any_item_11'] . "\",";
				$csvstr .= "\"".$r['amount'] . "\",";
				$csvstr .= "\"".$r['any_item_12'] . "\",";
				$csvstr .= "\"".$r['any_item_13'] . "\",";
				$csvstr .= "\"".$r['any_item_14'] . "\",";
				$csvstr .= "\"".$r['any_item_15'] . "\",";
				$csvstr .= "\"".$r['any_item_16'] . "\",";
				$csvstr .= "\"".$r['any_item_17'] . "\",";
				$csvstr .= "\"".$r['any_item_18'] . "\",";
				$csvstr .= "\"".$r['any_item_19'] . "\",";
				$csvstr .= "\"".$r['any_item_20'] . "\",";
				$csvstr .= "\"".$r['any_item_21'] . "\",";
				$csvstr .= "\"".$r['any_item_22'] . "\",";
				$csvstr .= "\"".$r['any_item_23'] . "\",";
				$csvstr .= "\"".$r['any_item_24'] . "\",";
				$csvstr .= "\"".$r['any_item_25'] . "\",";
				$csvstr .= "\"".$r['any_item_26'] . "\",";
				$csvstr .= "\"".$r['any_item_27'] . "\",";
				$csvstr .= "\"".$r['any_item_28'] . "\",";
				$csvstr .= "\"".$r['any_item_29'] . "\",";
				$csvstr .= "\"".$r['any_item_30'] . "\",";
				$csvstr .= "\"".$r['any_item_31'] . "\",";
				$csvstr .= "\"".$r['any_item_32'] . "\",";
				$csvstr .= "\"".$r['any_item_33'] . "\",";
				$csvstr .= "\"".$r['any_item_34'] . "\",";
				$csvstr .= "\"".$r['any_item_35'] . "\",";
				$csvstr .= "\"".$r['any_item_36'] . "\",";
				$csvstr .= "\"".$r['any_item_37'] . "\",";
				$csvstr .= "\"".$r['any_item_38'] . "\",";
				$csvstr .= "\"".$r['any_item_39'] . "\",";
				$csvstr .= "\"".$r['any_item_40'] . "\",";
				$csvstr .= "\"".$r['any_item_41'] . "\",";
				$csvstr .= "\"".$r['any_item_42'] . "\",";
				$csvstr .= "\"".$r['any_item_43'] . "\"\r\n";
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