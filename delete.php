<?php
  
  //DBに接続
  require('dbconnect.php');
  
  //feed_id取得
  $feed_id = $_GET["feed_id"];

  //Deleten文
  //条件がないと全て消える
  $sql="DELETE FROM `feeds` WHERE `feeds`.`id`=?";
  
  //SQL実行
  $data=array($feed_id);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  
  //一覧に戻る
  header('Location: timeline.php');

?>