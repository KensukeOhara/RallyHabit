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
  $registration_flag = postRegistration($dbh);

  function postRegistration($dbh) {
    //POSTされた場合 (登録)
    if (!(isset($_POST['name']) && isset($_POST['affiliation']) && isset($_POST['dominant']))) {
      return true;
    }

    $sql = 'SELECT * FROM `player_tb` WHERE `name`="' . $_POST['name'] . '" AND `affiliation`="' . $_POST['affiliation'] . '" AND `dominant_hand`=' . $_POST['dominant'];
    $sth = $dbh->query($sql);
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!empty($result)) {
      return false;
    }

    $sql = 'INSERT INTO `player_tb` (`name`, `affiliation`, `dominant_hand`) VALUES ("' . htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') . '","' . htmlspecialchars($_POST['affiliation'], ENT_QUOTES, 'UTF-8') . '","'. htmlspecialchars($_POST['dominant'], ENT_QUOTES, 'UTF-8') . '")';
    $dbh->exec($sql); //SQLの実行
    return true;
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
      ?>
      )
    </a>
    </div>
  </nav>
</header>
<div class="container">
  <div class="row">
    <?php
      if($registration_flag==false) {
        echo '<div class="text-danger">登録済み選手です</div>';
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
      <form action="./" method="POST">
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
</div> <!-- container -->


<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="./js/bootstrap.min.js"></script>
</body>
</html>