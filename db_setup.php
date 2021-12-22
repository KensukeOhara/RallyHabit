<?php
    //接続用関数の呼び出し
    require_once(__DIR__.'/functions.php');
    createTable(); //テーブルの生成

    //テーブルの生成
    function createTable() {
        //DBへの接続
        $dbh = connectDB();
        //データベースの接続確認
        if (!$dbh) {  //接続できていない場合
            echo 'DBに接続できていません．';
            return;
        }

        //テーブルが存在するかを確認するSQL文
        $sql = "show tables";
        $sth = $dbh->query($sql); //SQLの実行
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (0 < count($result)) {
            //データベース構築済み
            return;
        }
        //---------------------------------------------------------------------------
        //ユーザテーブル
        $sql = "CREATE TABLE IF NOT EXISTS `user_tb` ( `id` INT NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL COMMENT 'ユーザ名', `affiliation` VARCHAR(255) COMMENT '所属', PRIMARY KEY (`id`)) ENGINE = InnoDB";
        $dbh->exec($sql); //SQLの実行

        //選手テーブルの作成
        $sql = "CREATE TABLE IF NOT EXISTS `player_tb` ( `id` INT NOT NULL AUTO_INCREMENT, `user_id` INT DEFAULT 0 COMMENT 'ユーザID', `name` VARCHAR(255) NOT NULL COMMENT '選手名', `affiliation` VARCHAR(255) COMMENT '所属', `dominant_hand` BOOLEAN DEFAULT 0 COMMENT '利き手', PRIMARY KEY (`id`)) ENGINE = InnoDB";
        $dbh->exec($sql); //SQLの実行

        //試合テーブル
        $sql = "CREATE TABLE IF NOT EXISTS `game_tb` ( `id` INT NOT NULL AUTO_INCREMENT, `game_title` VARCHAR(255) COMMENT '試合名', `player_id1` INT NOT NULL COMMENT '選手ID1', `player_id2` INT NOT NULL COMMENT '選手ID2', `token` VARCHAR(255) COMMENT 'トークン(メモ)', `service_flag` BOOLEAN DEFAULT 0 COMMENT 'サービスなら0，レシーブなら1', `courses` VARCHAR(255) NOT NULL COMMENT 'コース群', `user_id` INT DEFAULT 0 COMMENT '入力ユーザのID', `update_time` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '更新日時', PRIMARY KEY (`id`)) ENGINE = InnoDB";
        $dbh->exec($sql); //SQLの実行

        $sql = 'INSERT INTO `user_tb` (`name`, `affiliation`) VALUES ("ゲスト", "ゲストグループ")';
        $dbh->exec($sql); //SQLの実行

        $sql = 'INSERT INTO `player_tb` (`name`, `affiliation`) VALUES ("白山亜美", "明徳義塾")';
        $dbh->exec($sql); //SQLの実行

        $sql = 'INSERT INTO `player_tb` (`name`, `affiliation`) VALUES ("赤江夏星", "香ヶ丘リベルテ高")';
        $dbh->exec($sql); //SQLの実行

        $sql = 'INSERT INTO `player_tb` (`name`, `affiliation`) VALUES ("大藤沙月", "四天王寺高")';
        $dbh->exec($sql); //SQLの実行


        echo '更新終了';
    }
?>