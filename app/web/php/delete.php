<?php
session_start();

date_default_timezone_set("Asia/Tokyo");
$now = date("Y-m-d H:i:s");

$host = "mysql";
$user = "root";
$password = "pass";
$database = "boarddata";

$mysqli = new mysqli($host, $user, $password, $database);
if($mysqli->connect_errno)
{
	echo "DB接続失敗". $mysqli->connect_error;
}

$maintenance = $mysqli->query("SELECT * FROM maintenances WHERE starttime <= '$now' AND endtime >= '$now'");
if($maintenance)
{ 
	$mdata = mysqli_fetch_array($maintenance, MYSQLI_ASSOC);
	$enable = $mdata['enable'];
}

if($enable)
{
	header('Location: ../index.php');
	exit;
}

$imagepath = "../../images/";

$id = $_GET["deleteid"];
if(!$id)
{
	echo "IDが存在しません";
}

$redis = new Redis();
$redis->connect('redis',6379);

for($i = 1;$i <= $redis->dbsize(); $i++)
{
	$redisdata = $redis->hGetALL('datas'.$i);
	if($redisdata['id'] === $id)
	{
		$redis->del('datas'.$i);
		break;
	}
}

$image = $mysqli->query("SELECT * FROM datas WHERE id=$id AND image IS NOT NULL");

if($image)
{
	foreach($image as $row)
	{
		$filename = $row['image'];
		$filepath = $imagepath.$filename;

		if($filename && file_exists($filepath))
		{
			if(!unlink($filepath)){ echo "削除失敗"; }
		}
	}
}

$delete = $mysqli->query("DELETE FROM datas WHERE id = $id");
if(!$delete) { echo "Error Delete From table datas!"; }
else 
{ 
	$delete_comment = $mysqli->query("DELETE FROM comments WHERE post_id = $id");
	if(!$delete_comment) { echo "Error Delete From table comments!"; }
}
?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<title>掲示板削除</title>
	<link href="style.css" rel="stylesheet">
</head>
<center>
<h1 class="title">掲示板</h1>
<section>
    <h2>削除完了</h2>
    <button class="button1" onclick="location.href='users.php'">戻る</button>
</section>
</center>