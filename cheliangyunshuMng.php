<?php
session_start();
require_once('config.php');
if(!isset($_SESSION['userid'])){
    echo json_encode(array('res'=>'Error','msg'=>'没有登录或已超时!'));
    return;
}
$act=addslashes($_POST['act']);
if(!($_SESSION['permission']&0x8000)){
    echo json_encode(array('res'=>'Error','msg'=>'无权使用人员管理功能!'));
    return;
}

if($act=='shijian')
	shijian();
if($act=='chengyunren')
	chengyunren();
if($act=='Fenyexianshi')
	fenyexianshi();


function shijian(){
	$nian=date("Y.m");
	$shijian1=$nian.".01";
	$shijian2=date("Y.m.d");
	$res=array('shijian1'=>$shijian1,'shijian2'=>$shijian2);
    echo json_encode($res);
    exit();
}

function chengyunren(){
	$dbh=dbconnect();
	$sql="select id,XM from chengyunren";
	foreach($dbh->query($sql) as $r){
		$str .= "<option value='{$r['id']}'>{$r['XM']}</option>";
	}
	echo $str;
	exit();
}

function fenyexianshi(){
	$dbh=dbconnect();
	$shij1=addslashes($_POST['shij1'])." 00:00:00";
	$shij2=addslashes($_POST['shij2'])." 23:59:59";
	$cyrxm=addslashes($_POST['cyrxm']);
	$page=addslashes($_POST['p']);
	$nr=addslashes($_POST['nr']);
	if($nr=='rc'){
		$sqlc="select count(*) from diaoyundan where CYRID='$cyrxm' and CFSJ between '$shij1' and  '$shij2' ";
		$nc=$dbh->query($sqlc)->fetchColumn(0);
		echo json_encode(array('res'=>'Ok','rc'=>$cyrxm));
		exit(0);
	}
	$sql="select CPH,JSXM,DYDH,CFSJ from diaoyundan where CYRID='$cyrxm' and CFSJ between '$shij1' and  '$shij2' ";
	if($page<0)
		$page=0;
	$pagesize=15;
	$sql .= "limit " . ($pagesize*$page) . ",$pagesize";

	$str="<table align='center' width='930'><thead><tr><th width='150'>调运单号</th><th width='100'>车牌号</th> <th width='80'>驾驶员</th> <th width='100'>调出日期</th><th width='100'>收货日期</th><th width='150'>发货地</th><th width='150'>收货单位</th><th width='100'>调运数量(件)</th> </tr> </thead><tbody>";
	$g=0;
	foreach($dbh->query($sql) as $r){
		$dydh[$g]=$r['DYDH'];
		$cph[$r['DYDH']]=$r['CPH'];
		$cfsj[$r['DYDH']]=substr($r['CFSJ'],0,10);
		$jsrxm[$r['DYDH']]=$r['JSXM'];
		$g++;
	}
	for($j=0;$j<count($dydh);$j++){
		$sql="select sum(FHJS) as js from diaoyunxiangbiao where YSDH='".$dydh[$j]."' and ZT='1'";
		$js[$dydh[$j]]=$dbh->query($sql)->fetchColumn(0);
		$sql="select JSSJ from diaoyunxiangbiao where YSDH='".$dydh[$j]."' and ZT='1' limit 0,1";
		$jssj[$dydh[$j]]=substr($dbh->query($sql)->fetchColumn(0),0,10);
		$sql="select CKMC from cangku where id in (select fhckid from diaoyunxiangbiao where YSDH='".$dydh[$j]."' and ZT='1')";
		$fhckmc[$dydh[$j]]=$dbh->query($sql)->fetchColumn(0);
		$sql="select dwmc from danweidm where dwbm=(select DWBM from cangku where id in (select dhckid from diaoyunxiangbiao where YSDH='".$dydh[$j]."' and ZT='1'))";
		$dhdz[$dydh[$j]]=$dbh->query($sql)->fetchColumn(0);
	}
	for($j=0;$j<count($dydh);$j++){
		$dydh1=$dydh[$j];
		$str .= "<tr height='30'><td>{$dydh[$j]}</td><td>{$cph[$dydh1]}</td><td>{$jsrxm[$dydh1]}</td><td>{$cfsj[$dydh1]}</td><td>{$jssj[$dydh1]}</td><td>{$fhckmc[$dydh1]}</td><td>{$dhdz[$dydh1]}</td><td>{$js[$dydh1]}</td></tr>";
		$zjs +=$js[$dydh1];
	}
	$str .="<tr height='30'><td>合计</td><td></td><td></td><td></td><td></td><td></td><td></td><td>".$zjs."</td></tr>";
	$str .= "</tbody></table>{$ncstr}";
	echo $str;
	exit();
}

$res=array('res'=>'Error','msg'=>'未知原因的错误！');
echo json_encode($res);
?>
