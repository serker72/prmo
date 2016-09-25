<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/billfileitem.php');
require_once('classes/billfilegroup.php');

require_once('classes/supplieritem.php');
require_once('classes/orgitem.php');

 require_once('classes/supplier_ruk_group.php');
 
  require_once('classes/supplier_ruk_kinds.php');
 
  require_once('classes/supplier_ruk_item.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'������������ �����������/�����������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$ui=new SupplierRukItem;
$_kinds=new SupplierRukKindGroup;

if(($action==0)){

	if(!isset($_GET['supplier_id'])){
		if(!isset($_POST['supplier_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $supplier_id=abs((int)$_POST['supplier_id']);	
	}else $supplier_id=abs((int)$_GET['supplier_id']);
	
}

if(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//�������� ������� ������������
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	$supplier_id=$editing_user['supplier_id'];
	
}

$_si=new SupplierItem;
$si=$_si->GetItemById($supplier_id);

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',87)&&!$au->user_rights->CheckAccess('w',121)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	$params=array();
	
	
	
   
    //������� �������� ������ ����������
	
	 
	$params['fio']=SecStr($_POST['fio']);
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	 
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	 
	$params['sign']=SecStr($_POST['sign']);
	
 
	$params['begin_pdate']=DateFromDMY($_POST['begin_pdate']);
	 
	
	 
 
	$code=$ui->Add($params); //, $quests);
	 
	//������ � �������
	if($code>0){
		/*$log->PutEntry($result['id'],'������ �����������',NULL,120,NULL,$params['name'],$code);
		
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'������������ �����������',NULL,120, NULL, '� ���� '.$k.' ����������� �������� '.$v,$code);		
			}
		}*/	
		$descr='������ ';
		if($params['kind_id']==1) $descr.=' ������������ ';
		elseif($params['kind_id']==2) $descr.= ' �������� ���������� ';
		
		$object_id=87;
		if($si['is_org']==0) $object_id=87;
		elseif($si['is_org']==1) $object_id=121;
		
		
		$log->PutEntry($result['id'],$descr,NULL, $object_id,NULL,$params['fio'], $supplier_id);
		
		 
		
		/* 87 - supp
		
		121 - org*/
	}
	
	
	//���������������
	if(isset($_POST['doNew'])){
		header("Location: supplier_ruks.php?supplier_id=".$supplier_id."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//���� ���� ������ � ������� 11 - ������ ������������ - �� ������� ����		
		if(!$au->user_rights->CheckAccess('w',121)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_supplier_ruks.php?action=1&id=".$code.'');
		die();	
		
	}else{
		header("Location: ed_supplier_ruks.php");
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//�������������� �����������
	 
	
    //if(($editing_user['is_confirmed']==0)&&!isset($_POST['is_confirmed'])){
	$condition=true;
	$condition=$condition&&($si['is_confirmed']==0);
	 
	
	if($condition){	
			
	
	
	
	
   // if(($editing_user['is_confirmed']==0)&&!isset($_POST['is_confirmed'])){
	  //������� �������� ������ ����������
	  $params=array();
	 $params['fio']=SecStr($_POST['fio']);
	
	 
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	 
	$params['sign']=SecStr($_POST['sign']);
	
 
	$params['begin_pdate']=DateFromDMY($_POST['begin_pdate']);
	 
	
	 
 
	
	
	  
	  $ui->Edit($id,$params);
	  
	  //������ � ���. �������� ������ � ����� ������
	  foreach($params as $k=>$v){
		  
		  $descr='������������ ';
		if($params['kind_id']==1) $descr.=' ������������ ';
		elseif($params['kind_id']==2) $descr.=' �������� ���������� ';
		
		$object_id=87;
		if($si['is_org']==0) $object_id=87;
		elseif($si['is_org']==1) $object_id=121;
		
		
		
		  
		  if(addslashes($editing_user[$k])!=$v){
			  $log->PutEntry($result['id'],$descr,NULL,$object_id, NULL, '� ���� '.$k.' ����������� �������� '.$v,$supplier_id);		
		  }
	  }
	  
	}
	 
	//���������������
	if(isset($_POST['doEdit'])){
		header("Location:  supplier_ruks.php?supplier_id=".$supplier_id."#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//���� ���� ������ � ������� 16 - ������ ������������ - �� ������� ����		
		 
		header("Location: ed_supplier_ruks.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location:  supplier_ruks.php?supplier_id=".$supplier_id."");
		die();
	}
	
	die();
 
	
}elseif(($action==2)){
	 
	
	$ui->Del($id);
	
	//$log->PutEntry($result['id'],'������ �����������',NULL,122, NULL, $editing_user['name'],$id);	
	
	
	$descr='������ ';
	if($params['kind_id']==1) $descr.=' ������������ ';
		elseif($params['kind_id']==2) $descr.=' �������� ���������� ';
		
		$object_id=87;
		if($si['is_org']==0) $object_id=87;
		elseif($si['is_org']==1) $object_id=121;
		
		
		
		  
		  
			  $log->PutEntry($result['id'],$descr,NULL,$object_id, NULL, '� ���� '.$k.' ����������� �������� '.$v,$supplier_id);		
		  
	header("Location:  supplier_ruks.php?supplier_id=".$supplier_id."");
		 
	die();
}



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
 
 
 
 	if($action==0){
		//�������� ������������
		
		$sm1=new SmartyAdm;
		//����
		
		
		$sm1->assign('session_id', session_id());
		$sm1->assign('kinds', $_kinds->GetItemsArr(0));
		
		$sm1->assign('supplier_id', $supplier_id);
		
		$sm1->assign('begin_pdate', date('d.m.Y'));
		 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',121)&&($si['is_confirmed']==0)); 
		
	
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',121)&&($si['is_confirmed']==0)); 
	
		
		
		$user_form=$sm1->fetch('supplier_ruk/supplier_ruk_create.html');
	}elseif($action==1){
		//�������������� ������������
		
		
		 
		
		$sm1=new SmartyAdm;
		
		$sm1->assign('session_id', session_id());
		$sm1->assign('kinds', $_kinds->GetItemsArr($editing_user['kind_id']));
		
		$editing_user['begin_pdate']=date('d.m.Y', $editing_user['begin_pdate']);
		
		$sm1->assign('user',$editing_user);
		
		
		
		
		$sm1->assign('supplier_id', $supplier_id);
		
		//����������� �������������� - ������ ���� Is_confirmed==0
		$sm1->assign('can_modify', $au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',121)&&($si['is_confirmed']==0));    //$au->user_rights->CheckAccess('w',121)); //   $editing_user['is_active']==0);
		 
		 
					
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',121)&&($si['is_confirmed']==0)); 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',121)&&($si['is_confirmed']==0)); 
		 
		
		
		
		
		$user_form=$sm1->fetch('supplier_ruk/supplier_ruk_edit.html');
		
		
		
		
		
	 
		
	}
 
 
 
 
 	$sm->assign('log2',$user_form);
 
 
 
 
 
 
	
	$content=$sm->fetch('supplier_ruk/ed_supplier_ruk_page.html');
	

	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>