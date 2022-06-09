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

$accid = $_SESSION['accountid'];

$name = $_POST["name"];
if($accid) { $name = $_SESSION['username']; }
$comment = $_POST["comment"];
$filename = $_FILES['image']['name'];

$posttime = date("Y-m-d H:i:s");

$imagepath = "../../images/";

if(!empty($comment))
{
	if($filename)
	{
		list($file_name, $file_type) = explode(".", $filename);

		$ran = (string)random_int(0, 99999);
		$dateformat = date("Ymdhis");
		$hash = $name.$dateformat.$ran;
		$special = hash('sha1', $hash);

		$filepath = $imagepath.$special.".".$file_type;
		$filename = $special.".".$file_type;
		
		if(!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) { echo "アップロード失敗"; }
	}
	else { $filename = NULL; }

	if($accid)
	{
		$data = $mysqli->query("INSERT INTO datas(name,message,posttime,accountid,image) VALUES('$name','$comment','$posttime','$accid','$filename')");
	}
	else
	{
		$data = $mysqli->query("INSERT INTO datas(name,message,posttime,image) VALUES('(G)$name','$comment','$posttime','$filename')");
	}
}

$redis = new Redis();
$redis->connect('redis',6379);

$datacount = 0;
$cacheIsExist = false;

$count = $mysqli->query("SELECT COUNT(id) FROM datas");
$rows = mysqli_fetch_array($count, MYSQLI_NUM);

$redisdata = $redis->hGetALL('datas1');
if(empty($redisdata) || $redis->dbsize() !== $rows[0])
{
	if(!empty($redisdata))
	{
		$count = 1;
		$bool = $redis->del('datas'.$count);
		while($bool > 0)
		{
			$count++;
			$bool = $redis->del('datas'.$count);
		}
	}

	$data = $mysqli->query("SELECT * FROM datas ORDER BY posttime DESC");
	if(!$data)
	{
		echo "データテーブルが存在しない。";
	}

	foreach($data as $row)
	{
		$cache = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'message' => $row['message'],
			'posttime' => $row['posttime'],
			'accountid' => $row['accountid'],
			'image' => $row['image']
		);

		$datacount++;
		$redis->hMSet('datas'.$datacount, $cache);
	}
	$cacheIsExist = false;
}
else
{
	$cacheIsExist = true;
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	header('Location: users.php');
	exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>掲示板<?php if(!$accid) { echo "(ゲスト)"; } ?></title>
	<link href="style.css" rel="stylesheet">
</head>
<body>
	<button onclick="location.href='../index.php'" class="return">←ログイン画面に戻る</button>
	<center>
	<h1 class="title">掲示板<?php if(!$accid) { echo "(ゲスト)"; } ?></h1>
	<section>
		<h2>投稿</h2>
		<form action="users.php" method="post" enctype="multipart/form-data">
			<?php if($accid) { ?>
			<p name="name" class="namebox" id="names">名前：<?php echo $_SESSION['username']?></p><br>
		<?php } else { ?>
			名前：<input type="text" name="name" class="namebox" id="names" required><br>
		<?php } ?>
			投稿内容：<button type="submit" id="send" class="button0">投稿</button><br>
			<textarea type="text" name="comment" class="textbox" id="messages" required></textarea><br>
			画像：<input type="file" name="image" accept="image/*">
		</form>
	</section>
	</center>
		<br><hr style="height: 2px; background-color: black;">
		<h2 align="center">投稿内容一覧</h2>

		<?php $datas = array();
		if($cacheIsExist) { ?>
		<?php for($i = 1;$i <= $datacount; $i++) {

			$redisdata = $redis->hGetALL('datas'.$i); 
			array_push($datas, $redisdata);
			}
		} else {
			foreach($data as $row)
			{
				array_push($datas, $row);
			}
		} 

		foreach($datas as $r): ?>
			<div class="comment">
				<p class="commentname">名前 : <?php echo $r['name'];?></p>
				<p class="commenttime">時刻 : <?php echo $r['posttime'];?></p>
				<p class="info">投稿内容 : <br><?php echo $r['message'];?></p>
				<?php if($r['image']) { ?>
				<div class="img_center"><img class="resize" src="<?php echo $imagepath.$r['image']; ?>"></div>
				<?php } ?>

				<div class="display">
				<?php if($_SESSION['accountid']) { ?>
					<form action="comments.php" method="get" class="from">
						<button type="submit" class="button1" name="commentid" value="<?php echo $r['id']?>">コメント</button>
					</form>
				<?php } ?>
				<?php if($_SESSION['accountid'] AND ($_SESSION['Developer'] === $r['lv'] OR $_SESSION['accountid'] === $r['accountid'])) { ?>
					<form action="editing.php" method="get" class="from">
						<input type="hidden" name="editid" value="<?php echo $r['id']?>">
						<button type="submit" class="button1">編集</button>
					</form>
					<form action="delete.php" method="get" class="from">
						<input type="hidden" name="deleteid" value="<?php echo $r['id']?>">
						<button type="submit" class="button1">削除</button>
					</form>
					<?php if($r['image']) { ?>
					<form action="imagedelete.php" method="get" class="from">
						<button type="submit" name="imageid" class="button1" value="<?php echo $r['id']?>">画像削除</button>
					</form>
					<?php } ?>
				<?php } ?>
				</div>
			</div>
		<?php endforeach; ?>
</body>
</html>