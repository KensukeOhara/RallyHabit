<?php
	//セッションの生成
	session_start();

	//接続用関数の呼び出し
	require_once(__DIR__ . '/functions.php');

	// DBへの接続
	$dbh = connectDB();

	if ($dbh) {
		// データベースへの問い合わせSQL文 (文字列)
		$sql = 'SELECT `id`,`name`, `password`, `affiliation`, `admin_flag` FROM `user_tb`
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


	// ログイン成功
	$login = 'OK';
	// 表示用ユーザ名をセッション変数に保存
	$_SESSION['id'] =  null;
	$_SESSION['user_name'] = null;
	$_SESSION['user_password'] = null;
	$_SESSION['affiliation'] = null;
	$_SESSION['admin_flag'] = null;


	$shh = null; //データの消去
	$dbh = null; //DBを閉じる



	header('Location: menu.php');


	


	
?>
<br>
