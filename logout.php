<?php
	// セッション開始
	session_start();

	// セッションIDを破棄
	session_unset(); //セッションの初期化
	// セッションを破棄
	session_destroy();
?>
<html>
<head>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="css/bootstrap.min.css">
<title>ログアウト</title>
</head>
<body>
<div class = "container">

<h2>ログアウトしました</h2>
<hr>
<!-- ログインページに戻る -->
<a href="login.html">再度ログインの方はこちらから</a>
			<br><br>
</div>
</body>
</html>