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
		$updateProductId = $_POST['product_id'];
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

			$stmt -> bindParam(':product_id', $updateProductId, PDO::PARAM_INT);

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
<script type="text/javascript">

//更新時の必須チェック
function updateSubmit() {
	var result = checkForm();
	result = confirm( "全計算は行わずに更新してもよいですか？行う場合は、いいえを押し、全計算ボタンを押してくさだい。" );
	return result;
}

//JANコードチェック
function checkForm() {
    if (document.updateFrom.product_code.value == "") {
        alert("JANコード項目を入力して下さい。");
		return false;
    } else {
		return true;
    }
}

//全再計算
function allCalForm() {

	 calForm(1);
	 calForm(2);
	 calForm(3);
	 calForm(4);
	 calForm(5);

	return false;
}

//計算ボタン押下時
function calForm(id) {

	var costName = "temp_cost_" + id;
	var percentageName = "temp_percentage_" + id;
	var suggestedRetailPrice = document.getElementById("product_unit_cost").value;
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
            <title>商品変更</title>
    </head>
    <body>
        <h1>商品変更画面</h1>
        <form name ="updateFrom" action="productComplete.php" method ="POST">
        <input type="hidden" id="actionDiv" name="actionDiv" value="1"/>
            <fieldset>
                <legend>商品変更フォーム</legend>
                <table>
        <?php
          if(isset($row)){
            foreach($row as $r){

					$product_unit_cost2 = floor($r['product_unit_cost'] /1.08);
					            
                    echo '<tr><td><label for="product_id">商品コード</label></td>';
					echo '<td><input type="text" id="product_id" name="product_id" value="',$r['product_id'],'" readonly="readonly" style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="dept_id">部門ＩＤ</label></td>';
					echo '<td><input type="text" id="dept_id" name="dept_id" value="',$r['dept_id'],'" readonly="readonly"  style="background-color:#919191"; /></td>';
					echo '<tr><td><label for="product_code">JANコード</label></td>';
					echo '<td><input type="text" id="product_code" name="product_code" value="',$r['product_code'],'" ></td></tr>';
					echo '<tr><td><label for="product_name">商品名</label></td>';
					echo '<td><input type="text" id="product_name" name="product_name" value="',$r['product_name'],'" readonly="readonly" style="background-color:#919191";/></td></tr>';
					echo '<tr><td><label for="product_name_kana">商品名カナ</label></td>';
					echo '<td><input type="text" id="product_name_kana" name="product_name_kana" value="',$r['product_name_kana'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="product_unit_cost">商品単価(税込)</label></td>';
					echo '<td><input type="text" id="product_unit_cost" name="product_unit_cost" value="',$r['product_unit_cost'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="product_unit_cost">商品単価(税別)</label></td>';
					echo '<td><input type="text" id="product_unit_cost" name="product_unit_cost2" value="',$product_unit_cost2,'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="tax_type">税区分</label></td>';
					echo '<td><input type="text" id="tax_type" name="tax_type" value="',$r['tax_type'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="cost">原価(税別)</label></td>';
					echo '<td><input type="text" id="cost" name="cost" value="',$r['cost'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="group_code">グループコード</label></td>';
					echo '<td><input type="text" id="group_code" name="group_code" value="',$r['group_code'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="terminal_display">端末表示</label></td>';
					echo '<td><input type="text" id="terminal_display" name="terminal_display" value="',$r['terminal_display'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="sales_type">売上区分</label></td>';
					echo '<td><input type="text" id="sales_type" name="sales_type" value="',$r['sales_type'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="sales_class_code">種別</label></td>';
					echo '<td><input type="text" id="sales_class_code" name="sales_class_code" value="',$r['sales_class_code'],'" readonly="readonly"  style="background-color:#919191"; /></td></tr>';
					echo '<tr><td><label for="yobiPrice1">希望小売価格(税別)</label></td>';
					echo '<td><input type="text" id="suggested_retail_price" name="suggested_retail_price" value="',$r['suggested_retail_price'],'" /></td>';
					echo '<td><input type="submit" id="allSal" name="allSal" value="全計算" onclick="return allCalForm();"><td></tr>';
					echo '<tr><td colspan="3"><font color="red">単価が入っている場合、単価と商品単価を元に計算します。</font></td></tr>';
					echo '<tr><td colspan="3"><font color="red">%をもとに計算したい場合は、単価を消してください。</font></td></tr>';
					echo '<tr><td><label for="yobiPrice1">予備１ 単価</label></td>';
					echo '<td><input type="text" id="temp_cost_1" name="temp_cost_1" value="',$r['temp_cost_1'],'" /></td>';
					echo '<td><input type="text" id="temp_percentage_1" name="temp_percentage_1" value="',$r['temp_percentage_1'],'" />%<td>';
					echo '<td><input type="submit" id="sal1" name="sal1" value="計算１" onclick="return calForm(1);"><td></tr>';
					echo '<tr><td><label for="yobiPrice2">予備２ 単価</label></td>';
					echo '<td><input type="text" id="temp_cost_2" name="temp_cost_2" value="',$r['temp_cost_2'],'" /></td>';
					echo '<td><input type="text" id="temp_percentage_2" name="temp_percentage_2" value="',$r['temp_percentage_2'],'" />%<td>';
					echo '<td><input type="submit" id="sal2" name="sal2" value="計算２" onclick="return calForm(2);"><td></tr>';
					echo '<tr><td><label for="yobiPrice3">予備３ 単価</label></td>';
					echo '<td><input type="text" id="temp_cost_3" name="temp_cost_3" value="',$r['temp_cost_3'],'" /></td>';
					echo '<td><input type="text" id="temp_percentage_3" name="temp_percentage_3" value="',$r['temp_percentage_3'],'" />%<td>';
					echo '<td><input type="submit" id="sal3" name="sal3" value="計算３" onclick="return calForm(3);"><td></tr>';
					echo '<tr><td><label for="yobiPrice4">予備４ 単価</label></td>';
					echo '<td><input type="text" id="temp_cost_4" name="temp_cost_4" value="',$r['temp_cost_4'],'" /></td>';
					echo '<td><input type="text" id="temp_percentage_4" name="temp_percentage_4" value="',$r['temp_percentage_4'],'" />%<td>';
					echo '<td><input type="submit" id="sal4" name="sal4" value="計算４" onclick="return calForm(4);"><td></tr>';
					echo '<tr><td><label for="yobiPrice5">予備５ 単価</label></td>';
					echo '<td><input type="text" id="temp_cost_5" name="temp_cost_5" value="',$r['temp_cost_5'],'" /></td>';
					echo '<td><input type="text" id="temp_percentage_5" name="temp_percentage_5" value="',$r['temp_percentage_5'],'" />%<td>';
					echo '<td><input type="submit" id="sal5" name="sal5" value="計算５" onclick="return calForm(5);"><td></tr>';
        	}
		}
	?>
                </table>
                <input type="submit" id="update" name="update" value="商品更新" onclick="return updateSubmit();">
            </fieldset>
        </form>
        <br>
        <form action="Menu.php">
            <input type="submit" value="メニューに戻る">
        </form>
    </body>
</html>