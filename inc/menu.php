<?
require_once('classes/menubuilder.php');
require_once('classes/menuitem.php');
require_once('classes/menuallitem.php');
require_once('classes/menusecitem.php');
require_once('classes/messagegroup.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

if(!isset($result)) $result=NULL;
if(!isset($_menu_id)) $_menu_id=0;


$builder=new MenuBuilder($result);

$menu_arr=$builder->BuildMenu($result['id'], $_menu_id);

$builder->SetBranch(3);
$menu_arr_fast=$builder->BuildMenu($result['id'], $_menu_id);

$sm=new SmartyAdm;

$sm->assign('login',$result['login']);

$sm->assign('name',stripslashes($result['name_s']));
$_menu_org=new OrgItem;
$_menu_opf=new OpfItem;

$menu_org=$_menu_org->GetItemById($result['org_id']);
$menu_opf=$_menu_opf->GetItemById($menu_org['opf_id']);
$sm->assign('org_name',stripslashes($menu_opf['name'].' '.$menu_org['full_name']));



//кнопка выбора организации
//доступна, если есть хотя бы одна, кроме текущей, доступная организация
/*if(isset($result)&&($result!==false)){
	require_once('classes/program_group.php');
	$_pg=new ProgramGroup;
	
	$_pg->FindAccess($result['email_s'], ($result['password']), 'login_program.html', $matched, $result['org_id'],true);
	 
	
	$sm->assign('has_change_base', count($matched)>0);
}
*/




/*$_df=new DiscountFind();


$skidka=$_df->FindDiscountByUser($result);
if(($result['group_id']==3)&&($skidka>0)){
	$sm->assign('has_skidka',true);
	$sm->assign('skidka',$skidka);
}

if(($result['group_id']==3)&&($result['fact_address_id']>0)){
	
	
	$fa=new FaItem;
	$faa=$fa->GetItemById($result['fact_address_id']);
	if($faa!==false){
		
		$sm->assign('has_address',true);
		$sm->assign('fact_address_form',stripslashes($faa['name']));
		$sm->assign('fact_address_name',stripslashes($faa['address']));
			
	}
}
*/

$sm->assign('menu',$menu_arr);
$menu_res=$sm->fetch('main_menu.html');

?>