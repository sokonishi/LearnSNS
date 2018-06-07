<?php
    session_start();
    //var_dump($_SESSION);

    //echo "<br>うまくいってなかったら、header関数を止めて確認するんだよ";
    require('dbconnect.php');

    // SELECT users テーブルから　$_SESSIONに保存されているidを使って一件だけ取り出す
    $sql = 'SELECT * FROM `users` WHERE `id`=?';
    $data = array($_SESSION['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    // $signin_userに取り出したレコードを代入する
    $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 写真と名前をレコードから取り出す

    // $img_nameに写真のファイル名を代入する
    // $name に名前をechoする
    // ユーザー名をechoする
    //var_dump($signin_user);

    //echo $signin_user['name'];
    //echo $signin_user['img_name'];

    $errors = array();

    //ボタン押した時
    if (!empty($_POST)) {
      //
      $feed = $_POST['feed'];
        //
      if ($feed != '') {
        //空じゃないとき 投稿処理
        // 2.SQL文の実行
        $sql = 'INSERT INTO `feeds` SET `feed`=?, `user_id`=?, `created`=NOW()';
        $data = array($feed, $signin_user['id']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
        //終了させる
        //強制的にリクエストさせる
        header('Location: timeline.php');
        //処理を強制的に終了
        //()にメッセージを書いたら表示させる事ができる。
        exit();

      } else {
        //空の時エラー
          $errors['feed'] = 'blank';
      }
    }

    //---------pagingの処理-----------
    $page = ''; //ページ番号が入る変数
    $page_row_number = 5;
 
    if (isset($_GET['page'])){
      $page = $_GET['page'];
    }else{
      //get送信されてるページ数がない場合、1ページとみなす
      $page = 1;
    }

    //これでも同じことができる
    //if ($page < 0){
    //  $page = 1;
    //}
    // max;カンマ区切りで整列された数字の中から最大の数を返す
    $page = max($page,1);

    //データの件数から、最大ページ数を計算する
    $sql_count = "SELECT COUNT(*) AS `cnt` FROM `feeds`";

    //SQL実行
    $stmt_count = $dbh->prepare($sql_count);
    $stmt_count->execute();

    $record_cnt = $stmt_count->fetch(PDO::FETCH_ASSOC);

    //ページ数計算
    //ceil 小数点の切り上げができる関数2.1 -> 3に変更できる
    $all_page_number = ceil($record_cnt['cnt'] / $page_row_number);
    
    // 不正に大きい数字を指定された場合、最大ページ番号に変換
    //これと同じことができる関数
    //if($page > $all_page_number){
    //  $page = $all_page_number;
    //}
    //min;カンマ区切りの数字の中から最小の数値を取得する関数
    $page = min($page,$all_page_number); 

    //var_dump($record_cnt['cnt'],$page_row_number);

    //データを取得する開始番号を計算
    $start = ($page-1)*$page_row_number;
    //---------pagingの処理-----------

    //検索ボタンが押されたら、あいまい検索
    //検索ボタンが押された＝GET送信されたsearch_wordというキーデータがある
    if (isset($_GET['search_word']) == true){
      //あいまい検索用の条件用SQL
    $sql = 'SELECT `feeds`.*, `users`.`name` , `users`.`img_name` FROM `feeds` LEFT JOIN `users` ON `feeds`.`user_id` = `users`.`id` WHERE `feeds`.`feed` LIKE "%'.$_GET['search_word'].'%" ORDER BY `feeds`.`created` DESC';

    }else{
    //検索のタイミングで外からelse内に入れた
    //通常(検索ボタンを押していない)は全件取得

    // LEFT JOINで全件取得
    //複数のテーブルがある時のカラム名の書き方,カラムの一部読み出し,テーブルのリネーム,順番の指定 ORDER BY
    //テーブルの正規化、テーブルを分けて管理すること。
    $sql = "SELECT `f`.*, `u`.`name` , `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id` = `u`.`id` WHERE 1 ORDER BY `f`.`created` DESC LIMIT $start,$page_row_number";
    //WHERE 1は無条件で引き出せる　WHEREなくても良い
    //``は省くことができる。データベースで囲まれたところは``
    }

    $data = array();
    //$statementはまだ使えない
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //$statementはまだ使えない,そのためfetchする
    //executeで取得したタイミングでは使えない
    //Object型　→ Array型に変更
    //PDOでは、fetchを使用する
    //var_dump($stmt);

    //$record = $stmt->fetch(PDO::FETCH_ASSOC);

    //fetchで取得できるデータは1レコードずつ
    // 1 fetch 1 record
    // while文で全て取り出す
    //echo '<pre>';
    //var_dump($record);
    //echo '</pre>';

    // fetchするごとに次のレコードを指定する
    // 全てfetchしたらfalseが表示される
    // while文でfalseが出たら終了的な感じで書く

    // 表示用の配列を初期化
    // 空の箱を用意するイメージ
    $feeds = array(); //while文に入れてしまうと毎回初期化されるので意味ない

    while (true) {
        // $record["like_cnt"] = 77;

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        //["like_flag"]=>
        if ($record == false) {
            break;
        }
        // commentテーブルから今取得できているfeedに対してのデータを取得
        // バッククォート全角注意
        $comment_sql = "SELECT `c`.*,`u`.`name`,`u`.`img_name`  FROM `comments` AS `c` LEFT JOIN `users` AS `u`  ON `c`.`user_id` = `u`.`id` WHERE `feed_id` = ?";
    
        $comment_data = array($record["id"]);

        // sql実行
        $comment_stmt = $dbh->prepare($comment_sql);
        $comment_stmt->execute($comment_data);
        
        //コメントを格納するための変数
        $comments_array = array();

        while (true) {
          $comment_record = $comment_stmt->fetch(PDO::FETCH_ASSOC);

          if ($comment_record == false){
            break;
          }

          //取得したコメントのデータを追加代入(重要!)
          $comments_array[] = $comment_record;
        }

        //１行分の変数(連想配列)に、新しくcommentsというキーを追加し、コメント情報を代入
        $record["comments"] = $comments_array;


        //like数を取得するためのSQL文を作成
        $like_sql = "SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id`=?";

        $like_data = array($record["id"]);

        //SQL文を実行
        $like_stmt = $dbh->prepare($like_sql);
        $like_stmt->execute($like_data);

        //like数を取得するSQL文を作成
        $like = $like_stmt->fetch(PDO::FETCH_ASSOC);
        //$like = array("like_cnt"=>5);
        $record["like_cnt"] = $like["like_cnt"];
        //いいね取り消し

        $like_flag_sql = "SELECT COUNT(*) as `like_flag` FROM `likes` WHERE `user_id`=? AND `feed_id`=?";

        //SQL実行
        $like_flag_data = array($_SESSION['id'],$record["id"]);
        $like_flag_stmt = $dbh -> prepare($like_flag_sql);
        $like_flag_stmt -> execute($like_flag_data);

        //likeしてる数を取得
        $like_flag = $like_flag_stmt -> fetch(PDO::FETCH_ASSOC);

        if($like_flag["like_flag"] > 0){
          $record["like_flag"] = 1;
        } else {
          $record["like_flag"] = 0;
        }

        //いいね済みのリンクが押されたときは、配列にすでにいいね！してるものだけを代入する
        //($_GET["feed_select"])の存在確認　　($_GET["feed_select"])がlikesだったとき
        if(isset($_GET["feed_select"]) && ($_GET["feed_select"] == "likes") && ($record["like_flag"] == 1)){
          $feeds[] = $record;
        }elseif(isset($_GET["feed_select"]) && ($_GET["feed_select"] == "news")){
          //新着順が押されたとき
          $feeds[] = $record;
        }

        // feed_selectが指定されてないときは全件表示
        if(!isset($_GET["feed_select"])){
          $feeds[] = $record;
        }
        //配列への追加構文
        //二次元配列になる
        //$feeds[] = $record;→こいつはいいね済みのif文に突っ込んだ
        //取り出し方はecho $feeds['id']['feed'];
    }

        //while ($record = $stmt->fetch(PDO::FETCH_ASSOC);) {
        //
        //$feeds[] = $record;
        //取り出し方はecho $feeds['id']['feed'];
    //}

    $c = count($feeds);

    //for ($i=0 ; $i < $c ;$i++){
    //  echo $feeds[$i]['feed'];
    //  echo '<br>';
    //}

    //上のfor文と同値
    //先に作る
    foreach ($feeds as $feed){
      //$feed = $feeds[$i]
      //echo $feed['feed'];
      //echo '<br>';
    }

    if (empty($_SESSION)) {
        header("Location: signin.php");
        exit();
    }

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
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">タイムライン</a></li>
          <li><a href="user_index.php">ユーザー一覧</a></li>
        </ul>
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="18" class="img-circle"><?php echo $signin_user['name']; ?><span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="profile.php">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-xs-3">
        <ul class="nav nav-pills nav-stacked">

          <?php if (isset($_GET["feed_select"]) && ($_GET["feed_select"] == "likes")){ ?>
            <li><a href="timeline.php?feed_select=news">新着順</a></li>

            <li class="active"><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <?php } else { ?>
            <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>

            <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <?php } ?>

          <!-- <li><a href="timeline.php?feed_select=follows">フォロー</a></li> -->
        </ul>
      </div>
      <div class="col-xs-9">
        <div class="feed_form thumbnail">

          <form method="POST" action="">
            <div class="form-group">
              <!-- textareaは改行しないでしめる -->
              <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
                <?php if(isset($errors['feed']) && $errors['feed'] == 'blank') { ?>
                  <p class="alert alert-danger">何か入力してください</p>
                <?php } ?>
            </div>
            <input type="submit" value="投稿する" class="btn btn-primary">
          </form>
        </div>

        <!-- 繰り返し -->
        <?php foreach ($feeds as $feed){ ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40">
              </div>
              <div class="col-xs-11">


                <a href="profile.php?user_id=<?php echo $feed['user_id'] ?>">
                <?php echo $feed['name']; ?></a><br>


                <a href="#" style="color: #7F7F7F;"><?php echo $feed['created']; ?></a>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <span style="font-size: 24px;"><?php echo $feed['feed']; ?></span>
              </div>
            </div>
            <div class="row feed_sub">
              <div class="col-xs-12">
                <!-- <form method="POST" action="" style="display: inline;"> -->
                  <!-- <input type="hidden" name="feed_id" > -->
                    <!-- <input type="hidden" name="like" value="like"> -->
                    <!-- feed_idをGET送信することを可能にする -->
                    <!-- いいねか取り消しか -->
                <?php if ($feed["like_flag"] == 0){ ?>
                  <a href="like.php?feed_id=<?php echo $feed['id'] ?>">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！</button>
                  </a>
                <?php } else { ?>
                  <a href="unlike.php?feed_id=<?php echo $feed['id'] ?>">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-down" aria-hidden="true"></i>いいねを取り消す！</button>
                  </a>
                <?php } ?>
                <!-- </form> -->

                  <!-- いいねを表示するかしないか -->
                <?php if(!$feed["like_cnt"] == 0) { ?>
                  <span class="like_count">いいね数 : <?php echo $feed["like_cnt"]; ?></span>
                <?php } ?>

              <!---  コメント機能実装　collapseCommentに飛ぶが一意ではないのでidを追加-->
                <a href="#collapseComment<?php echo $feed["id"];?>" data-toggle="collapse" aria-expanded="false">

                  <?php if ($feed["comment_count"] == 0){ ?>
                    <span class="comment_count">コメント</span>
                  <?php }else{ ?>
                    <span class="comment_count">コメント数:<?php echo $feed["comment_count"]; ?></span>
                  <?php } ?>

                </a>
              <!---  ここまで　 -->

                <?php if ($feed["user_id"] == $_SESSION["id"]){ ?>
                <!-- feed_id=の後ろにスペース入れない -->

                  <!-- 画面遷移するときは何も入力されていなくても基本的にGET送信されている -->
                  <!-- edit.php?以降は遷移先で$feed['id']を使いたいため指定する必要がある -->
                  <a href="edit.php?feed_id=<?php echo $feed['id'] ?>" class="btn btn-success btn-xs">編集</a>
                  <!-- JSで削除確認昨日挿入 returnはfalseになる-->
                  <a onclick="return confirm('本当に消すか？');" href="delete.php?feed_id=<?php echo $feed['id'] ?>" class="btn btn-danger btn-xs">削除</a>

                <?php } ?>
              </div>
              <!-- コメントが押されたら表示される領域 -->
              <!-- <div class="collapse" id="collapseComment"> -->
              <!-- 表示の確認！ -->
              <!-- </div> -->

            <!--  コメント実装 includeは内容から変数まで引き継げる-->
              <?php include("comment_view.php"); ?>
            <!--  ここまで -->
            </div>
          </div>
        <?php } ?>
        <!-- ここまで -->

        <div aria-label="Page navigation">
          <ul class="pager">
            <!-- 押して欲しくないときはdisabledをクラスに追加 -->
            <?php if ($page == 1) { ?>
              <li class="previous disabled"><a href="#"><span aria-hidden="true">&larr;</span>Newer</a></li>
            <?php }else{ ?>
              <li class="previous"><a href="timeline.php?page=<?php echo $page - 1 ?>"><span aria-hidden="true">&larr;</span>Newer</a></li>
            <?php } ?>

            <?php if ($page == $all_page_number) {?>
              <li class="next disabled"><a href="#">Older<span aria-hidden="true">&rarr;</span></a></li>
            <?php }else{ ?>
              <li class="next"><a href="timeline.php?page=<?php echo $page + 1 ?>">Older<span aria-hidden="true">&rarr;</span></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>

