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

	//遷移区分
	$actionDiv = $_POST['actionDiv'];
	//商品コード
	$product_id = $_POST['product_id'];
	//部門ＩＤ
	$dept_id = $_POST['dept_id'];
	//JANコード
	$product_code = $_POST['product_code'];
	//商品名
	$product_name = $_POST['product_name'];
	//商品名カナ
	$product_name_kana = $_POST['product_name_kana'];
	//商品単価
	$product_unit_cost = $_POST['product_unit_cost'];
	//税区分
	$tax_type = $_POST['tax_type'];
	//原価
	$cost = $_POST['cost'];
	//グループコード
	$group_code = $_POST['group_code'];
	//端末表示
	$terminal_display = $_POST['terminal_display'];
	//売上区分
	$sales_type = $_POST['sales_type'];
	//種別
	$sales_class_code = $_POST['sales_class_code'];
	//希望小売価格
	$suggested_retail_price = $_POST['suggested_retail_price'];
	//予備１ 単価
	$temp_cost_1 = $_POST['temp_cost_1'];
	$temp_percentage_1 = $_POST['temp_percentage_1'];
	//予備２ 単価
	$temp_cost_2 = $_POST['temp_cost_2'];
	$temp_percentage_2 = $_POST['temp_percentage_2'];
	//予備３ 単価
	$temp_cost_3 = $_POST['temp_cost_3'];
	$temp_percentage_3 = $_POST['temp_percentage_3'];
	//予備４ 単価
	$temp_cost_4 = $_POST['temp_cost_4'];
	$temp_percentage_4 = $_POST['temp_percentage_4'];
	//予備５ 単価
	$temp_cost_5 = $_POST['temp_cost_5'];
	$temp_percentage_5 = $_POST['temp_percentage_5'];

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

	$updateSql = "UPDATE smaregi_product_trn SET
						product_code=:product_code,
						suggested_retail_price=:suggested_retail_price,
						temp_cost_1=:temp_cost_1,
						temp_percentage_1=:temp_percentage_1,
						temp_cost_2=:temp_cost_2,
						temp_percentage_2=:temp_percentage_2,
						temp_cost_3=:temp_cost_3,
						temp_percentage_3=:temp_percentage_3,
						temp_cost_4=:temp_cost_4,
						temp_percentage_4=:temp_percentage_4,
						temp_cost_5=:temp_cost_5,
						temp_percentage_5=:temp_percentage_5
					WHERE product_id = :product_id";

	try {
		//クエリ実行
		$stmt = $pdo -> prepare($updateSql);

		//sqlに各値をバインド
		$stmt -> bindParam(':product_id', $product_id);
		$stmt -> bindParam(':product_code', $product_code);
		$stmt -> bindParam(':suggested_retail_price', $suggested_retail_price);
		$stmt -> bindParam(':temp_cost_1', $temp_cost_1);
		$stmt -> bindParam(':temp_percentage_1', $temp_percentage_1);
		$stmt -> bindParam(':temp_cost_2', $temp_cost_2);
		$stmt -> bindParam(':temp_percentage_2', $temp_percentage_2);
		$stmt -> bindParam(':temp_cost_3', $temp_cost_3);
		$stmt -> bindParam(':temp_percentage_3', $temp_percentage_3);
		$stmt -> bindParam(':temp_cost_4', $temp_cost_4);
		$stmt -> bindParam(':temp_percentage_4', $temp_percentage_4);
		$stmt -> bindParam(':temp_cost_5', $temp_cost_5);
		$stmt -> bindParam(':temp_percentage_5', $temp_percentage_5);

		$stmt -> execute();
		$pdo->commit();

	} catch (Exception $e) {
		exit('<br>'.'DBへの更新に失敗しました。'.$e->getMessage());
		$pdo->rollBack();
	} finally {
		$pdo = null;
		$stmt = null;
		$messege = "下記の内容でと更新いたしました。";
	}



		?>
<!doctype html>
<html>
<script type="text/javascript">
//更新時の必須チェック
function updateSubmit() {
	var result = checkForm();

	//各値再計算
	for(int i = 0; i < 6; i++){

	 calForm(1);
	 calForm(2);
	 calForm(3);
	 calForm(4);
	 calForm(5);
	}
	return result;
}

//計算ボタン押下時
function updateFrom() {
	 var result = checkForm();

	 return result;
}

function checkForm() {
    if (document.updateFrom.product_code.value == "") {
        alert("JINコード項目を入力して下さい。");
		return false;
    } else {
		return true;
    }
}

function calForm(id) {

	var costName = "temp_cost_" + id;
	var percentageName = "temp_percentage_" + id;
	var suggestedRetailPrice = document.getElementById("suggested_retail_price").value;
	var tempCost = document.getElementById(costName).value;
	var tempPercentage = document.getElementById(percentageName).value;

 	//希望小売価格が設定されている場合
    if (suggestedRetailPrice != "") {

        //予備単価が設定されている場合
        if (tempCost != "") {

          	//小数点の第一位切り上げ
         	var sumPow = Math.pow(10, 1) ;
			Math.ceil
			var result = tempCost / suggestedRetailPrice;

        	document.getElementById(percentageName).value = Math.ceil((result * 100) * sumPow) / sumPow ;;

        //単価割合が設定されている場合
        } else if(tempPercentage != "") {

	       	//整数の第一位切り上げ
	       	var sumPow = Math.pow(10, -1) ;
	       	Math.ceil

        	var result = suggestedRetailPrice * (tempPercentage / 100);
            document.getElementById(costName).value = Math.ceil( result * sumPow ) / sumPow ;;
        }
    }

    return false;
}
</script>
    <head>
            <meta charset="UTF-8">
            <title>商品更新完了</title>
    </head>
    <body>
        <h1>商品更新完了画面</h1>
       <form action="Menu.php">
       <input type="hidden" id="actionDiv" name="actionDiv" value="9"/>
 <br/>
      <?php
       echo '<a>' .$messege.'</a><br>';
      ?>
      <br/>
            <fieldset>
                <legend>商品詳細フォーム</legend>
                      <table>
            <?php
            	$product_unit_cost2 = floor($product_unit_cost /1.08);
            
            	echo '<tr><td><label for="product_id">商品コード</label></td>';
            	echo '<td>',$product_id,'</td></tr>';
            	echo '<tr><td><label for="dept_id">部門ＩＤ</label></td>';
            	echo '<td>',$dept_id,'</td>';
            	echo '<tr><td><label for="janCode">JANコード</label></td>';
            	echo '<td>',$product_code,'</td></tr>';
            	echo '<tr><td><label for="product_name">商品名</label></td>';
            	echo '<td>',$product_name,'</td></tr>';
            	echo '<tr><td><label for="product_name_kana">商品名カナ</label></td>';
            	echo '<td>',$product_name_kana,'</td></tr>';
            	echo '<tr><td><label for="product_unit_cost">商品単価(税込)</label></td>';
            	echo '<td>',$product_unit_cost,'</td></tr>';
            	echo '<tr><td><label for="product_unit_cost2">商品単価(税別)</label></td>';
            	echo '<td>',$product_unit_cost2,'</td></tr>';
            	echo '<tr><td><label for="tax_type">税区分</label></td>';
            	echo '<td>',$tax_type,'</td></tr>';
            	echo '<tr><td><label for="cost">原価</label></td>';
            	echo '<td>',$cost,'</td></tr>';
            	echo '<tr><td><label for="group_code">グループコード</label></td>';
            	echo '<td>',$group_code,'</td></tr>';
            	echo '<tr><td><label for="terminal_display">端末表示</label></td>';
            	echo '<td>',$terminal_display,'</td></tr>';
            	echo '<tr><td><label for="sales_type">売上区分</label></td>';
            	echo '<td>',$sales_type,'</td></tr>';
            	echo '<tr><td><label for="sales_class_code">種別</label></td>';
            	echo '<td>',$sales_class_code,'</td></tr>';
            	echo '<tr><td><label for="suggested_retail_price">希望小売価格(税別)</label></td>';
            	echo '<td>',$suggested_retail_price,'</td></tr>';
            	echo '<tr><td><label for="yobiPrice1">予備１ 単価</label></td>';
            	echo '<td>',$temp_cost_1,'</td>';
            	echo '<td>',$temp_percentage_1,'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice2">予備２ 単価</label></td>';
            	echo '<td>',$temp_cost_2,'</td>';
            	echo '<td>',$temp_percentage_2,'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice3">予備３ 単価</label></td>';
            	echo '<td>',$temp_cost_3,'</td>';
            	echo '<td>',$temp_percentage_3,'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice4">予備４ 単価</label></td>';
            	echo '<td>',$temp_cost_4,'</td>';
            	echo '<td>',$temp_percentage_4,'%<td></tr>';
            	echo '<tr><td><label for="yobiPrice5">予備５ 単価</label></td>';
            	echo '<td>',$temp_cost_5,'</td>';
            	echo '<td>',$temp_percentage_5,'%<td></tr>';
          ?>
           </table>
            </fieldset>
        <br>
            <input type="submit" value="メニューに戻る">
        </form>
        <form action="notJanCodeList.php">
                    <input type="submit" value="検索画面に戻る">
        </form>
    </body>
</html>