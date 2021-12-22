<?php
    $removing_id = $_POST['id'];
    $token = $_POST['token']; //token

    //接続用関数の呼び出し
    require_once(__DIR__.'/functions.php');
    //DBへの接続
    $dbh = connectDB();
    //データベースの接続確認
    if (!$dbh) {  //接続できていない場合
        echo 'DBに接続できていません．';
        return;
    }

    //削除処理
    $sql = 'DELETE FROM `game_tb` WHERE `id`="' . $removing_id . '"';
    $dbh->exec($sql); //SQLの実行

    //同一トークンで登録した試合の表示
    $sql = 'SELECT `id`, `courses` FROM `game_tb` WHERE `token`="' . $token .'"';
    $sth = $dbh->query($sql);
    $results = $sth->fetchAll();
    $courses = [];
    foreach ($results as $result) {
        $courses[$result['id']] = html_entity_decode($result['courses']);
    }
    echo json_encode($courses);
?>