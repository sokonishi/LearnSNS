<?php

    //session変数を使えるようにする
    session_start();
    
    //DB接続
    require('dbconnect.php');
    
    //follower_idを取得(意味としてはusers,id)
    //http://localhost/LearnSNS/follow.php?follower_id=13というリンクを想定

    $follower_id=$_GET['follower_id'];

    // followボタンを押した人のid
    $user_id = $_SESSION['id'];
    
    //SQL文作成　(INSERT文)
    $sql="INSERT INTO `followers` (`id`, `user_id`, `follower_id`) VALUES (NULL,?, ?)";

    //SQL実行
    $data = array($user_id,$follower_id);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //一覧に戻る
    header("Location: profile.php?user_id=".follower_id);

?>