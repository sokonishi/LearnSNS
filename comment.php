<?php

    //echo "<pre>";
    //var_dump($_POST);
    //echo "<pre>";
    //session変数を使えるようにする
    session_start();
    
    //DB接続
    require('dbconnect.php');
    
    //feed_idを取得
    $login_user_id = $_SESSION['id'];
    $comment = $_POST['write_comment'];
    $feed_id=$_POST['feed_id'];

    
    //SQL文作成　(INSERT文)
    $sql="INSERT INTO `comments` (`comment`, `user_id`, `feed_id`, `created`) VALUES (?, ?, ?,now())";
    
    //SQL実行
    $data = array($comment,$login_user_id,$feed_id);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    // feedsテーブルにcommentのカウントをUpdateする
    $update_sql = "UPDATE `feeds` SET `comment_count` = `comment_count`+1 WHERE `id`=?";

    $update_data = array($feed_id);

    //SQL文実行
    $update_stmt = $dbh->prepare($update_sql);
    $update_stmt->execute($update_data);

    //一覧に戻る
    header("Location: timeline.php");

?>