<?php

    //session変数を使えるようにする
    session_start();
    
    //DB接続
    require('dbconnect.php');
    
    //feed_idを取得
    $follower_id=$_GET['follower_id'];

    //feed_idを取得
    $feed_id=$_GET['feed_id'];

    //SQL文作成　(DELETE文)
    $sql="DELETE FROM `followers` WHERE `user_id` = ? AND `follower_id` = ?";

    //SQL実行
    $data = array($_SESSION['id'],$follower_id);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //一覧に戻る
    header("Location: profile.php?user_id=".$follower_id);

?>