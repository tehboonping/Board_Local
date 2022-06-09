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

if(isset($_GET['commentid'])) { $_SESSION['commentid'] = $_GET['commentid']; }

$commentid = $_GET['commentid'];
if(!$commentid) { $commentid = $_SESSION['commentid']; }
if(!$commentid) { echo "IDが存在しません！"; }

$name = $_SESSION['username'];

$data = $mysqli->query("SELECT * FROM datas WHERE id = $commentid");
if(!$data) { echo "Error select from table datas!"; }

if(isset($_POST['message']))
{
	$comment_name = $_POST['name'];
	$comment_message = $_POST['message'];
	$comment_accid = NULL;
	if(isset($_SESSION['accountid'])) { $comment_accid = $_SESSION['accountid']; }

	$insert = $mysqli->query("INSERT INTO comments(post_id,name,accountid,comment,image,commenttime) VALUES('$commentid','$comment_name','$comment_accid','$comment_message',NULL,'$now')");
	if(!$insert) { echo "Error insert into table comments！"; }

	header('Location: comments.php');
}

if(isset($_GET['deleteid']))
{
	$delete_id = $_GET['deleteid'];
	$delete = $mysqli->query("DELETE FROM comments WHERE id = $delete_id");
	if(!$delete) { echo "Error delete from table comments！"; }
}

$comment = $mysqli->query("SELECT * FROM comments WHERE post_id = $commentid ORDER BY id DESC");
if(!$comment) { echo "Error select from table comments！"; }

$imagepath = "../../images/";
$row = mysqli_fetch_array($data, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>掲示板コメント</title>
	<link href="style.css" rel="stylesheet">
</head>
<body>
	<center>
		<h1 class="title">掲示板</h1>
    	<h2>投稿</h2>
    </center>
    <div class="comment">
		<p class="commentname">名前 : <?php echo $row['name']?></p>
		<p class="commenttime">時刻 : <?php echo $row['posttime']?></p>
		<p class="info">投稿内容 : <br><?php echo $row['message']?></p>
		<?php if($row['image']) { ?>
			<div class="img_center"><img class="resize" src="<?php echo $imagepath.$row['image']; ?>"></div>
		<?php } ?>
	</div>
	<br><hr style="height: 2px; background-color: black;">

	<h3 align="center">コメント</h3>
	<div class="view">
	<?php foreach($comment as $row):?>
		<div class="comment">
			<p class="commentname">名前 : <?php echo $row['name']?>　　時刻 : <?php echo $row['commenttime']?></p>
			<p class="info">コメント : <?php echo $row['comment']?></p>
			<?php if($row['image']) { ?>
				<div class="img_center"><img class="resize" src="<?php echo $imagepath.$row['image']; ?>"></div>
			<?php } 
			if(isset($_SESSION['accountid']) && $_SESSION['accountid'] == $row['accountid']) { ?>
			<div class="display">
				<form action="comments.php" method="get" class="from">
					<button type="submit" class="button1" name="deleteid" value="<?php echo $row['id']?>">削除</button>
				</form>
			</div>
			<?php } ?>
		</div>
	<?php endforeach; ?>
	</div>

	<div class="footer_div">
	<form class="footer_form" action="comments.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="name" value="<?php echo $name; ?>">
		<textarea type="text" name="message" class="comment_textbox" required></textarea>
    	<br><center><button class="button1" type="submit">コメントする</button>
    	<button type="button" class="button1" onclick="location.href='users.php'">戻る</button></center>
	</form>
	</div>
</body>
</html>