<!doctype html>
<html>
<script type="text/javascript">

//商品ファイルチェック
function productCheckForm() {
    if (document.productForm.userfile.value == "") {
        alert("ファイルを選択してください。");
		return false;
    } else {
		return true;
    }
}

//売上げファイルチェック
function salasCheckForm() {

    if (document.salasForm.userfile.value == "") {
        alert("ファイルを選択してください。");
		return false;
    }

    //日付チェック
    return ckDate(document.salasForm.salsDate.value);
}

/****************************************************************
* 機　能： 入力された値が日付でYYYY/MM/DD形式になっているか調べる
* 引　数： datestr　入力された値
* 戻り値： 正：true　不正：false
****************************************************************/
function ckDate(datestr) {
    // 正規表現による書式チェック
    if(!datestr.match(/\d{8}$/)){
    	alert("入力された日付の形式が間違っています。");
        return false;
    }
    var vYear = datestr.substr(0, 4) - 0;
    var vMonth = datestr.substr(4, 2) - 1; // Javascriptは、0-11で表現
    var vDay = datestr.substr(6, 2) - 0;
    // 月,日の妥当性チェック
    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
        var vDt = new Date(vYear, vMonth, vDay);
        if(isNaN(vDt)){
        	alert("入力された日付の形式が間違っています。");
            return false;
        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
            return true;
        }else{
            return false;
        }
    }else{
    	alert("入力された日付の形式が間違っています。");
        return false;
    }
}

</script>
    <head>
            <meta charset="UTF-8">
            <title>メニュー</title>
    </head>
    <body>
       <h1>商品管理連携アプリ</h1>
       <h2>メニュー画面</h2>
        <fieldset>
            <legend>商品管理</legend>
            <form enctype="multipart/form-data" name="productForm" action="fileUpload.php" method="POST">
                <fieldset>
                <legend>商品データCSV取込</legend>
                <a>弥生販売の商品データCSVを取り込みます。</a>
                <!-- MAX_FILE_SIZE は、必ず "file" input フィールドより前になければなりません -->
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000000" />
                <!-- input 要素の name 属性の値が、$_FILES 配列のキーになります -->
                対象ファイルをアップロードしてください。
                <a href="help.files/sheet001.htm" target="S1">(ヘルプ)</a><br>
                ファイル：<input name="userfile" type="file" /><br>
                <input type="submit" value="ファイルを送信" onclick="return productCheckForm();"/>
                </fieldset>
            </form>
            <form action="notJanCodeList.php">
                <fieldset>
                    <legend>商品データ検索</legend>
                    <a>商品データを確認します。</a>
                    <input type="submit" value="商品検索">
                </fieldset>
            </form>
            <form action="productExport.php">
                <fieldset>
                    <legend>商品データCSV出力</legend>
                    <a>スマレジ取込用の商品データCSVを出力します。</a>
                    <a href="help.files/sheet002.htm" target="S1">(ヘルプ)</a>
                    <input type="submit" value="商品CSV出力">
                </fieldset>
            </form>
        </fieldset>
        <br>
        <fieldset>
            <legend>売上管理</legend>
            <form enctype="multipart/form-data" name="salasForm" action="salasFileUpload.php" method="POST">
                <fieldset>
                <legend>売上データCSV取込</legend>
                <a>スマレジの売上データCSVを取り込みます。</a>
                <!-- MAX_FILE_SIZE は、必ず "file" input フィールドより前になければなりません -->
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000000" />
                <!-- input 要素の name 属性の値が、$_FILES 配列のキーになります -->
                対象ファイルをアップロードしてください。
                <a href="help.files/sheet003.htm" target="S1">(ヘルプ)</a><br>
                締め日：<input type="text" name="salsDate" value="" />(例：20170101)<br>
                ファイル：<input name="userfile" type="file" /><br>
                <input type="submit" value="ファイルを送信" onclick="return salasCheckForm();"/>
                </fieldset>
            </form>

        </fieldset>
    </body>
</html>