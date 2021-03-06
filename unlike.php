<?php

    //session変数を使えるようにする
    session_start();
    
    //DB接続
    require('dbconnect.php');
    
    //feed_idを取得
    $feed_id=$_GET['feed_id'];

    
    //SQL文作成　(INSERT文)
    $sql="DELETE FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?";

    //SQL実行
    //$_SESSION['id']でなぜuser_id取られるの
    //$_SESSION['id']はlikeした人、$feed_idはlikeされた記事
    $data = array($_SESSION['id'],$feed_id);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //一覧に戻る
    header("Location: timeline.php");

?>