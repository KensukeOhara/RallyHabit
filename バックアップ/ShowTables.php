<?php
//接続用関数の呼び出し
require_once(__DIR__. '/functions.php');
$dbh = connectDb();
$sql = 'SHOW TABLES';		//Web競卓のデータベースの内容表示
$sth = $dbh->query($sql); 	//SQLの実行
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>データベースの表示</title>
		
	</head>
	<body>
		<?php
		// データの取り出し
		while ($row = $sth->fetch()) {
			echo "<pre>";
			print_r($row);
			echo "</pre>";
		}
		?>
		
	</body>
</html>
