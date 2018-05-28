<?php
  require('dbconnect.php');
  //feed_idを取得
  $feed_id = $_GET["feed_id"];
  //$exfeed = $_POST['exfeed'];

  //編集したいfeeds tableのデータを取得して、入力欄に初期表示する
  //ポイント
  //書いた人の情報も表示したいので、テーブル結合を使う（timelineと同じ）
  //編集したいfeeds tabeleのデータは一件、繰り返し処理は必要ない
  //""で変数を囲んで中身を表示する方法、変数展開
  $sql = "SELECT `feeds`. * ,`users`.`name`, `users`.`img_name` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id`=`users`.`id` WHERE `feeds`.`id`=$feed_id";
  $data = array();
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  //$record = $stmt->fetch(PDO::FETCH_ASSOC);
  //今回は一つだけのデータを抽出しているため$feeds[]というように連想配列にしなくて良い
  //$feeds = $record;
  $feeds = $stmt->fetch(PDO::FETCH_ASSOC);//の方がスマート
  //HTML内にデータ表示の処理を記述

  //更新ボタンが押されたら(POST送信されたデータが存在したら)
  if (!empty($_POST)){
    //Update文でDBに保存
    //UPDATE テーブル名 SETカラム名=値　(,カラム名2=値2) WHERE 条件;
    //UPDATE `feeds` SET `feed` = 'まじでどうしよ' WHERE `feeds`.`id` = 80;
    $update_sql = "UPDATE `feeds` SET `feed` = '?' WHERE `feeds`.`id` = ?";

    //SQL文実行
    $data=array($_POST["feed"],$_GET["feed_id"]);
    $stmt = $dbh->prepare($update_sql);
    $stmt->execute($data);

    //一覧に戻る
    header('Location: timeline.php');
  }


  //自分の答え→ミス　全部データ消える
  //$sql = "UPDATE `feeds` SET `feed`=?";←WHEREがなかったから？
  //$data = array($exfeed);
  //$stmt = $dbh->prepare($sql);
  //$stmt->execute($data);
  //終了させる
  //強制的にリクエストさせる
  //header('Location: timeline.php');
  //処理を強制的に終了
  //()にメッセージを書いたら表示させる事ができる。
  //exit();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px;">
  <div class="container">
    <div class="row">
      <!-- ここにコンテンツ -->
      <div class="col-xs-4 col-xs-offset-4">
        <form class="form-group" method="post" action="timeline.php">
          <img src="user_profile_img/<?php echo $feeds['img_name'] ?>" width="60">
          <?php echo $feeds['name'] ?><br>
          <?php echo $feeds['created'] ?><br>
          <textarea name="feed" class="form-control" ><?php echo $feeds['feed'] ?></textarea>
          <input type="submit" value="更新" class="btn btn-warning btn-xs">
        </form>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>