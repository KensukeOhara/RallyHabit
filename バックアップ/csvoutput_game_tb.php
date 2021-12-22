<?php
//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');
$dbh = connectDb();
//　DBに記録されている試合数を表す配列
$matchNum = [""];
//　日時及び選手id、選択した試合の取得
$time = ["", "", "", ""];
//　選手名の取得
$playerName = ["", ""];

//CSVファイルの名を定義
$file_path = "game_tb_data.csv";	//ファイル名

$export_sql = "SELECT `id`, `game_title`, `player_id1`, `player_id2`, `token`, `service_flag`, `courses`, `user_id`, `update_time` FROM `game_tb`";
/*--
foreach( $export_csv_title as $key => $val ){
    $export_header[] =  mb_convert_encoding($val, 'SJIS-win', 'UTF-8',);
}
--*/
//CSV書き込み入力
if(touch($file_path)){
        $file = new SplFileObject($file_path, "w");

        // 出力するCSVにヘッダーを書き込む
       // $file->fputcsv($export_header);

        // データベース検索
        $stmt = $dbh->query($export_sql);

        // 検索結果をCSVに書き込む（SJIS-winに変換するコードに後々更新します。）
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $file->fputcsv($row);

        }

        // データベース接続の切断
        $dbh = null;
    }
?>
