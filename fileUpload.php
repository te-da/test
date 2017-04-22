
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

// 4.1.0より前のPHPでは$FILESの代わりに$HTTP_POST_FILESを使用する必要があります。

//現在日時 YYYYMMDD
$today = date("Ymd");

//ファイル名 shohin_YYYYMMDD.txt
$filename = setting::IMPORT_FILE_NAME.$today.setting::FILE_TYPE_TXT;

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

$deleteSql = "DELETE FROM smaregi_product_wk";
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
	}

	//ファイルクローズ
	fclose($temp);
	$file = null;
} catch (Exception $e) {
	exit('<br>'.'ファイルの読み込みに失敗しました。'.$e->getMessage());
	$pdo->rollBack();
}

$insertSql = "INSERT INTO smaregi_product_wk (product_id,
			dept_id,
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
			 VALUES
			(:product_id,
			:dept_id,
			:product_name,
			:product_name_kana,
			:product_unit_cost,
			:tax_type,
			:cost,
			:group_code,
			:terminal_display,
			:sales_type,
			:per_tax_dept_code,
			:sales_class_code)";

try {
	//ＣＳＶレコード単位で登録
	for($i = 0; $i < count($csv); ++$i) {

		try {
		//sql設定
		$stmt = $pdo -> prepare($insertSql);

		//固定値
		$tax_type = 0;
		$terminal_display = 1;
		$sales_type = 0;

		//sqlに各値をバインド
		$stmt -> bindParam(':product_id', $csv[$i][0]);
		$stmt -> bindParam(':dept_id', $csv[$i][12]);
		$stmt -> bindParam(':product_name', $csv[$i][1]);
		$stmt -> bindParam(':product_name_kana', $csv[$i][2]);
		$stmt -> bindParam(':product_unit_cost', $csv[$i][25]);
		$stmt -> bindParam(':tax_type', $tax_type);
		$stmt -> bindParam(':cost', $csv[$i][34]);
		$stmt -> bindParam(':group_code', $csv[$i][12]);
		$stmt -> bindParam(':terminal_display', $terminal_display);
		$stmt -> bindParam(':sales_type', $sales_type);
		$stmt -> bindParam(':per_tax_dept_code', $csv[$i][13]);
		$stmt -> bindParam(':sales_class_code', $csv[$i][14]);

		//クエリ実行
		$stmt -> execute();

		} catch (Exception $e) {
			$messege = "取込に失敗しました。商品ＩＤ=".$csv[$i][0]."　商品名=".$csv[$i][1]
			           ."　内容=".$e->getMessage();
		}
	}
} catch (Exception $e) {
	exit('<br>'.'DBへの登録に失敗しました。'.$e->getMessage());
	$pdo->rollBack();
} finally {
	$pdo->commit();
}

//新規で登録されたデータ
try {
	$stmt = $pdo->query("select '新規' AS data_type,A.product_id AS product_id ,A.product_name AS product_name
						from smaregi_product_wk A
						left join smaregi_product_trn B on A.product_id = B.product_id
						where B.product_id is null
						union
						select '削除' AS data_type, C.product_id AS product_id, C.product_name AS product_name
						from smaregi_product_trn C
						left join smaregi_product_wk D on C.product_id = D.product_id
						where D.product_id is null order by data_type desc, product_id asc");

	$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (\Exception $e) {
	exit('<br>'.'DBへの参照に失敗しました。'.$e->getMessage());
} finally {
	$pdo = null;
	$stmt = null;
}

?>
<!doctype html>
<html>
    <head>
            <meta charset="UTF-8">
            <title>前回取込時との商品差分</title>
    </head>
    <body>
    <?php
       echo '<a>' .$messege.'</a><br>';
    ?>
    <br>
    <fieldset>
      <legend>前回取込時との商品差</legend>
      <a>前回取込時との差分は下記になります。本取込を行いますか。</a>
      <form action="productImport.php">
      <input type="submit" name="submit" value="本取込" onClick="form.action='productImport.php';return true">
      </form>
      <form action="menu.php">
      <input type="submit" name="submit" value="戻る" onClick="form.action='menu.php';return true">
      </form>
                <br><br>
      <table border="1">
        <tr bgcolor="bule">
          <th align="left">状態</th>
          <th align="left">ID</th>
          <th align="left">名称</th>
        </tr>
          <?php
          if(isset($row)){
            foreach($row as $r){
              echo '<tr>';
              echo '<td align="left" width="20%">',$r['data_type'],'</td>';
              echo '<td align="left" width="20%">',$r['product_id'],'</td>';
              echo '<td align="left" width="60%">',$r['product_name'],'</td>';
              echo '</tr>';
            }
          }
          ?>
      </table>
    </fieldset>
    </body>
</html>
