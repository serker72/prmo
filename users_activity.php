<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/users_activity.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Активность пользователей');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);


//внесение изменений в права
if(isset($_POST['doInp'])){
	
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
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: users_activity.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',118)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=32;
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',118));
	$dto=new DiscrTableObjects($result['id'],array('118'));
	$admin=$dto->Draw('users_activity.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	
	*/
	
	
	//покажем лог
	$log=new UsersActivity;
	//Разбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	$decorator2=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator2->AddEntry(new UriEntry('print',$_GET['print']));
	if(isset($_GET['is_active'])){
		//$decorator->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
		$decorator2->AddEntry(new UriEntry('is_active',1));
		
	}else{
		if(count($_GET)>0){
			 $decorator->AddEntry(new UriEntry('is_active',0));	
			 $decorator2->AddEntry(new UriEntry('is_active',0));
		}else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator2->AddEntry(new UriEntry('is_active',1));	
		//	$decorator->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
		}
	}
	
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	
	if(!isset($_GET['pdate11'])){
	
			$_pdate11=DateFromdmY(date("d.m.Y"))-60*60*24*30;
			$pdate11=date("d.m.Y", $_pdate11);//"01.01.2006";
		
	}else $pdate11 = $_GET['pdate11'];
	
	
	
	if(!isset($_GET['pdate12'])){
			
			$_pdate12=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate12=date("d.m.Y", $_pdate12);//"01.01.2006";	
	}else $pdate12 = $_GET['pdate12'];
	
	
	
	$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate11), SqlEntry::BETWEEN,DateFromdmY($pdate12)+(60*60*24-1)));
	$decorator->AddEntry(new UriEntry('pdate11',$pdate11));
	$decorator->AddEntry(new UriEntry('pdate12',$pdate12));
	
	
	$decorator2->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate11), SqlEntry::BETWEEN,DateFromdmY($pdate12)+(60*60*24-1)));
	$decorator2->AddEntry(new UriEntry('pdate11',$pdate11));
	$decorator2->AddEntry(new UriEntry('pdate12',$pdate12));
	
	if(isset($_GET['user'])){
		$decorator->AddEntry(new SqlEntry('login',SecStr($_GET['user']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('user',$_GET['user']));
		
		$decorator2->AddEntry(new SqlEntry('s.login',SecStr($_GET['user']), SqlEntry::LIKE));
		$decorator2->AddEntry(new UriEntry('user_subj_login',$_GET['user']));
		$decorator2->AddEntry(new UriEntry('user',$_GET['user']));
	}else{
		$decorator->AddEntry(new SqlEntry('login','SSS', SqlEntry::E));
		$decorator->AddEntry(new UriEntry('user',''));
		
		$decorator2->AddEntry(new SqlEntry('s.login','SSS', SqlEntry::LIKE));
		$decorator2->AddEntry(new UriEntry('user_subj_login',''));
		$decorator2->AddEntry(new UriEntry('user',''));
	}
	

	
	
	if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
		$decorator2->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
		$decorator2->AddEntry(new UriEntry('description',$_GET['description']));
	}
	
	if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
		$decorator2->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
		$decorator2->AddEntry(new UriEntry('object_id',$_GET['object_id']));
	}
	
	if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
		$decorator2->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
		$decorator2->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
	}
	
	if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
		$decorator2->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
		$decorator2->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
	}
	
	if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
		$decorator2->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
		$decorator2->AddEntry(new UriEntry('ip',$_GET['ip']));
	}
	
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator2->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator2->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator2->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator2->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator2->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator2->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator2->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator2->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator2->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator2->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator2->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator2->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
		break;	
		case 12:
			$decorator2->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator2->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
		break;	
		default:
			$decorator2->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator2->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
	
	$decorator2->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator2->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	
	
	
	if($print==0) $llg=$log->ShowLog('ua/ua'.$print_add.'.html',$decorator,$decorator2,$from,$to_page);
	else $llg=$log->ShowLog('ua/ua'.$print_add.'.html',$decorator,$decorator2,$from,100000);
	
	
	$sm->assign('log',$llg);
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$content=$sm->fetch('ua/users_activity_page'.$print_add.'.html');
	
		
	
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