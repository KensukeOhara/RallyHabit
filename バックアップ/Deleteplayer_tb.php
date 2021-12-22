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
$file_path = "player_tb_data.csv";	//ファイル名

$export_sql = "DELETE `id`, `user_id`, `name`, `affiliation`, `dominant_hand` FROM `player_tb` WHERE `id` = 'NULL'";
/*--
foreach( $export_csv_title as $key => $val ){
    $export_header[] =  mb_convert_encoding($val, 'SJIS-win', 'UTF-8',);
}
--*/
//CSV書き込み入力

