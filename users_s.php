<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/user_s_group.php');

//require_once('classes/storagegroup.php');
//require_once('classes/sectorgroup.php');

require_once('classes/rep_users_quests.php');

require_once('classes/user_v_group.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'—отрудники');



$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
 



if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('x',8)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
			}
		}
	}
	
	header("Location: users_s.php");	
	die();
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);



if(!$au->user_rights->CheckAccess('w',8)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$log=new ActionLog;
if($print==0){
	$log->PutEntry($result['id'],'открыл раздел —отрудники',NULL,8);
}else{
	$log->PutEntry($result['id'],'открыл раздел —отрудники: верси€ дл€ печати',NULL,8);
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=8;
	if($print==0) include('inc/menu.php');
	
	
	//демонстраци€ стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	/*
	//строим вкладку администрировани€
	$sm->assign('has_admin',$au->user_rights->CheckAccess('x',8)||
							$au->user_rights->CheckAccess('x',10)||
							$au->user_rights->CheckAccess('x',11)
							);
	$dto=new DiscrTableObjects($result['id'],array('8','10','11'));
	$admin=$dto->Draw('users_s.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	//тест
	/*require_once('classes/logincreator.php');
	$lc=new LoginCreator;
	
	echo $lc->GenLogin(2,$result['id']);
	*/
	
	//–азбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	
	//ограничени€ по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
	}
	//print_r($limited_user);
	
		
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['users_s_from'])){
		$from=abs((int)$_SESSION['users_s_from']);
	}else $from=0;
	$_SESSION['users_s_from']=$from;

	
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	if(isset($_GET['is_active'])&&($_GET['is_active']==1)){
		$decorator->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}elseif(isset($_GET['is_active'])&&($_GET['is_active']==0)){
		 $decorator->AddEntry(new UriEntry('is_active',0));	
	}else{
		if(!$au->user_rights->CheckAccess('w',512)) {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		}elseif(isset($_GET['doFilter'])){ //if(count($_GET)>1){

			 $decorator->AddEntry(new UriEntry('is_active',0));	
			 //echo 'ZZZZZZZZZZZZzz';
		}else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		}
	}
	
	
	if(isset($_GET['login'])&&(strlen($_GET['login'])>0)){
		$decorator->AddEntry(new SqlEntry('u.login',SecStr($_GET['login']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('login',$_GET['login']));
	}
	
	if(isset($_GET['name_s'])&&(strlen($_GET['name_s'])>0)){
		$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['name_s']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name_s',$_GET['name_s']));
	}
	
	if(isset($_GET['position_s'])&&(strlen($_GET['position_s'])>0)){
		$decorator->AddEntry(new SqlEntry('u.position_s',SecStr($_GET['position_s']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('position_s',$_GET['position_s']));
	}
	
	if(isset($_GET['email_s'])&&(strlen($_GET['email_s'])>0)){
		$decorator->AddEntry(new SqlEntry('u.email_s',SecStr($_GET['email_s']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('email_s',$_GET['email_s']));
	}
	
	
	if(isset($_GET['storage'])&&(strlen($_GET['storage'])>0)){
		//$decorator->AddEntry(new SqlEntry('storage',SecStr($_GET['storage']), SqlEntry::LIKE));
		//$decorator->AddEntry(new SqlEntry('storage',0, SqlEntry::IN_VALUES,NULL, 'select distinct' //::LIKE));
		$in_storage=abs((int)$_GET['storage']);
		
		$decorator->AddEntry(new UriEntry('storage',$_GET['storage']));
	}else $in_storage=NULL;
	
	if(isset($_GET['sector'])&&(strlen($_GET['sector'])>0)){
		//$decorator->AddEntry(new SqlEntry('storage',SecStr($_GET['storage']), SqlEntry::LIKE));
		$in_sector=abs((int)$_GET['sector']);
		
		$decorator->AddEntry(new UriEntry('sector',$_GET['sector']));
	}else $in_sector=NULL;
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('email_s',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('email_s',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('position_s',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('position_s',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	
	
	$ug=new UsersSGroup;
	$ug->SetAuthResult($result);

	
	if($print==0) $uug= $ug->GetItems('users/s'.$print_add.'.html',$decorator,$from,$to_page,$in_storage,$in_sector,  $au->user_rights->CheckAccess('w',512), $au->user_rights->CheckAccess('w',542), '', 3, $limited_user);
	else $uug= $ug->GetItems('users/s'.$print_add.'.html',$decorator,0,1000000,$in_storage,$in_sector,  $au->user_rights->CheckAccess('w',512),  $au->user_rights->CheckAccess('w',542), '', 3, $limited_user);
	
	
	
	
	$sm->assign('users',$uug);
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
//**************************** виртуальные сотрудники ****************************************************************/	
	
	$sm->assign('has_virtual',$au->user_rights->CheckAccess('w',769));
	
	$prefix='_1';
	
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	elseif(isset($_SESSION['users_s_from'.$prefix])){
		$from=abs((int)$_SESSION['users_s_from'.$prefix]);
	}else $from=0;
	$_SESSION['users_s_from'.$prefix]=$from;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	if(isset($_GET['is_active'.$prefix])&&($_GET['is_active'.$prefix]==1)){
		$decorator->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}elseif(isset($_GET['is_active'.$prefix])&&($_GET['is_active'.$prefix]==0)){
		 $decorator->AddEntry(new UriEntry('is_active',0));	
	}else{
		if(!$au->user_rights->CheckAccess('w',780)) {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
		}elseif(isset($_GET['doFilter'.$prefix])){ //if(count($_GET)>1){
			 $decorator->AddEntry(new UriEntry('is_active',0));	
			 //echo 'ZZZZZZZZZZZZzz';
		}else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
		}
	}
	
	
	if(isset($_GET['login'.$prefix])&&(strlen($_GET['login'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('login',SecStr($_GET['login'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('login',$_GET['login'.$prefix]));
	}
	
	if(isset($_GET['name_s'.$prefix])&&(strlen($_GET['name_s'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('name_s',SecStr($_GET['name_s'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name_s',$_GET['name_s'.$prefix]));
	}
	
	if(isset($_GET['position_s'.$prefix])&&(strlen($_GET['position_s'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('position_s',SecStr($_GET['position_s'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('position_s',$_GET['position_s'.$prefix]));
	}
	
	 
	
	if(isset($_GET['email_s'.$prefix])&&(strlen($_GET['email_s'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('email_s',SecStr($_GET['email_s'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('email_s',$_GET['email_s'.$prefix]));
	}
	
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('name_s',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('email_s',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('email_s',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('position_s',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('position_s',SqlOrdEntry::ASC));
		break;
		 
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('login',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	$decorator->AddEntry(new UriEntry('tab_page',3));
	
	
	
	$ug1=new UsersVGroup;
	
	$ug1->SetAuthResult($result);

	if($print==0) $uug1= $ug1->GetItems('users/s'.$print_add.'.html',$decorator,$from,$to_page,$in_storage,$in_sector,  $au->user_rights->CheckAccess('w',780), $au->user_rights->CheckAccess('w',770),$prefix, 3);
	else $uug1= $ug1->GetItems('users/s'.$print_add.'.html',$decorator,0,1000000,$in_storage,$in_sector,  $au->user_rights->CheckAccess('w',780),  $au->user_rights->CheckAccess('w',770),$prefix, 3);
	
	
	$sm->assign('virtual', $uug1);
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	//$sm->assign('users',$uug);
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//отчет "¬опросы, курируемые сотрудниками"
	
	$_rp=new RepUsersQuests;
	
	$qsts=array();
	
	
	$qsts=array();
	foreach($_GET as $k=>$v){
		if(eregi("^quest_",$k)){
			$qsts[]=abs((int)$v);
		}
		
	}
	
	
	$rep=$_rp->ShowData($qsts,'users_quests/users_quests'.$print_add.'.html', (count($qsts)>0)&&(isset($_GET['doSub'])||isset($_GET['doSub.x'])||isset($_GET['doSub_x'])),$print, $limited_user);
	
	
	$sm->assign('users_quests',$rep);
	
	
	
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	if( (count($qsts)>0)&&(isset($_GET['doSub'])||isset($_GET['doSub.x'])||isset($_GET['doSub_x']))  ){
		$log->PutEntry($result['id'],'просмотр отчета ¬опросы, курируемые сотрудниками',NULL,8);
	}elseif((count($qsts)>0)&&(isset($_GET['doSub'])||isset($_GET['doSub.x'])||isset($_GET['doSub_x']))){
		$log->PutEntry($result['id'],'печать отчета ¬опросы, курируемые сотрудниками',NULL,8);
	}
	
	
	
	
	
	
	
	
	$content=$sm->fetch('users/users_s'.$print_add.'.html');
	
	
	
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>