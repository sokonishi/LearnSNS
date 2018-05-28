<?php

    //session変数を使えるようにする
    session_start();
    
    //DB接続
    require('dbconnect.php');
    
    //feed_idを取得
    $feed_id=$_GET['feed_id'];

    
    //SQL文作成　(INSERT文)
    $sql="INSERT INTO `likes` (`user_id`, `feed_id`) VALUES (?, ?)";
    
    //SQL実行
    //$_SESSION['id']でなぜuser_id取られるの　------> signin.php 41 行目で定義しているため。
    //$_SESSION['id']はlikeした人、$feed_idはlikeされた記事
    $data = array($_SESSION['id'],$feed_id);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //一覧に戻る
    header("Location: timeline.php");

?>