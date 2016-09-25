<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/supplieritem.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

 
 
require_once('../classes/v2/delivery_lists.class.php');
require_once('../classes/v2/delivery_templates.class.php');
require_once('../classes/v2/delivery.class.php');
	

//setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');

 

	
$ret='';

// сохраняем данные в зависимости от шага
if(isset($_POST['action'])&&($_POST['action']=="save_data")){
	$id=abs((int)$_POST['id']);
	$step=abs((int)$_POST['step']);
	$list_id=abs((int)$_POST['list_id']);
	$segment_id=abs((int)$_POST['segment_id']);
	$mode=abs((int)$_POST['mode']);
	
	$emails=(iconv('utf-8', 'windows-1251',$_POST['emails']));
	
	$_di=new Delivery_Item;
	$_dsu=new Delivery_UserSegmentItem;
	$_du=new Delivery_UserItem;
	$_ds=new Delivery_SegmentItem;
	$_dt=new Delivery_TemplateItem;
	
	$di=$_di->getitembyid($id);
	
	//шаг 1
	if($step==1){
		$params=array();
		if($mode==1){
			$params['list_id']=$list_id;
			$params['segment_id']=0;
			
			$_di->Edit($id, $params);	
			
		}elseif($mode==2){
			$params['list_id']=$list_id;
			$params['segment_id']=$segment_id;
			
			$_di->Edit($id, $params);	
		}elseif($mode==3){
			//создать сегмент
			$params['name']='Сегмент для рассылки '.$di['name'].' '.date('d.m.Y H:i');
			$params['list_id']=$list_id;
			$_segment_id=$_ds->Add($params);
			
			$_emails=explode("\n", $emails);
			
			foreach($_emails as $k=>$v){
				$test_user=$_du->GetItemByFields(array('list_id'=>$list_id, 'email'=>SecStr($v)));
				if($test_user!==false){
					$_dsu->Add(array('user_id'=>$test_user['id'], 'segment_id'=>$_segment_id));
				}
			}
			
			$params=array();
			$params['list_id']=$list_id;
			$params['segment_id']=$_segment_id;
			
			$_di->Edit($id, $params);
		}
			
		
	}
	elseif($step==2){
		/*"id":$("#id").val(),
				  "name":$("#name").val(),
				  "step":$("#step").val(),
				  "topic":$("#topic").val(),
				  "from_name":$("#from_name").val(),
				  "from_email":$("#from_email").val(),
				  "to_is_personal":to_is_personal,
				  "to_field":$("#to_field").val(),
				  "has_tracking":has_tracking,
				  "has_clicks_tracking":has_clicks_tracking*/
		
		$params=array();
		$params['name']=SecStr(iconv('utf-8', 'windows-1251', $_POST['name']));
		$params['topic']=SecStr(iconv('utf-8', 'windows-1251', $_POST['topic']));
		$params['from_name']=SecStr(iconv('utf-8', 'windows-1251', $_POST['from_name']));
		$params['from_email']=SecStr(iconv('utf-8', 'windows-1251', $_POST['from_email']));
		$params['to_is_personal']=abs((int)$_POST['to_is_personal']);
		$params['to_field']=SecStr(iconv('utf-8', 'windows-1251', $_POST['to_field']));
		$params['has_tracking']=abs((int) $_POST['has_tracking']);
		$params['has_clicks_tracking']=abs((int) $_POST['has_clicks_tracking']);
		$params['is_birth']=abs((int) $_POST['is_birth']);
		 
		 $_di->Edit($id, $params);
				  
		
	}
	elseif($step==3){
		$template_id=abs((int)$_POST['template_id']);
		
		$old=$_di->GetItemById($id);
		if($old['template_id']!=$template_id){
			$template=$_dt->GetItemById($template_id);
			
			$params=array();
			$params['template_id']=$template_id;
			$params['html_content']=SecStr($template['html_content']);
			
			$params['plain_text_content']=SecStr($template['html_content'],10);
			
			 $_di->Edit($id, $params);
		}
		
	}
	elseif($step==4){
		$params=array();
		$params['html_content']=SecStr(iconv('utf-8', 'windows-1251', $_POST['html_content']));
		$params['plain_text_content']=SecStr(iconv('utf-8', 'windows-1251', $_POST['html_content']),10);
		$_di->Edit($id, $params);
		
	}
	/*
	$current_id=abs((int)$_POST['current_id']);
	
	
	$_ui=new Delivery_UserItem;
	
	$ui=$_ui->getitembyfields(array('list_id'=>$list_id, 'email'=>$email), array('id'=>$current_id));
	if($ui!==false) $ret=1;
	else $ret=0; 
	 */
	
} 

elseif(isset($_POST['action'])&&($_POST['action']=="load_template_name")){
	$id=abs((int)$_POST['id']);
	$_dti=new Delivery_TemplateItem;
	$dti=$_dti->GetItemById($id);
	
	$ret=$dti['name'];
}


echo $ret;	
?>