<?php
include_once('toupiao/connect.php');
$zf="活动搞疯了快圣诞节疯狂老师的欧菲光多少了活动搞疯了的欧菲光多少了";
$sql="insert into data(toupiao,indate,beizhu) values('0','2016-03-24','$zf')";
for($i=0;$i<1;$i++){
	mysql_query($sql,$conn);
}
?>