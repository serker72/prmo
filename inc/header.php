<?
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');


$sm=new SmartyAdm;
if(isset($result)&&($result!==NULL)){
	$sm->assign('authed',true);
	
	$sm->assign('login',$result['login']);
	$sm->assign('email_s',$result['email_s']);

	$sm->assign('name',stripslashes($result['name_s']));
	$_menu_org=new OrgItem;
	$_menu_opf=new OpfItem;
	
	$menu_org=$_menu_org->GetItemById($result['org_id']);
	$menu_opf=$_menu_opf->GetItemById($menu_org['opf_id']);
	$sm->assign('org_name',stripslashes($menu_opf['name'].' '.$menu_org['full_name']));

	
	
	
	//новая позиция каталога
	if($au->user_rights->CheckAccess('w',67)){
		$sm->assign('has_new_position',true);	
	}
	
	if($au->user_rights->CheckAccess('w',70)){
		$sm->assign('has_new_tovgr',true);	
	}
	
	if($au->user_rights->CheckAccess('w',87)){
		$sm->assign('has_new_supplier',true);	
	}
	
if($au->user_rights->CheckAccess('w',81)){
		$sm->assign('has_new_komplekt',true);
		
		//CanCreateBySector($user_id)
	//	$_temp_kg=new KomplGroup();
		//$sm->assign('can_create_by_sector',$_temp_kg->CanCreateBySector($result['id']));
		
			
	}
	
	
	$sm->assign('has_plan',$au->user_rights->CheckAccess('w',903));	
	
	
}

if(!isset($stop_popup)) $stop_popup=false;
$sm->assign('stop_popup', $stop_popup);




$header_res=$sm->fetch('header.html');
unset($sm);

$_is_ipad=(strpos($_SERVER['HTTP_USER_AGENT'],'iPad')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'Android')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'android')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'Mobile')!==false)
||(strpos($_SERVER['HTTP_USER_AGENT'],'mobile')!==false
);
@$smarty->assign('is_ipad',$_is_ipad);

?>