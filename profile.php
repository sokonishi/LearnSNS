<?php 
  session_start();
  require('dbconnect.php');

  if (isset($_GET['user_id'])){
  
  // https://localhost/LernSNS/profile.php?user_id=13
  //上のようなURLでアクセスされたときは$_GET['user_id']に13が代入される
  $user_id = $_GET['user_id'];

  }else{
  //https://localhost/LernSNS/profile.php
  //上のようなURLでアクセスされたときは$_GET['user_id']が存在しないので、$_SESSION['id']を使用する
  $user_id = $_SESSION['id'];
  }

  $sql = 'SELECT * FROM `users` WHERE `id`=?';
  $data = array($user_id);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  $profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

  //---------自分の答え------------
  //$feed_id = $_GET['feed_id'];
  //var_dump($feed_id);

  //$sql = 'SELECT `feeds`.*, `users`.`name` , `users`.`img_name` FROM `//feeds` LEFT JOIN `users` ON `feeds`.`user_id` = `users`.`id` WHERE `//feeds`.`id` = ?';

  //$data = array($feed_id);
  //$stmt = $dbh->prepare($sql);
  //$stmt->execute($data);

  //$record = $stmt->fetch(PDO::FETCH_ASSOC);
  ////var_dump($record);

  //------ここからは必要------------
  $sql = 'SELECT * FROM `users` WHERE `id`=?';
  $data = array($_SESSION['id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
//
  // $signin_userに取り出したレコードを代入する
  $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);
  //var_dump($signin_user);
  //----------ここまで--------------

  //-----following一覧の作り方------
  //今表示されている、プロフィールイメージの人がフォローしている
  //フォロワーの情報をusersテーブルから引っ張りたいから`fw`.`follower_id` = `u`.`id`
  $following_sql = 'SELECT `fw`.* ,`u`.`name`,`u`.`img_name`,`u`.`created` FROM `followers` AS `fw` LEFT JOIN `users` AS `u` ON `fw`.`follower_id` = `u`.`id` WHERE `user_id`=?';
  $following_data = array($user_id);
  $following_stmt = $dbh->prepare($following_sql);
  $following_stmt->execute($following_data);

  $following = array();

  while (true) {
    $following_record = $following_stmt->fetch(PDO::FETCH_ASSOC);

    if ($following_record == false){
      break;
    } 
    $following[] = $following_record;
  }
  //var_dump($following);

  //-------follower一覧の取得-------
  $followers_sql = 'SELECT `fw`.* ,`u`.`name`,`u`.`img_name`,`u`.`created` FROM `followers` AS `fw` LEFT JOIN `users` AS `u` ON `fw`.`user_id` = `u`.`id` WHERE `follower_id`=?';
  $followers_data = array($user_id);
  $followers_stmt = $dbh->prepare($followers_sql);
  $followers_stmt->execute($followers_data);

  $followers = array();

  $follow_flag = 0;
  //ログインユーザーが今見てるプロフィールページの人をフォローしたら1、フォローしてなかったら0

  while (true) {
    $followers_record = $followers_stmt->fetch(PDO::FETCH_ASSOC);

    if ($followers_record == false){
      break;
    } 

    //フォロワーの中に、ログインしている人がいるかをチェック
    if ($followers_record['user_id'] == $_SESSION['id']){
      $follow_flag = 1;
    }

    $followers[] = $followers_record;
  }
  //echo '<pre>';
  //var_dump($followers);
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
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $signin_user['img_name'] ?>" width="18" class="img-circle"><?php echo $signin_user['name'] ?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="profile.php">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-xs-3 text-center">
        <img src="user_profile_img/<?php echo $profile_user['img_name'] ?>" class="img-thumbnail" />
        <h2><?php echo $profile_user['name'] ?></h2>

        <?php if ($user_id != $_SESSION["id"]){ ?>

          <?php if ($follow_flag == 0) {?>
            <a href="follow.php?follower_id=<?php echo $profile_user['id'] ?>">
            <button class="btn btn-default btn-block">フォローする</button>
            </a>
          <?php } else { ?>
            <a href="#">
            <button class="btn btn-default btn-block">フォロー解除</button>
            </a>
          <?php } ?>
        <?php } ?>

      </div>

      <div class="col-xs-9">
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#tab1" data-toggle="tab">Followers</a>
          </li>
          <li>
            <a href="#tab2" data-toggle="tab">Following</a>
          </li>
        </ul>
        <!--タブの中身-->
        <div class="tab-content">
          <div id="tab1" class="tab-pane fade in active">
            <?php foreach ($followers as $follower_user){?>
            <div class="thumbnail">
              <div class="row">
                <div class="col-xs-2">
                  <img src="user_profile_img/<?php echo $follower_user['img_name'] ?>" width="80">
                </div>
                <div class="col-xs-10">
                  <a href="profile.php?user_id=<?php echo $follower_user['user_id'] ?>">
                  <?php echo $follower_user['name'] ?></a><br>
                  <a href="#" style="color: #7F7F7F;"><?php echo $follower_user['created'] ?></a>
                </div>
              </div>
            </div>
            <?php } ?>
            
          </div>
          <!-- following -->
          <div id="tab2" class="tab-pane fade">

            <?php foreach ($following as $follow){ ?>
              <div class="thumbnail">
                <div class="row">
                  <div class="col-xs-2">
                    <img src="user_profile_img/<?php echo $follow['img_name'] ?>" width="80">
                  </div>
                  <div class="col-xs-10">
                    <a href="profile.php?user_id=<?php echo $follow['follower_id'] ?>">
                    <?php echo $follow['name'] ?></a><br>
                    <a href="#" style="color: #7F7F7F;"><?php echo $follow['created'] ?></a>
                  </div>
                </div>
              </div>
            <?php } ?>

          </div>
          <!-- ここまでfollowing -->
        </div>

      </div><!-- class="col-xs-12" -->

    </div><!-- class="row" -->
  </div><!-- class="cotainer" -->
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>