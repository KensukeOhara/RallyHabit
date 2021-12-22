<?php
  session_start();

  //接続用関数の呼び出し
  require_once(__DIR__.'/functions.php');
  //DBへの接続
  $dbh = connectDB();
  //データベースの接続確認
  if (!$dbh) {  //接続できていない場合
      echo 'DBに接続できていません．';
      return;
  }

  if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = "ゲスト";
  }

  $sql = 'SELECT `invite_flag` FROM `user_tb` WHERE `name`="' . $_SESSION['user_name'] . '"';
  $sth = $dbh->query($sql);
  $result = $sth->fetch();

  $_SESSION['invite_flag'] = $result['invite_flag'];

  $user_name = $_SESSION['user_name'];
  $affiliation = $_SESSION['affiliation'];

  $registration_flag = postRegistration($dbh);  
  $registration_group_flag = postRegistration_group($dbh, $user_name, $affiliation);
  $registration_invite_flag = postRegistration_invite($dbh, $user_name, $affiliation);  //0: エラー, 1: 値が空, 2: 招待メッセージ送信
  $registration_message_flag = postRegistration_message($dbh, $user_name, $affiliation);

  //選手追加のボタンが押された時の処理
  function postRegistration($dbh) {
    //POSTされた場合 (登録)
    if (!(isset($_POST['name']) && isset($_POST['affiliation']) && isset($_POST['dominant']))) {
      return true;
    }
    
    //選手一覧のデータを取り出す
    $sql = 'SELECT * FROM `player_tb` WHERE `name`="' . $_POST['name'] . '" AND `affiliation`="' . $_POST['affiliation'] . '" AND `dominant_hand`=' . $_POST['dominant'];
    $sth = $dbh->query($sql);
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!empty($result)) {
      return false;
    }
    
    //POSTした選手の追加
    $sql = 'INSERT INTO `player_tb` (`name`, `affiliation`, `dominant_hand`) VALUES ("' . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') . '","' . htmlspecialchars($_POST['affiliation'], ENT_QUOTES, 'UTF-8') . '","'. htmlspecialchars($_POST['dominant'], ENT_QUOTES, 'UTF-8') . '")';
    $dbh->exec($sql); //SQLの実行
    return true;  

  }

  //グループ作成のボタンが押された時の処理
  function postRegistration_group($dbh, $user_name, $affiliation) {

    if (!(isset($_POST['group_name']))){
      return true;
    }

    
    //どこのグループにも所属していない場合のみ実行
    if($affiliation == NULL){

      //登録したグループ名が既に存在しないかの確認
      //group_tbから全てのグループ名を取得
      $sql = 'SELECT `name` FROM `group_tb`';
      $sth = $dbh->query($sql); //SQLの実行
      //group_tbのnameのみ配列に代入
      $result = $sth->fetchAll(PDO::FETCH_COLUMN);
      //グループの数(配列数)を取得
      $Maxid = count($result);
      //グループ名が被っていないか走査
      for($i=0; $i<$Maxid; $i++){
        //被っていたらfalseを返す
        if($result[$i] == $_POST['group_name']){
          return false;
        }
      }

      //group_tbにグループの追加
      $sql = 'INSERT INTO `group_tb` (`name`) VALUES ("'  . htmlspecialchars($_POST['group_name'], ENT_QUOTES, 'UTF-8') . '")';
      $dbh->exec($sql); //SQLの実行
  
      //ユーザ情報を更新(入力したグループに所属し，そのグループの管理者にする)
      $sql = 'UPDATE `user_tb` SET `group_flag`= 2, `affiliation`="'. $_POST['group_name'] . '" WHERE `name`="' . $user_name . '"';
      $sth = $dbh->query($sql); 	//SQLの実行

      return true;

    //既にグループに所属している場合はfalseを返す
    }else{
      return false;
    }


  }

  //グループ招待のボタンが押された時の処理
  function postRegistration_invite($dbh, $user_name, $affiliation){
    
    if (!(isset($_POST['invite_name']))){
      return 1;
    }
    //存在するユーザ名のみ処理を行う
    //user_tbから全てのグループ名を取得
    $sql = 'SELECT `name` FROM `user_tb`';
    $sth = $dbh->query($sql); //SQLの実行
    //group_tbのnameのみ配列に代入
    $result = $sth->fetchAll(PDO::FETCH_COLUMN);
    //グループの数(配列数)を取得
    $Maxid = count($result);
    //グループ名が被っていないか走査
    for($i=0; $i<$Maxid; $i++){
      //被っていたら2を返す
      if($result[$i] == $_POST['invite_name']){

        $sql = 'INSERT INTO `message_tb` (`sender`, `receiver`, `sender_affiliation`) VALUES ("' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . '","' . htmlspecialchars($_POST['invite_name'], ENT_QUOTES, 'UTF-8') . '","'. htmlspecialchars($affiliation, ENT_QUOTES, 'UTF-8') . '")';
        $dbh->exec($sql); //SQLの実行

        $sql = 'UPDATE `user_tb` SET `invite_flag`= 1 WHERE `name`="' . $_POST['invite_name'] . '"';
        $sth = $dbh->query($sql); 	//SQLの実行
        return 2;
      }
    }
    return 0;
  }


  function postRegistration_message($dbh, $user_name, $affiliation){
    $sql = 'SELECT `invite_flag` FROM `user_tb` WHERE `name`="' . $user_name . '"';
    $sth = $dbh->query($sql); 	//SQLの実行
    $result = $sth ->fetch();

    //ボタンが押されていない場合はnullにする
      if (!(isset($_POST['invite_button']))){
        return null;
      }
      //承諾ボタンが押された場合の処理
      if($_POST['invite_button'] == 1){

        //招待者のグループ名を持ってくる
        $sql = 'SELECT `sender_affiliation` FROM `message_tb` WHERE `receiver`="' . $user_name . '"';
        $sth = $dbh->query($sql);
        $result = $sth ->fetch();

        //group_flagを1(グループメンバー)，invite_flagを0, 所属を招待者のグループにする
        $sql = 'UPDATE `user_tb` SET `group_flag` = 1, `invite_flag`= 0, `affiliation`="' . $result['sender_affiliation'] . '"WHERE `name`="' . $user_name . '"';
        $sth = $dbh->query($sql); 	//SQLの実行
        return true;

      //拒否ボタンが押された場合の処理
      }else if($_POST['invite_button'] == 0){
        $sql = 'UPDATE `user_tb` SET `invite_flag`= 0 WHERE `name`="' . $user_name . '"';
        $sth = $dbh->query($sql);
        return false;
      }
  }


  //選手表示
  function showPlayers($dbh) {
    $sql = 'SELECT * FROM `player_tb`';
    $sth = $dbh->query($sql);
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach($results as $result) {
      echo '<a href="result.php?id='. $result['id']. '" class="list-group-item list-group-item-action">'. $result['name'] . ' ('. $result['affiliation'] . '，';
      if ($result['dominant_hand']==0) {
        echo '右';
      }else{
        echo '左';
      }
      echo ')</a>';
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>ラリー癖分析</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="./">ラリー癖分析 (
      <?php
        echo $_SESSION['user_name'];
        if($_SESSION['user_name'] != 'ゲスト' && $_SESSION['admin_flag'] == 0){
          echo ", 一般ユーザ";
        }else if($_SESSION['admin_flag'] == 1){
          echo ", 管理者";
        }
      ?>
      )
    </a>
    <a href="logout.php" class = "btn btn-outline-primary">ログアウト</a>
    </div>
  </nav>
</header>
<div class="container">
  <?php
    if($registration_group_flag == false){
      echo "<div class = 'text-danger'>既にグループに所属しているか，同じグループ名が既に存在しています．</div>";
    }
    if($registration_invite_flag == 0){
      echo "<div class = 'text-danger'>ユーザ名が存在していません．</div>";
    }else if($registration_invite_flag == 2){
      echo "<div class = 'text-success'>" . $_POST['invite_name'] . "に招待メッセージを送信しました．</div>";
    }
  ?>
  <div class="row">
    <?php
      if($registration_flag==false) {
        echo '<div class="text-danger">登録済み選手です</div>';
      }
      if($registration_message_flag == true){
        echo '<div class="text-success">グループの招待を承諾しました</div>';
        $registration_message_flag = null;
      }else if($registration_message_flag == false){
        echo '<div class="text-danger">グループの招待を拒否しました</div>';
        $registration_message_flag = null;

      }
      if($_SESSION['invite_flag'] == 1){


        $sql = 'SELECT `sender`, `sender_affiliation` FROM `message_tb` LIMIT 1';
        $sth = $dbh->query($sql);
        $result = $sth ->fetch();

        echo '<div class="col-lg-12">
                <div class="page-header">
                <br>
                  <div style = "text-align: center; font-size: 20px; font-weight: bold;">グループから招待が届いています．承諾しますか？</div>
                  <div style = "text-align: center; font-size: 15px; font-weight: bold;">招待者:' . $result['sender'] . '</div>';
            echo '<div style = "text-align: center; font-size: 15px; font-weight: bold;">招待グループ:' . $result['sender_affiliation'] . '</div>';
            echo '<form action = "menu.php" method = "POST">
                    <div style = "text-align: center">
                      <button type="submit" name="invite_button" class="btn btn-primary" value = "1">承諾</button>
                      <button type="submit" name="invite_button" class="btn btn-danger" value = "0">拒否</button>
                  </form>
                    </div>
                </div> <!-- page-header -->
              </div> <!-- col-lg-12-->';
    } 
    ?>
    <div class="col-lg-12">
      <div class="page-header">
      <h3>選手一覧</h3>
      </div> <!-- page-header -->
    </div> <!-- col-lg-12-->
  </div> <!-- row -->
  <div class="row">
    <div class="col-lg-12">
      <div class="list-group">
      <?php
        showPlayers($dbh); //選手表示
      ?>
      </div> <!-- list-group -->
    </div> <!-- col-lg-12-->
  </div> <!-- row -->
  <hr>
  <div class="row">
    <div class="col-sm-12">
      <div class="page-header">
      <h3>選手追加</h3>
      </div> <!-- page-header -->
      <form action="menu.php" method="POST">
        <div class="form-row">
          <div class="form-group col-6">
            <label for="nameInput">氏名</label>
            <input type="text" class="form-control" name="name" id="nameInput"
            placeholder="氏名の入力">
          </div><!-- form-group-->
          <div class="form-group col-6">
            <label for="affiliationInput">所属</label>
            <input type="text" class="form-control" name="affiliation" id="affiliationInput"
            placeholder="所属の入力">
          </div><!-- form-group-->
          <div class="form-group">
            <label>利き手</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="dominant" id="left" value="1">
              <label class="form-check-label" for="left">左</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="dominant" id="right" value="0" checked>
              <label class="form-check-label" for="right">右</label>
            </div><!-- form-check -->
          </div><!-- form-group-->
        </div><!-- form-row -->
        <button type="submit" class="btn btn-primary">登録</button>
      </form>
    </div> <!-- col-lg-12-->
  </div> <!-- row -->
  <hr>
  <div class="row">
    <div class="col-sm-12">
     <div class="page-header">
       <h3>分析グループ作成</h3>
     </div> <!-- page-header -->
     <form action ="menu.php" method = "POST">
      <div class = "form-row">
       <div class="form-group col-6">
          <label for="nameInput">グループ名</label>
          <input type="text" class="form-control" name="group_name" id="groupInput"
          placeholder="登録グループ名の入力">
        </div><!-- form-group-->
      </div> <!--  form-row -->
      <button type="submit" class="btn btn-primary">登録</button>
     </form>
    </div> <!-- col-lg-12-->
  </div> <!-- row -->
  <hr>
  <?php
  if($_SESSION['group_flag'] == 2){
     echo '<div class="row">';
       echo '<div class="col-sm-12">';
        echo '<div class="page-header">';
          echo '<h3>分析グループ招待</h3>';
        echo '</div> <!-- page-header -->';
          echo '<form action ="menu.php" method = "POST">';
            echo '<div class = "form-row">';
              echo '<div class="form-group col-6">';
                echo '<label for="nameInput">ユーザ名</label>';
                  echo '<input type="text" class="form-control" name="invite_name" id="InviteInput" placeholder="招待したいユーザ名を入力">';
              echo'</div><!-- form-group-->';
            echo '</div> <!--  form-row -->';
            echo'<button type="submit" class="btn btn-primary">招待</button>';
          echo '</form>';
      echo '</div> <!-- col-lg-12-->';
    echo '</div> <!-- row -->';
  }
?>
<br>
</div> <!-- container -->


<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="./js/bootstrap.min.js"></script>
</body>
</html>