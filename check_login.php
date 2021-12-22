<?php
	//セッションの生成
	session_start();

	//接続用関数の呼び出し
	require_once(__DIR__ . '/functions.php');

	if(!(isset ($_POST['user']) && isset($_POST['pass']))) {

		header('Location: login.html');
	}
	// ユーザー名／パスワード
	$user = htmlspecialchars($_POST['user'], ENT_QUOTES);
	$pass = htmlspecialchars($_POST['pass'], ENT_QUOTES);
	// DBへの接続
	$dbh = connectDB();

	if ($dbh) {
		// データベースへの問い合わせSQL文 (文字列)
		$sql = 'SELECT `id`,`name`, `password`, `affiliation`, `admin_flag`, `group_flag`, `invite_flag` FROM `user_tb`
			WHERE `name` = "' . $user . '"
			AND `password` = "' . $pass . '"';

		$sth = $dbh->query($sql); //SQLの実行
		$result = $sth->fetchALL(PDO::FETCH_ASSOC);

	}
	/*
	echo "<pre>";
	print_r($result); //デバッグ
	echo "</pre>";
	*/

	//認証
	//if (($user == 'x19000') && ($pass == 'webphp')) {
	if (count($result) == 1) { //配列数が唯一の場合
		// ログイン成功
		$login = 'OK';
		// 表示用ユーザ名をセッション変数に保存
		$_SESSION['id'] = $result[0]['id'];
		$_SESSION['user_name'] = $result[0]['name'];
		$_SESSION['user_password'] = $result[0]['password'];
		$_SESSION['affiliation'] = $result[0]['affiliation'];
		$_SESSION['admin_flag'] = $result[0]['admin_flag'];
		$_SESSION['group_flag'] = $result[0]['group_flag'];
	}else{
		//ログイン失敗
		$login = 'ERROR';
	}

	$shh = null; //データの消去
	$dbh = null; //DBを閉じる

	$_SESSION['login'] = $login;

	// 移動
	if ($login == 'OK') {
		// ログイン成功
		header('Location: menu.php');
	} else {
		// ログイン失敗：ログインフォーム画面へ
		header('Location: login.html');
	}

	


	
?>
<br>
