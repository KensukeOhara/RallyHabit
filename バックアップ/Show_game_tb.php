<?php
//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');
$dbh = connectDb();
$sql = 'SHOW COLUMNS FROM `game_tb`';		//サービス癖分析のデータベースの内容表示
$sth = $dbh->query($sql); 	//SQLの実行
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>game_tbの表示</title>
		
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
		<!--
		<table border=1>
			 <tr bgcolor="#CCCCCC">
				<th>フィールド</th>
				<th>型</th>
 				<th>Null</th>
				<th>Key</th>
				<th>Default</th>
				<th>Extra</th>
			</tr>
				 <?php
				 // データの取り出し
				 while ($row = $sth->fetch()) {
					 echo '<tr>';
					 echo '<td>' . $row['Field'] . '</td>';
					 echo '<td>' . $row['Type'] . '</td>';
					 echo '<td>' . $row['Null'] . '</td>';
					 echo '<td>' . $row['Key'] . '</td>';
					 echo '<td>' . $row['Default'] . '</td>';
					 echo '<td>' . $row['Extra'] . '</td>';
					 echo '</tr>';
				 }
				 ?>
                 </table>
			 -->
	</body>
</html>
