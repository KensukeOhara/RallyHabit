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
    $_SESSION['user_id'] = 0;
  }
  //デバッグ用
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 0;
  }

  //クロスサイトリクエストフォージェリ（CSRF）対策
  $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(16));

  if(!isset($_GET['id'])) { //IDがなかったらトップに移動
    header('Location: index.php');
  }
  $id = $_GET['id'];

  $sql = 'SELECT * FROM `player_tb` WHERE `id`="' . $id . '"';
  $sth = $dbh->query($sql);
  $player_info = $sth->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>ラリー傾向</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/mycss.css">
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="./index.php">ラリー癖分析 (
      <?php
        echo $_SESSION['user_name'];
      ?>
      )</a>
    </div>
  </nav>
</header>

<div class="container">
    <div class="row">
        <div class="col-12 clearfix">
            <div class="float-left">
                <h4><?php
                echo $player_info['name'];
                ?>選手の試合登録</h4>
            </div><!-- float-left -->
            <div class="float-right">
                <a class="btn btn-outline-primary" href="result.php?id=<?php
                    echo $id;
                ?>" role="button">分析結果</a>
            </div><!-- float-right -->
        </div> <!-- col-lg-12-->
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="game_title">試合名</label>
                    <input type="text" class="form-control" name="game_title" id="game_title" placeholder="20210816インカレ2021">
                </div>
                <div class="form-group col-6">
                    <label for="opponent_name">対戦相手の氏名</label>
                    <input type="text" class="form-control" name="opponent_name" id="opponent_name" placeholder="山田太郎">
                </div><!-- form-group-->
                <div class="form-group col-6">
                    <label for="opponent_affiliation">対戦相手の所属</label>
                    <input type="text" class="form-control" name="opponent_affiliation" id="opponent_affiliation" placeholder="AA大学">
                </div><!-- form-group-->
                <div class="form-group mb-0">
                    <label>利き手</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="dominant" id="left" value="1">
                        <label class="form-check-label" for="left">左</label>
                    </div><!-- form-check -->
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="dominant" id="right" value="0" checked>
                        <label class="form-check-label" for="right">右</label>
                    </div><!-- form-check -->
                </div><!-- form-group-->
            </div><!-- form-row -->
            <hr class="mb-0">
            <div class="form-row">
                <div class="col-6">
                    <label class="mt-0 text-danger">ラリーが強打のみが対象</label>
                    <div class="form-group">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="serviceChange">サービス交代</button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="courtChange">コート交代</button>
                    </div><!-- form-group -->
                    <div class="form-group mb-0">
                        <div id="service_in_upper">サービス(<?php
                        echo $player_info['name']
                    ?>)</div>
                    </div><!-- form-group -->
                    <div class="form-group mb-0">
                        <button type="button" class="btn btn-success col-5 court upper_bt" id="court_u1" value="0">0</button>
                        <button type="button" class="btn btn-success col-5 court right_court upper_bt" id="court_u2" value="1">1</button>
                    </div><!-- form-group -->
                    <div class="row">
                        <img src="img/net.png" class="col-11">
                    </div>
                    <div class="form-group mb-0">
                        <button type="button" class="btn btn-success col-5 court bottom_bt" id="court_b2" value="1" disabled>1</button>
                        <button type="button" class="btn btn-success col-5 court right_court bottom_bt" value="0" id="court_b1" disabled>0</button>
                    </div><!-- form-group -->
                    <div class="form-group mb-0 mt-0">
                        <div id="service_in_bottom">
                        レシーブ(対戦相手)
                        </div>
                    </div><!-- form-group -->
                    <div class="form-group mb-0" id="result">
                        0-0-0-0
                    </div>
                    <div class="text-right">
                        <button type="cancel" class="btn btn-danger" id="reset_bt" disabled>リセット</button>
                        <button type="submit" class="btn btn-primary" id="submit_bt" disabled>登録</button>
                    </div><!-- text-right -->
                </div><!-- col-7 -->
                <div class="col-6">
                    <label>登録履歴</label>
                    <table class="table" id="history_table">

                    </table>
                </div><!-- col-5 -->
            </div><!-- form-row -->
        </div><!-- col-12 -->
    </div> <!-- row -->
</div> <!-- container -->

<script>
    var target_name = "<?php echo $player_info['name']; ?>";
    var token = "<?php echo $_SESSION['token']; ?>";
    var user_id = "<?php echo $_SESSION['user_id']; ?>";
    var player_id1 = "<?php echo $id; ?>";
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="./js/bootstrap.min.js"></script>
<script src="./js/myjs.js"></script>
</body>
</html>