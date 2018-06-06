<?php
    session_start();

    //DB接続
    require('dbconnect.php');

    //ユーザーの一覧を表示するため取得する

  
    //SQL文作成
    $sql = 'SELECT * FROM `users` WHERE 1';

    //SELECT COUNT(feed) FROM `feeds` WHERE `user_id`=20

    //SQL文実行
    $data = array();
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //繰り返し文の中でフェッチ(配列の保存)
    $users = array();

    while (true) {
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record == false) {
            break;
        }
        //つぶやき数を取得するSQL文を作成
        //COUNT(*)をfeed_cntと定義し直している
        $feed_sql = "SELECT COUNT(*) AS `feed_cnt` FROM `feeds` WHERE `user_id`=?";

        //今回は$record["id"]はusersテーブル、timelineではfeedsテーブル
        $feed_data = array($record["id"]);

        //SQL文を実行
        $feed_stmt = $dbh->prepare($feed_sql);
        $feed_stmt->execute($feed_data);

        //つぶやき数を取得するSQL文を作成
        $feed = $feed_stmt->fetch(PDO::FETCH_ASSOC);
        //$feed = array("feed_cnt"=>5);
        $record["feed_cnt"] = $feed["feed_cnt"];
        //いいね取り消し

        //
        $users[] = $record;

      }
    //データ保存した配列を表示で使用する
      //echo '<pre>';
      //var_dump($users);
      //echo '<pre>';


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
<body style="margin-top: 60px; background: #E4E6EB;">
    <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li><a href="timeline.php">タイムライン</a></li>
          <li class="active"><a href="#">ユーザー一覧</a></li>
        </ul>
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="" width="18" class="img-circle">test <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <?php foreach($users as $user){?>
    <div class="row">
      <div class="col-xs-12">

          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $user['img_name']; ?>" width="80">
              </div>
              <div class="col-xs-11">
                <a href="profile.php?user_id=<?php echo $user["id"] ?>"><?php echo $user['name']; ?></a><br>
                <a href="#" style="color: #7F7F7F;"><?php echo $user['created']; ?></a>
              </div>
            </div>
            
            <div class="row feed_sub">
              <div class="col-xs-12">
                <span class="comment_count">つぶやき数 : <?php echo $user["feed_cnt"]; ?></span>
              </div>
            </div>
          </div><!-- thumbnail -->
      </div><!-- class="col-xs-12" -->
    </div><!-- class="row" -->
    <?php } ?>
  </div><!-- class="cotainer" -->
</body>
</html>