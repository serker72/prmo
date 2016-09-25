<?
session_start();
require_once('classes/global.php');
require_once('classes/smarty/SmartyAdm.class.php');
 require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/supplieritem.php');
require_once('classes/opfitem.php');

require_once('classes/suppliercontactitem.php');
require_once('classes/user_s_item.php');

require_once('classes/suppliercontactdatagroup.php');
require_once('classes/usercontactdatagroup.php');
 
 
 
require_once('classes/v2/delivery_lists.class.php');
require_once('classes/v2/delivery.class.php');

 
require_once('classes/authuser.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',944)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	} 
 
 

//$_list=new Delivery_UserGroup;
$_razd=new Delivery_ListItem;
$_du=new Delivery_UserItem;
$_d=new Delivery_Item;

foreach($_GET as $key=>$val){ 

	$cter=0;
	 if(eregi("^1_",$key)){
	//	echo 'контрагент'; 
		$cter++;
	 }
	 
	 if(eregi("^2_",$key)){
	//	echo 'сотрудник'; 
		$cter++;
		
	 }
	 
	 
}

$_ui=new UserSItem;
$_si=new SupplierItem;
$_sci=new SupplierContactItem;
$_opf=new OpfItem;

$_scg=new SupplierContactDataGroup;
$_ucg=new UserContactDataGroup;

if($cter>0){
	$li_params=array();
	
	$li_params['name']=SecStr('Список для поздравительной рассылки от '.date('d.m.Y H:i:s'));
 
	$li_params['comment']=SecStr('Создан сотрудником '.$result['name_s'].' из отчета Дни Рождения');	

 
 
	$list_id=$_razd->Add($li_params);
	
	foreach($_GET as $key=>$val){ 
		
		 
		 if(eregi("^1_",$key)){
		//	echo 'контрагент'; 
			$data=explode('_', eregi_replace('^1_','',$key));
			
			$si=$_si->getitembyid($data[0]);
			$opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($data[1]);
			$scg=$_scg->GetItemsByIdArr($sci['id']);
			foreach($scg as $k=>$v){
				if($v['kind_id']!=5) continue;
				 $u_params=array();
				 $u_params['is_subscribed']=1;
			
				 $u_params['list_id']=$list_id;
			 	
				$u_params['email']=SecStr($v['value']);
	 
				$u_params['comment']=SecStr('');	
				
				$u_params['f']=SecStr($sci['name']);
				$u_params['i']=SecStr('');
				$u_params['o']=SecStr('');
				$u_params['kind_id']=1;
				$u_params['supplier_id']=$si['id'];
				$u_params['supplier_contact_id']=$sci['id'];
				$u_params['supplier_contact_data_id']=$v['id'];
				
				$_du->Add($u_params);
			}
	
			
			 
		 }
		 
		 if(eregi("^2_",$key)){
			$data=explode('_', eregi_replace('^2_','',$key));
			 
			//echo 'сотрудник'; 
			//echo ($val);
			
			$ui=$_ui->GetItemById($data[0]);
			$ucg=$_ucg->GetItemsByIdArr($ui['id']);
			foreach($ucg as $k=>$v){
				if($v['kind_id']!=5) continue;
				 $u_params=array();
				 $u_params['is_subscribed']=1;
			
				 $u_params['list_id']=$list_id;
			 	
				$u_params['email']=SecStr($v['value']);
	 
				$u_params['comment']=SecStr('');	
				
				$u_params['f']=SecStr($ui['name_s']);
				$u_params['i']=SecStr('');
				$u_params['o']=SecStr('');
				$u_params['kind_id']=2;
				$u_params['user_id']=$ui['id'];
				$u_params['user_contact_data_id']=$v['id'];
				
				$_du->Add($u_params);
			}
	
		 }
		 
		 
	}
	
	
	$params=array();
		$params['name']='Поздравительная рассылка от '.date('d.m.Y H:i');
		$params['pdate_status_change']=time();
		$params['is_birth']=1;
		
		$id=$_d->Add($params);
		
		header('Location: '.$_d->GetPageName().'?id='.$id.'&list_id='.$list_id);	 
		die(); 
 }else{
	 header('Location: delivery_campaigns.php');	 
		die(); 
	 
 }


 /*
 
  if(isset($_POST['Update'])||isset($_POST['Update1'])){
	  $kind=(int)$_POST['kind'];
	  
	  
	  
	  
	  if($kind==2){
		  //Обновляем базу
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				   
				  	$r=new Delivery_UserItem;
				    $r->Del($lid);
				   
				  
				  
			  }
		  }
	  }
	  
	  if($kind==4){
		   
		   //скопировать в список
		   foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  $lid=(int)$val;
				  
				      
					$r=new Delivery_UserItem;
					$ours=$r->getitembyid($lid);
					if($ours!==false){
						$params=array();
						$params['email']=$ours['email'];
						$params['f']=$ours['f'];
						$params['i']=$ours['i'];
						$params['o']=$ours['o'];
						
						$params['comment']=$ours['comment'];
						
						$params['list_id']=abs((int)$_POST['target_list']);
						$params['is_subscribed']=1;
						
						$test=$r->GetItemByFields(array('email'=>$params['email'], 'list_id'=>$params['list_id']));
						if($test===false) $r->Add($params);
						else $r->Edit($test['id'], $params);
						
							
					}
					 
					
					 
				  
				}
		  }
	 
		  
	  }
	  
	  if($kind==5){
		   
		   //переместить в список
		   foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  $lid=(int)$val;
				  
				   
					$r=new Delivery_UserItem;
				    $r->Edit($lid, array('list_id'=>abs((int) $_POST['target_list'])));
				    
				}
		  }
	 
		  
	  }
	  
	  if($kind==6){
		  //отписать от рассылки
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				   
				  	$r=new Delivery_UserItem;
				    $r->Edit($lid, array('is_subscribed'=>0, 'unsubscribe_way'=>'Отписан администратором', 'unsubscribe_reason'=>'Выбор администратора'));
				   
				  
				  
			  }
		  }
	  }
	  
	   if($kind==7){
		  //восстановить подписку
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				  
				  	$r=new Delivery_UserItem;
				    $r->Edit($lid, array('is_subscribed'=>1, 'unsubscribe_way'=>'', 'unsubscribe_reason'=>''));
				   
				  
				  
			  }
		  }
	  }
	  
	   
	  header('Location: '.$_list->GetPageName().'?id='.$id.'&from='.$from.'&to_page='.$to_page);
	  die();
  }
 
*/






?>