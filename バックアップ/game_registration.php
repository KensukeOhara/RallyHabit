<?php
    //取得情報
    $player_id1 = $_POST['player_id1']; //選手ID1
    $player1 = $_POST['player1']; //選手1の名前
    $game_title = $_POST['game_title'];//試合名
    $player2 = $_POST['player2']; //選手2の名前
    $player2_affiliation = $_POST['player2_affiliation']; //選手2の所属
    $dominant = $_POST['dominant']; //選手2の利き手
    $service_flag = $_POST['service_flag']; //サービス権
    $rally_array = htmlspecialchars($_POST['rally_array'], ENT_QUOTES, 'UTF-8'); //ラリー
    $token = $_POST['token']; //token
    $user_id = $_POST['user_id']; //登録のユーザID

    //接続用関数の呼び出し
    require_once(__DIR__.'/functions.php');
    //DBへの接続
    $dbh = connectDB();
    //データベースの接続確認
    if (!$dbh) {  //接続できていない場合
        echo 'DBに接続できていません．';
        return;
    }

    //player2が登録されていなかったら登録．選手IDを返す
    $player_id2 = registrationPlayer($dbh, $user_id, $player2, $player2_affiliation, $dominant);

    $service_flag_num = 0;
    if ($service_flag != TRUE) {
        $service_flag_num = 1;
    }
    //ラリーの登録
    $sql = 'INSERT INTO `game_tb`(`game_title`, `player_id1`, `player_id2`, `token`, `service_flag`, `courses`) VALUES ("'.$game_title .'", "' . $player_id1. '", "' . $player_id2 . '", "'. $token . '", "'. $service_flag_num .'", "'. $rally_array .'")';
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

    //player2が登録されていなかったら登録．選手IDを返す
    function registrationPlayer($dbh, $user_id, $player2, $player2_affiliation, $dominant) {
        //player2が登録されているか確認．
        $sql = 'SELECT * FROM `player_tb` WHERE `name`="' . $player2 . '" AND `affiliation`="' . $player2_affiliation . '" AND `dominant_hand`=' . $dominant;
        $sth = $dbh->query($sql);
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) { //存在していなかったら登録
            $sql = 'INSERT INTO `player_tb` (`user_id`, `name`, `affiliation`, `dominant_hand`) VALUES ("'. $user_id . '","' . $player2 .'", "'. $player2_affiliation . '", "'. $dominant . '")';
            $dbh->exec($sql); //SQLの実行
            return $dbh->lastInsertId();
        }

        return $result['id'];

    }


?>