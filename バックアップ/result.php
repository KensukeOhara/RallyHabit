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

  if(!isset($_GET['id'])) { //IDがなかったらトップに移動
    header('Location: index.php');
  }
  $id = $_GET['id'];

  $sql = 'SELECT * FROM `player_tb` WHERE `id`="' . $id . '"';
  $sth = $dbh->query($sql);
  $player_info = $sth->fetch(PDO::FETCH_ASSOC);

  //ラリー情報の取得
  //選手情報と利き手から判定
  //まずは分析対象の選手のみ
  function getRallyInfo($dbh, $player_id, $opponent_dominant) {
    //サービス権あり
    $sql = 'SELECT `courses` FROM `game_tb` INNER JOIN `player_tb` ON `game_tb`.`player_id2`=`player_tb`.`id` WHERE `player_id1`="' . $player_id . '" AND `player_tb`.`dominant_hand`="' . $opponent_dominant . '" AND `service_flag`="0"';
    $sth = $dbh->query($sql);
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $total_courses = array(); //トータルのコース初期化
    foreach($results as $result) {
      //コース
      $courses = json_decode(html_entity_decode($result['courses']));
      $total_courses[bindec($courses[1].$courses[2])][bindec($courses[2].$courses[3])] += 1;
      /*
      echo "<pre>";
      echo var_dump($courses) . '<br>';
      echo "</pre>";
      */
      //echo (intval($courses[0])) . '<br>';
      //サービス権がある場合，2球目と3球目の情報が必要
      //$total_courses[intval($courses[1])][intval($courses[2])] += 1;
      /*
      echo $courses[1] . $courses[2] . ':' . bindec($courses[1].$courses[2]) . ", ";
      echo $courses[2] . $courses[3] . ':' . bindec($courses[2].$courses[3]) . "<br>";
      */
    }

    //サービス権なし
    $sql = 'SELECT `courses` FROM `game_tb` INNER JOIN `player_tb` ON `game_tb`.`player_id2`=`player_tb`.`id` WHERE `player_id1`="' . $player_id . '" AND `player_tb`.`dominant_hand`="' . $opponent_dominant . '" AND `service_flag`="1"';
    $sth = $dbh->query($sql);
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach($results as $result) {
      //コース
      $courses = json_decode(html_entity_decode($result['courses']));
      //echo var_dump($courses) . '<br>';
      //echo (intval($courses[0])) . '<br>';
      //サービス権がない場合，3球目と4球目の情報が必要
      $total_courses[bindec($courses[1].$courses[2])][bindec($courses[2].$courses[3])] += 1;
    }

    return $total_courses;
  }

  //プログレスバーの表示
  function showProgress($total_courses, $player1_dominant) {
    $jap_total_courses = [];
    //ソートの準備
    foreach($total_courses as $pos_key => $receive_arr) {
      foreach ($receive_arr as $receive_key => $receive_val) {
        $label = '';
        if ($player1_dominant==0) { //分析選手が右利き
          if ($pos_key % 2 == 0) { //偶数 (フォア)
            $label .= 'フォア，';
          }else{
            $label .= 'バック，';
          }
        }else{//分析選手が左利き
          if ($pos_key % 2 == 1) { //奇数 (フォア)
            $label .= 'フォア，';
          }else{
            $label .= 'バック，';
          }
        }

        //コース1 (相手選手から)
        if ($pos_key == 0 || $pos_key == 3) {
          $label .= 'クロス->';
        }else{
          $label .= 'ストレート->';
        }

        //コース1 (分析選手から)
        if ($receive_key == 0 || $receive_key == 3) {
          $label .= 'クロス';
        }else{
          $label .= 'ストレート';
        }

        $jap_total_courses[$label] = $receive_val;
        //echo $label . ': ';
        //echo $pos_key . '-' . $receive_key .': ' . $receive_val . '<br>';
      }
    }
    arsort($jap_total_courses);
    $sum = array_sum($jap_total_courses);
    foreach ($jap_total_courses as $key => $val) {
      $ratio = $val / $sum * 100;
      showHTMLProgress($key . '('.$val . ', ' .round($ratio).'%)', $ratio);
    }

    //echo var_dump($jap_total_courses);
  }
  //プログレスバーをHTMLで出力
  function showHTMLProgress($str, $ratio) {
    echo '<dt>' .$str . '</dt>';
    echo '<dd>';
    echo '  <div class="progress">';
    echo '    <div class="progress-bar bg-success" role="progressbar" style="width: ' . $ratio . '%;" aria-valuenow="' . $ratio . '" aria-valuemin="0" aria-valuemax="100"></div>';
    echo '  </div>';
    echo '</dd>';
  }

  //試合一覧の表示
  function showGameList ($dbh, $id) {
    $sql = 'SELECT DISTINCT `game_title`, `player_id1`, `player_tb`.`name`, `token` FROM `game_tb` Inner JOIN `player_tb` on `player_id2`=`player_tb`.`id` WHERE `player_id1`="' . $id . '"';
    $sth = $dbh->query($sql);
    $results = $sth->fetchAll();
    foreach ($results as $result) {
        echo '<tr>';
        echo '  <td>' . $result['game_title'] . '</td>';
        echo '  <td>' . $result['name'] . '</td>';
        echo '  <td>' . '</td>';
        echo '</tr>';
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>ラリー傾向</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
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
      <div class="float-left"><h4><?php
          echo $player_info['name'];
        ?></h4></div>
      <div class="float-right">
      <a class="btn btn-outline-primary" href="registration.php?id=<?php
        echo $id;
      ?>" role="button">試合登録</a>
      </div>
    </div> <!-- col-lg-12-->
    <div class="col-12">
      <label>右利き</label>
      <?php
        $total_courses = getRallyInfo($dbh, $id, 0);
        showProgress($total_courses, $player_info['dominant_hand']);
      ?>
    </div><!-- col-12 -->
    <div class="col-12">
      <label>左利き</label>
      <?php
        $total_courses = getRallyInfo($dbh, $id, 1);
        showProgress($total_courses, $player_info['dominant_hand']);
      ?>
    </div><!-- col-12 -->
    <hr>
    <div class="col-12">
      <label>試合一覧</label>
      <table class="table" id="game_list">
        <thead>
          <tr>
            <th scope="col">試合名</th>
            <th scope="col">対戦相手</th>
          </tr>
          </thead>
          <tbody>
        <?php
          showGameList($dbh, $id);
        ?>
          </tbody>
      </table>
    </div><!-- col-12 -->
  </div> <!-- row -->
</div> <!-- container -->


<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="./js/bootstrap.min.js"></script>
</body>
</html>