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
if($act=='Add')
    add();
if($act=='daohuodidian')
	daohuodidian();
if($act=='Fenyexianshi')
	fenyexianshi();
if($act=='diaoyundan')
	diaoyundan();
if($act=='modifyxianshi')
	modifyxianshi();
if($act=='JSCK')
	jsck();
if($act=='DJ2')
	dj2();
if($act=='heduijianshu')
	heduijianshu();
if($act=='chakanckkc')
	chakanckkc();
if($act=='chakanckkc1')
	chakanckkc1();
if($act=='yijianrk')
	yijianrk();
//一键入库

function yijianrk(){
	$dbh=dbconnect();
	$dhdd=addslashes($_POST['dhdd']);
	$fhdd=addslashes($_POST['fhdd']);
	$dydh=addslashes($_POST['dydh']);
	$jsck1=addslashes($_POST['jsck1']);
	$jssj=date("Y-m-d H:i:s");
	$nianfen=date("Y");
	$ztms=$dhdd."入库";
	$sql="select dwbm from danweidm where dwmc='$dhdd'";
	$dhdwbm=$dbh->query($sql)->fetchColumn(0);
	if($dhdwbm==''){
		exit("到货单位编码查询错误");
	}
	$sql="select dwbm from danweidm where dwmc='$fhdd'";
	$fhdwbm=$dbh->query($sql)->fetchColumn(0);
	if($fhdwbm==''){
		exit("发货单位编码查询错误");
	}
	$dbh->query("BEGIN"); //或者mysql_query("START TRANSACTION"); 
	$sql="select DJBH,DJMC,FHJS,fhckid from diaoyunxiangbiao where YSDH='$dydh' and dhckid='0'";
	foreach($dbh->query($sql) as $r){
		if($r){
			$djbh=$r['DJBH'];
			$djmc=$r['DJMC'];
			$js=$r['FHJS'];
			$zhongL=50*$js;
			$fhckid=$r['fhckid'];
			$sqlxgck="update cangku set XKC=XKC-$js where id='$fhckid'";//修改仓库现库存1
			if($dbh->exec($sqlxgck)<>1){
				$dbh->query("ROLLBACK");   
				exit("发货仓库库存修改失败！");
			}
			$sqlxgck1="update cangku set XKC=XKC+$js where id='$jsck1'";//修改仓库现库存2
			if($dbh->exec($sqlxgck1)<>1){
				$dbh->query("ROLLBACK");   
				exit("接受仓库库存修改失败！");
			}
			$sql="select id from dengjikucun where CKID='$jsck1' and DJBH='$djbh'";
			$djkcyf=$dbh->query($sql)->fetchColumn(0);
			if($djkcyf){
				$sqlxgdjkc1="update dengjikucun set KC=KC+$js where CKID='$jsck1' and DJBH='$djbh'";//修改等级库存1
				if($dbh->exec($sqlxgdjkc1)<>1){
					$dbh->query("ROLLBACK");   
					exit("接受仓库等级库存修改失败！");
				}
			}else{
				$sql="insert into dengjikucun (CKID,DJBH,KC) values('$jsck1','$djbh','$js')";
				if($dbh->exec($sql)<>1){
					$dbh->query("ROLLBACK");   
					exit("接受仓库等级库存添加失败！");
				}
			}
			$sqlxgdjkc2="update dengjikucun set KC=KC-$js where CKID='$fhckid' and DJBH='$djbh'";//修改等级库存2
			if($dbh->exec($sqlxgdjkc2)<>1){
				$dbh->query("ROLLBACK");   
				exit("发货仓库等级库存修改失败！");
			}
			$sqltjdata="insert into data (zwjb,ywjb,indwbm,indate,frmdwbm,zhongl,js,nianfen,incangkuid,frmcangkuid) values ('$djmc','$djbh','$dhdwbm','$jssj','$fhdwbm','$zhongL','$js','$nianfen','$jsck1','$fhckid')";
			if($dbh->exec($sqltjdata)<>1){
				$dbh->query("ROLLBACK");   
				exit("data表数据添加失败！");
			}
		}
	}
	$sqlxgdyxb="update diaoyunxiangbiao set JSSJ='$jssj',SHJS=FHJS,SHZL=FHZL,dhckid='$jsck1',ZT='1',ZTMS='$ztms' where YSDH='$dydh' and dhckid='0'";//入库
	if($dbh->exec($sqlxgdyxb)){
		$dbh->query("COMMIT");   
		$res=array('res'=>'OK','msg'=>'成功入库！' ); 
	}else{
		$dbh->query("ROLLBACK");   
		$res=array('res'=>'Error','msg'=>'入库失败！' );
	}
	$dbh->query("END");
	echo json_encode($res);
	exit();
}
//入库
function add(){
    $dbh=dbconnect();
	$dhdd=addslashes($_POST['dhdd']);
	$fhdd=addslashes($_POST['fhdd']);
    $id=addslashes($_POST['id']);
	$js=addslashes($_POST['js']);
	$jsck=addslashes($_POST['jsck']);
	$js1=addslashes($_POST['js1']);
	$dj1=addslashes($_POST['dj1']);
	$caozuo=addslashes($_POST['caozuo']);
	$dj2=addslashes($_POST['dj2']);
	$jssj=date("Y-m-d H:i:s");
	$ztms=$dhdd."入库";

	$sql="select dwbm from danweidm where dwmc='$dhdd'";
    $dhdwbm=$dbh->query($sql)->fetchColumn(0);
	if($dhdwbm==''){
		exit("到货单位编码查询错误");
	}
	$sql="select dwbm from danweidm where dwmc='$fhdd'";
    $fhdwbm=$dbh->query($sql)->fetchColumn(0);
	if($fhdwbm==''){
		exit("发货单位编码查询错误");
	}
	$sql="select YSDH,FHJS,fhckid,dhdwbh from diaoyunxiangbiao where id='$id'";
    $dydh=$dbh->query($sql)->fetchColumn(0);
	$fhjs=$dbh->query($sql)->fetchColumn(1);
	$fhckid=$dbh->query($sql)->fetchColumn(2);
	$ydhdwbh=$dbh->query($sql)->fetchColumn(3);
	if($dydh==''){
		exit("运输单号查询失败！");
	}
	$sql="select DJBH from dengjibiao where DJMC='$dj1'";
    $djbh1=$dbh->query($sql)->fetchColumn(0);
	if($djbh1==''){
		exit("等级编号查询失败！");
			
	}
	$sql="select DJMC from dengjibiao where DJBH='$dj2'";
    $djmc2=$dbh->query($sql)->fetchColumn(0);
	if($djmc2==''){
		exit("等级名称查询错误");
			
	}
	if($js1)
		$beizhu=$js1."件".$dj1.$caozuo."为".$djmc2;
	else
		$beizhu="";
	$zhongl=50*$js;
	$zhongl1=50*($fhjs-$js);
	$zhongl2=50*$js1;
	$dsjs=$fhjs-$js;
	$dbh->query("BEGIN"); //或者mysql_query("START TRANSACTION");
	if($fhjs==$js){
		if($js1==""){
			$sqlxgdyxb="update diaoyunxiangbiao set JSSJ='$jssj',SHJS=FHJS,SHZL=FHZL,BZ='$beizhu',dhckid='$jsck',ZT='1',ZTMS='$ztms' where id='$id'";
			if($dbh->exec($sqlxgdyxb)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库失败1！");
			}
		}else{
			$sqlxgdyxb="update diaoyunxiangbiao set JSSJ='$jssj',SHJS=FHJS-$js1,SHZL=FHZL-(50*$js1),BZ='$beizhu',dhckid='$jsck',ZT='1',ZTMS='$ztms' where id='$id'";
			if($dbh->exec($sqlxgdyxb)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库失败2！");
			}
			$sqltjdyxb2="insert into diaoyunxiangbiao (YSDH,DJBH,DJMC,JSSJ,SHJS,SHZL,dhdwbh,dhckid,ZT,ZTMS) values ('$dydh','$dj2','$djmc2','$jssj','$js1','$zhongl2','$ydhdwbh','$jsck','1','$ztms')";
			if($dbh->exec($sqltjdyxb2)<>1){
				$dbh->query("ROLLBACK");   
				exit("降级失败！");
			}
		}
	}else{
		if($js1==""){
			$sqlxgdyxb="update diaoyunxiangbiao set JSSJ='$jssj',FHJS='$js',FHZL='$zhongl',SHJS='$js',SHZL='$zhongl',BZ='$beizhu',dhckid='$jsck',ZT='1',ZTMS='$ztms' where id='$id'";
			if($dbh->exec($sqlxgdyxb)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库失败3！");
			}
			$sqlxgdyxb1="insert into diaoyunxiangbiao (YSDH,DJBH,DJMC,FHJS,FHZL,fhckid,dhdwbh,ZTMS) values ('$dydh','$djbh1','$dj1','$dsjs','$zhongl1','$fhckid','$ydhdwbh','调运在途')";
			if($dbh->exec($sqlxgdyxb1)<>1){
				$dbh->query("ROLLBACK");   
				exit("等待入库失败！");
			}
		}else{
			$sqlxgdyxb="update diaoyunxiangbiao set JSSJ='$jssj',FHJS='$js',FHZL='$zhongl',SHJS=$js-$js1,SHZL=50*($js-$js1),BZ='$beizhu',dhckid='$jsck',ZT='1',ZTMS='$ztms' where id='$id'";
			if($dbh->exec($sqlxgdyxb)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库失败4！");
			}
			$sqlxgdyxb1="insert into diaoyunxiangbiao (YSDH,DJBH,DJMC,FHJS,FHZL,fhckid,dhdwbh,ZTMS) values ('$dydh','$djbh1','$dj1','$dsjs','$zhongl1','$fhckid','$ydhdwbh','调运在途')";
			if($dbh->exec($sqlxgdyxb1)<>1){
				$dbh->query("ROLLBACK");   
				exit("等待入库失败1！");
			}
			$sqltjdyxb2="insert into diaoyunxiangbiao (YSDH,DJBH,DJMC,JSSJ,SHJS,SHZL,dhdwbh,dhckid,ZT,ZTMS) values ('$dydh','$dj2','$djmc2','$jssj','$js1','$zhongl2','$ydhdwbh','$jsck','1','$ztms')";
			if($dbh->exec($sqltjdyxb2)<>1){
				$dbh->query("ROLLBACK");   
				exit("降级失败1！");
			}
		}
	}
	
	if($js1){
		$sql="select id from dengjikucun where CKID='$jsck' and DJBH='$djbh1'";
		$djkcyf=$dbh->query($sql)->fetchColumn(0);
		if($djkcyf){
			$sqlxgdjkc1="update dengjikucun set KC=KC+($js-$js1) where CKID='$jsck' and DJBH='$djbh1'";
			if($dbh->exec($sqlxgdjkc1)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库等级库存修改失败！");
			}
		}else{
			$sql="insert into dengjikucun(CKID,DJBH,KC) values('$jsck','$djbh1','$js-$js1')";
			if($dbh->exec($sql)<>1){
				$dbh->query("ROLLBACK");   
				exit("接受仓库等级库存添加失败1！");
			}
		}
		$sql="select id from dengjikucun where CKID='$jsck' and DJBH='$dj2'";
		$djkcyf=$dbh->query($sql)->fetchColumn(0);
		if($djkcyf){
			$sqlxgdjkc2="update dengjikucun set KC=KC+$js1 where CKID='$jsck' and DJBH='$dj2'";
			if($dbh->exec($sqlxgdjkc2)<>1){
				$dbh->query("ROLLBACK");   
				exit("入库等级库存修改失败1！");
			}
		}else{
			$sql="insert into dengjikucun(CKID,DJBH,KC) values('$jsck','$dj2','$js1')";
			if($dbh->exec($sql)<>1){
				$dbh->query("ROLLBACK");   
				exit("接受仓库等级库存添加失败2！");
			}
		}
	}else{
		$sql="select id from dengjikucun where CKID='$jsck' and DJBH='$djbh1'";
		$djkcyf=$dbh->query($sql)->fetchColumn(0);
		if($djkcyf){
			$sqlxgdjkc1="update dengjikucun set KC=KC+$js where CKID='$jsck' and DJBH='$djbh1'";//修改等级库存1
			if($dbh->exec($sqlxgdjkc1)<>1){
				$dbh->query("ROLLBACK");   
				exit("接受仓库等级库存修改失败！");
			}
		}else{
			$sql="insert into dengjikucun (CKID,DJBH,KC) values('$jsck','$djbh1','$js')";
			if($dbh->exec($sql)<>1){
				$dbh->query("ROLLBACK");   
				exit("接受仓库等级库存添加失败3！");
			}
		}/*
		$sqlxgdjkc1="update dengjikucun set KC=KC+$js where CKID='$jsck' and DJBH='$djbh1'";
		if($dbh->exec($sqlxgdjkc1)<>1){
			$dbh->query("ROLLBACK");   
			exit("入库等级库存修改失败3！");
		}*/
	}
	
	
	
	
	
	$sqlxgdjkc3="update dengjikucun set KC=KC-$js where CKID='$fhckid' and DJBH='$djbh1'";
	if($dbh->exec($sqlxgdjkc3)<>1){
		$dbh->query("ROLLBACK");   
		exit("发货等级库存修改失败！");
	}
	$sqlxgck1="update cangku set XKC=XKC-$js where id='$fhckid'";
	if($dbh->exec($sqlxgck1)<>1){
		$dbh->query("ROLLBACK");   
		exit("发货仓库库存修改失败！");
	} 
	$sqlxgck2="update cangku set XKC=XKC+$js where id='$jsck'";
	if($dbh->exec($sqlxgck2)<>1){
		$dbh->query("ROLLBACK");   
		exit("入库仓库库存修改失败！");
	}
	
	$indate=date('Y-m-d H:i:s');
	$nianfen=date('Y');
	$sqltjdata="insert into data (zwjb,ywjb,indwbm,indate,frmdwbm,zhongl,js,nianfen,incangkuid,frmcangkuid) values ('$dj1','$djbh1','$dhdwbm','$indate','$fhdwbm','$zhongl','$js','$nianfen','$jsck','$fhckid')";
	if($dbh->exec($sqltjdata)){
		$dbh->query("COMMIT");   
		$res=array('res'=>'OK','msg'=>'成功入库！' );
	}else{
		$dbh->query("ROLLBACK");
		$res=array('res'=>'Error','msg'=>'data表数据添加失败！' ); 
	}
	$dbh->query("END");    
    echo json_encode($res);
    exit();
}
function modifyxianshi(){
	$dbh=dbconnect();
    $id=addslashes($_POST['id']);
	$sql="select DJMC,FHJS from diaoyunxiangbiao where id='$id'";
	$djmc=$dbh->query($sql)->fetchColumn(0);
	$fhjs=$dbh->query($sql)->fetchColumn(1);
	if($djmc){
		$res=array('res'=>'Ok','djmc'=>$djmc,'js'=>$fhjs);
	}else{
		$res=array('res'=>'Error','msg'=>'信息读取错误！' );
	}
    echo json_encode($res);
    exit();
}
function jsck(){
	$dbh=dbconnect();
	$sql="select id,CKMC from cangku where DWBM in (select danweibm from user where danweimc='".$_SESSION['danweimc']."')";
	foreach($dbh->query($sql) as $r){
		$str .= "<option value='{$r['id']}'>{$r['CKMC']}</option>";
	}
	echo $str;
	exit();
}
function dj2(){
	$dbh=dbconnect();
	$sql="select DJBH,DJMC from dengjibiao";
	foreach($dbh->query($sql) as $r){
		$str .= "<option value='{$r['DJBH']}'>{$r['DJMC']}</option>";
	}
	echo $str;
	exit();
}
function heduijianshu(){
	$dbh=dbconnect();
    $id=addslashes($_POST['id']);
	$sql="select FHJS from diaoyunxiangbiao where id='$id'";
    $FHJS=$dbh->query($sql)->fetchColumn(0);
	if($FHJS){
		$res=array('res'=>'OK','js'=>$FHJS);
	}else{
		$res=array('res'=>'Error');
	}
	echo json_encode($res);
    exit();
}
function chakanckkc(){
	$dbh=dbconnect();
    $jsck=addslashes($_POST['jsck']);
	$sql="select ZKR,XKC from cangku where id='$jsck'";
	$ZKR=$dbh->query($sql)->fetchColumn(0);
	$XKC=$dbh->query($sql)->fetchColumn(1);
	if($ZKR){
		$res=array('res'=>'OK','ZKR'=>$ZKR,'XKC'=>$XKC);
	}else{
		$res=array('res'=>'Error');
	}
	echo json_encode($res);
    exit();
}
function chakanckkc1(){
	$dbh=dbconnect();
	$jsck1=addslashes($_POST['jsck1']);
	$dydh=addslashes($_POST['dydh']);
	$sql="select JS from diaoyunxiangbiao where YSDH='$dydh' and dhckid='0'";
	foreach($dbh->query($sql) as $r){
		if($r){
			$jjs +=$r['JS'];
		}
	}
	$sql="select ZKR,XKC from cangku where id='$jsck1'";
	$ZKR=$dbh->query($sql)->fetchColumn(0);
	$XKC=$dbh->query($sql)->fetchColumn(1);
	if($ZKR<$XKC+$jjs){
		$res=array('res'=>'Error','msg'=>'仓库空间不足！');
	}else{
		$res=array('res'=>'OK');
	}
	echo json_encode($res);
	exit();
}
function daohuodidian(){
	$shijian=date("Y-m-d H:i:s");
	$res=array('dhdd'=>$_SESSION['danweimc'],'shijian'=>$shijian,'name'=>$_SESSION['username']);
    echo json_encode($res);
    exit();
}
function diaoyundan(){
	$dbh=dbconnect();
    $dydh=addslashes($_POST['dydh']);
	$dydh2="<img src='../barcode/test.php?codebar=BCGcode39&text=$dydh'>";
	$sql="select CYRDW,CPH,JSXM from diaoyundan where DYDH='$dydh'";
    $cyrdw=$dbh->query($sql)->fetchColumn(0);
	$cph=$dbh->query($sql)->fetchColumn(1);
	$jsxm=$dbh->query($sql)->fetchColumn(2);
	$sql="select dwmc from danweidm where dwbm=(select DWBM from cangku where id in (select fhckid from diaoyunxiangbiao where YSDH='$dydh'))";
    $dwmc=$dbh->query($sql)->fetchColumn(0);
	$res=array('res'=>'OK','jsyxm'=>$jsxm,'cph'=>$cph,'cyrdw'=>$cyrdw,'fhdw'=>$dwmc,'txm'=>$dydh2);
	echo json_encode($res);
    exit();
}
function fenyexianshi(){
	$dbh=dbconnect();
	$dydh=addslashes($_POST['dydh']);
	$sql="select id,DJBH,DJMC,FHJS,FHZL,SHJS,SHZL,BZ,fhckid,dhckid from diaoyunxiangbiao where YSDH='$dydh' order by id ";
	function caozuo($zhujian){
		$str = "<input type='button' value='接收入库' title='烟叶入库'  onclick='modify($zhujian)'  />";
		return $str;
	}
	$str="<table align='center' width='770'><thead><tr height='40'> <th colspan='2' rowspan='2' width='130' background='../images/biaotou.jpg'></th> <th colspan='2'>发货</th> <th colspan='3'>收货</th> <th rowspan='2' width='210'>备注</th><th width='70'>操作</th></tr> <tr height='30'> <th width='50'>件数</th> <th width='80'>重量(kg)</th> <th width='50'>件数</th> <th width='80'>重量(kg)</th> <th width='100'>接受仓库</th><th><input type='button' value='一键入库' title='烟叶一键入库'  onclick='yijianrk()'  /></th></tr></thead><tbody>";
	foreach($dbh->query($sql) as $r){
		$sqlc="select CKMC from cangku where id='".$r['dhckid']."'";
		$ckmc=$dbh->query($sqlc)->fetchColumn(0);
		//foreach($dbh->query($sqlc) as $rc){
			//$ckmc=$rc['CKMC'];
		//}
		$str .= "<tr height='30'><td width='50'>{$r['DJBH']}</td><td width='80'>{$r['DJMC']}</td><td>";
		if($r['fhckid']<>0){
			$str .= "{$r['FHJS']}";
			$js1+=$r['FHJS'];
		}
		$str .="</td><td>";
		if($r['fhckid']<>0){
			$str .="{$r['FHZL']}";
			$zl1+=$r['FHZL'];
		}
		$str .="</td><td>";
		if($r['dhckid']<>0){
			$str .= "{$r['SHJS']}";
			$js2+=$r['SHJS'];
			
		}
		$str .="</td><td>";
		if($r['dhckid']<>0){
			$str .="{$r['SHZL']}";
			$zl2+=$r['SHZL'];
		}
		$str .= "</td><td>{$ckmc}</td><td>{$r['BZ']}</td><td>";
		if($r['dhckid']==0)
			$str .= caozuo($r['id']); 
		$str .= "</td></tr>";
		++$i;
	}
	$str .="<tr height='30'><td width='50'></td><td width='80'></td><td>".$js1."</td><td>".$zl1."</td><td>".$js2."</td><td>".$zl2."</td><td></td><td></td><td></td></tr>";
	for($j=0;$j<15-$i;$i++){
		$str .="<tr height='30'><td width='50'></td><td width='80'></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
	}
	$str .= "</tbody></table>{$ncstr}";
	echo $str;
	exit();
}

$res=array('res'=>'Error','msg'=>'未知原因的错误！');
echo json_encode($res);
?>
