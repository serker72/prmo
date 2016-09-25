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
require_once('classes/filemessagegroup.php');
 

require_once('classes/userpaspgroup.php');
require_once('classes/wffilegroup.php');
require_once('classes/trustfilegroup.php');
 

require_once('classes/payfilegroup.php');
 
require_once('classes/filegroup.php');
require_once('classes/billfilegroup.php');
require_once('classes/accfilegroup.php');



require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');
require_once('classes/calendar.php');
require_once('classes/last_docs.php');
require_once('classes/sched.class.php');
 

 $au=new AuthUser();
$result=$au->Auth();
/*if(($result===NULL)){
	 
	header("Location: http://www.gydex.ru");
	die();		
} */

//очистим старые сессии
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",SITETITLE);

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

?>

<?
//var_dump($_SESSION);
//var_dump($au->GetRightsTable());


$smarty = new SmartyAdm;
 /*
$content='
<h2>Уважаемые коллеги! </h2>
<br />
<br />
<strong>С целью улучшения работы программы "Электронный
менеджер" проводятся профилактические работы. <br />
Расчетное время
запуска программы: 0:00 3 марта 2014 г.</strong>

';

$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
die();
 */

$test_profile=$au->GetProfile();
if($test_profile===NULL){
	$smarty = new SmartyAdm;
	
	if(isset($_GET['error_code'])){
		$smarty->assign('error_message',$au->ShowError((int)$_GET['error_code']));	
	}
	$smarty->display('auth_form.html');
	unset($smarty);
	
}elseif(!$au->CheckOrgId()){
	
	//не определена организация
	
	
	
	//echo 'grp';
	if(isset($_GET['org_id'])){
		$org_id=abs((int)$_GET['org_id']);
	}else
		$org_id=0;
	
	$fa=new OrgsGroup();
	$items=$fa->GetItemsByUserIdArr($test_profile['id'], $org_id);
	
	$smarty = new SmartyAdm;
	//$content='';
	
	$smarty->assign('items',$items);
	
	if(isset($_GET['error_code'])){
		$smarty->assign('error_message',$_GET['error_code']);	
	}
	
	$smarty->display('login_addresses.html');
	
}else{
	
	
	//проверить, не устарел ли пароль
	if($result['password_expired']==1){
		?>
        
        <script type="text/javascript">
$(function(){
  $("#tabs").tabs();
});
</script>

         <div class="content">

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Смена пароля</a>
    </li>
  </ul>
	 <div id="tabs-1">


		<br />
<br />
<br />
<br />


		<h1>Уважаемый(ая) <?=$result['name_s']?>!</h1>
<br />
<br />
<br />
		
        <p >
        <strong>В целях безопасности работы в программе "<?=SITETITLE?>" проводится обязательная смена паролей пользователей. <br />
		Просим Вас сменить пароль.</strong>
        </p><br />

        <p >
		<form action="noscripts.php" method="post" id="chPform">
        
        <label for="new_password">Ваш новый пароль:</label><br />
        <input type="text" size="20" maxlength="255" name="new_password" id="new_password" /><br />

        <small><em>минимальная длина пароля 6 символов; <br />
допустимые символы - латинские буквы и цифры</em></small><br />
        
        
		<br />
		<input type="submit" value="Сменить пароль" name="chP" />
        <p />
        <a href="/login.html?doOut">Выйти из программы</a>
		
        </form>
        <script type="text/javascript">
		$(function(){
			
			$("#chPform").attr("action","_period_change.php");
			
			function ValData(){
				var can_submit=true;
				
				
				  
				  if($("#new_password").val().length<6) can_submit=can_submit&&false;
				  
				 reg1=new RegExp("^[A-Za-z0-9]+$");
				 
				 
				  if($("#new_password").val().search(reg1)==-1) can_submit=can_submit&&false;
				  
				  if(!can_submit){
					  alert("Пароль должен быть 6 символов и длиннее и состоять только из латинских букв и цифр!");	
					  $("#new_password").focus();
				  }	
				  
				  
				  if(can_submit){
					//проверить логин  
					
					$.ajax({
						  async: false,
						  url: "/js/_period_change.php",
						  type: "POST",
						  data:{
							  "action":"check_password",
							  value: $("#new_password").val()
						  },
						  beforeSend: function(){
								
						  },
						  success: function(data){
							if(data!=0){
							   
							   alert("Невозможно сменить пароль на заданный Вами. Причина: "+data+"."); 
							   can_submit=false;
							}
						  },
						  error: function(xhr, status, mm){
							  
							  alert("Ошибка при проверке пароля. Пожалуйста, попытайтесь сменить пароль снова.");
							  can_submit=false;	
						  }	 
					  });
				  }
				  
				
				
				return can_submit;
			}
			
			
			$("#chPform").bind("submit",function(){
				
				return ValData();	
			});
			
			
		});
		</script>
        </p>
        </div>
        </div>
        </div>
	<?
	
	
	
	
	
	}else{
	  
	 
	  include('inc/menu.php');
	  
	  
	  //демонстрация стартовой страницы
	  $smarty = new SmartyAdm;
	  $fmg=new FileMessageGroup;
	  
	  
	 // print_r($result);
	  
	  
	  $usg=new UsersSGroup;
	  
  
	 // $fmg->ClearLostFiles();
	  
	  
	 
	 /* 
	  $usg->AutoBirthdayMessagesMarkers(0, false, $result);
	  $usg->AutoBirthdayMessagesMarkers(1, false, $result);
	  
	  $usg->AutoBirthdayMessagesMarkers(3, false, $result);
	  
	  $usg->AutoBirthdayMessagesMarkers(7, false, $result);
	  */
	  
	  $sm1=new SmartyAdm;
	  
	 
		//доступ к новостям
		$sm1->assign('has_news',$au->user_rights->CheckAccess('w',1118));		
		  
	

	 
	 
	  //calendar
	   $c= new Calendar();
			if(!isset($_GET['pdate'])) {
				$pdate=date('Y-m-d');
			}else $pdate=$_GET['pdate'];
			

	  $calendar= $c->Draw($pdate,'index.php','pdate','', $pdate,0);		  
		  
	  $sm1->assign('calendar', $calendar);
	   $sm1->assign('pdate', $pdate);
	  
	 
	 
	  	
		
	  //планировщик
	  $sm1->assign('has_plan', $au->user_rights->CheckAccess('w',903));
	  if($au->user_rights->CheckAccess('w',903)){
		  $_plans=new Sched_Group;
			
			 $decorator=new DBDecorator;
		  
		 // $decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		  
		  $decorator->AddEntry(new SqlEntry('p.status_id',3, SqlEntry::NE));
		  $decorator->AddEntry(new SqlEntry('p.status_id',1, SqlEntry::NE));
		  $decorator->AddEntry(new SqlEntry('p.status_id',18, SqlEntry::NE));
		
		 /* $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		  $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		  */
		  
		  if(!isset($_GET['sortmode'.$prefix])){
				$sortmode=-1;	
			}else{
				$sortmode=abs((int)$_GET['sortmode'.$prefix]);
			}
			
				
				
			switch($sortmode){
				case 0:
					 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
		 			 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
				break;
				case 1:
					 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		 			 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
				break;
				
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::DESC));
			
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('kind.name',SqlOrdEntry::ASC));
		
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('s.name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('s.name',SqlOrdEntry::ASC));
				break;
				
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
					$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
					$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::ASC));
				break;
				
				
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
				break;
				
				
				
				default:
						
					 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		 			 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
				
				break;	
				
			}
			 
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
		  
		
		 
		  
		  
		   if(!isset($_GET['sched_begin'])){
		
					$_pdate1=DateFromdmY(date("d.m.Y"));  
					$pdate1=date("d.m.Y", $_pdate1); 
				
			}else $pdate1 = $_GET['sched_begin'];
			
			
			
			if(!isset($_GET['sched_end'])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
					$pdate2=date("d.m.Y", $_pdate2); 
			}else $pdate2 = $_GET['sched_end'];
		
		
			
		  
		    if(!isset($_GET['sched_period'])){
			  $sched_period=1;	
		  }else{
			  $sched_period=abs((int)$_GET['sched_period']);
		  } 
		  
		  $decorator->AddEntry(new UriEntry('sched_period',$sched_period));
		  $decorator->AddEntry(new UriEntry('sched_end',$pdate2));
		  $decorator->AddEntry(new UriEntry('sched_begin',$pdate1));
		  
		  $sm1->assign('sched_period',$sched_period);
		   $sm1->assign('sched_begin',$pdate1);
				$sm1->assign('sched_end',$pdate2);
		  
		  
		  
		  //выбрать виды действий
		  $kinds=array();
			$cou_stat=0;   
			if(isset($_GET['kinds'])&&is_array($_GET['kinds'])) $cou_stat=count($_GET['kinds']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $kinds=$_GET['kinds'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_kinds',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_kinds',1));
				}else{
				
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_id_'.$v,1));
					$decorator->AddEntry(new SqlEntry('p.kind_id', NULL, SqlEntry::IN_VALUES, NULL,$kinds));	
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kinds[]',$v));
					
			
				}
			} 
		  
		  
		  //выбрать группы статусов
		  $states=array();
			$cou_stat=0;   
			if(isset($_GET['states'])&&is_array($_GET['states'])) $cou_stat=count($_GET['states']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $states=$_GET['states'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_states',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_states',1));
				}else{
				
					foreach($states as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
					foreach($states as $k=>$v) $decorator->AddEntry(new UriEntry('states[]',$v));
					
					$dec_statuses=array();
					foreach($states as $k=>$v) {
						if($v==1){
							//запланированные
							$dec_statuses[]=23; $dec_statuses[]=24; $dec_statuses[]=25; $dec_statuses[]=26; $dec_statuses[]=2; $dec_statuses[]=22;
						}
						if($v==2){
							//выполненные
							$dec_statuses[]=10;
						}
					}
					
					$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$dec_statuses));		
					
			
					
				}
			} 
		  
		  switch($sched_period){
			  case 1:
				 $decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d') , SqlEntry::BETWEEN, date('Y-m-d')));  
			
			  break;
			  
			  case 2:
			 	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY(date('d.m.Y'))+24*60*60  ) , SqlEntry::BETWEEN, date('Y-m-d', DateFromdmY(date('d.m.Y'))+24*60*60  ) ));  	 
			  break;
			  case 3:
			 	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY(date('d.m.Y'))-24*60*60  ) , SqlEntry::BETWEEN, date('Y-m-d', DateFromdmY(date('d.m.Y'))-24*60*60  ) ));  	 
			  break;
			  
			  case 4:
			 	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY(date('d.m.Y'))  ) , SqlEntry::BETWEEN, date('Y-m-d', DateFromdmY(date('d.m.Y'))+7*24*60*60  ) ));  	 
			  break;
			  
			  case 5:
			 	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY(date('d.m.Y'))  ) , SqlEntry::BETWEEN, date('Y-m-d', DateFromdmY(date('d.m.Y'))+30*24*60*60  ) ));  	 
			  break;
			  
			  case 6:
			 	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)) , SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))  ));  	 
				
				
			  break;
			  	 
		  }
		  			  
		  $fields=$decorator->GetUris();
		  foreach($fields as $k=>$v){
				$sm1->assign($v->GetName(),$v->GetValue());	
		  }
		  $link=$decorator->GenFltUri('&');
		//echo $link;
		$link='index.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		 
		$sm1->assign('link',$link);
		  
		  
		  $sm1->assign('planned', $_plans->ShowPosIndex($decorator, $result['id']));
			
	  }else{
		  //$sm1->assign('planned', '<h1>GYDEX. В работе!</h1>');	  
	  }
		
		
		
		
		
		
		
		
		
		
		
	//новая позиция каталога
	if($au->user_rights->CheckAccess('w',67)){
		$sm1->assign('has_new_position',true);	
	}
	
	if($au->user_rights->CheckAccess('w',70)){
		$sm1->assign('has_new_tovgr',true);	
	}
	
	if($au->user_rights->CheckAccess('w',87)){
		$sm1->assign('has_new_supplier',true);	
	}
	
	if($au->user_rights->CheckAccess('w',81)){
			$sm1->assign('has_new_komplekt',true);
			
	}
	
	if($au->user_rights->CheckAccess('w',268)){
			$sm1->assign('has_new_pay',true);
			
	}
	
	if($au->user_rights->CheckAccess('w',679)){
			$sm1->assign('has_new_pay_in',true);
			
	}
	
	if($au->user_rights->CheckAccess('w',904)){
 			$sm1->assign('has_new_plan',true);	
 		}  
	  
	 
	  $_last_docs=new LastDocs;
	  
	  $_last_docs->AddDoc( new LastDocs_Kompl(array(81, 82, 282),  NULL, 'ed_komplekt.php', 'action=1', 'id'));
	  
	  $_last_docs->AddDoc( new LastDocs_Bill(array(92, 93, 283), 'and description="открыл карту исходящего счета" ', 'ed_bill.php', 'action=1', 'id'));
	  
	  $_last_docs->AddDoc( new LastDocs_BillIn(array( 613, 625), 'and description="открыл карту входящего счета"  ', 'ed_bill_in.php', 'action=1', 'id'));
	  
	  $_last_docs->AddDoc( new LastDocs_AccIn(array( 664, 673), ' and description="открыл карту поступления"  ', 'ed_acc_in.php', 'action=1', 'id'));
	  
	  $_last_docs->AddDoc( new LastDocs_Acc(array( 235, 286), ' and description="открыл карту реализации"  ', 'ed_acc.php', 'action=1', 'id'));
	  
	   $_last_docs->AddDoc( new LastDocs_PayIn(array( 683, 693), ' and description="открыл карту входящей оплаты"  ', 'ed_pay_in.php', 'action=1', 'id'));
	   
	   $_last_docs->AddDoc( new LastDocs_Pay(array( 272, 281), ' and description="открыл карту исходящей оплаты"  ', 'ed_pay.php', 'action=1', 'id')); 
	   
	   $_last_docs->AddDoc( new LastDocs_Cash(array( 836, 848), ' and description="открыл карту расхода наличных"  ', 'ed_cash.php', 'action=1', 'id'));  
	   
	   
	   $_last_docs->AddDoc( new LastDocs_CashIn(array( 886, 898), ' and description="открыл карту прихода наличных"  ', 'ed_cash_in.php', 'action=1', 'id'));  
	   
	  $_last_docs->AddDoc( new LastDocs_Trust(array( 208, 284), ' and description="открыл карту доверенности"  ', 'ed_trust.php', 'action=1', 'id'));  
	  
	  $_last_docs->AddDoc( new LastDocs_Inv(array( 322), ' and description="открыл карту инвентаризационного акта"  ', 'ed_inv.php', 'action=1', 'id'));   
	  
	  
	   $_last_docs->AddDoc( new LastDocs_InvCalc(array( 451), ' and description="открыл карту инвентаризационного акта"  ', 'ed_invcalc.php', 'action=1', 'id'));   
	   
	  
	  $sm1->assign('last_docs', $_last_docs->GetData($result['id']));
	  
	  
	   //права для графиков
	  $sm1->assign('can_view_kompl',   $au->user_rights->CheckAccess('w',80));
	  
	  $sm1->assign('can_view_acc',   $au->user_rights->CheckAccess('w',200));
	  
	  $sm1->assign('can_view_bill',   $au->user_rights->CheckAccess('w',97));
	  
	  $sm1->assign('can_view_bill_in',   $au->user_rights->CheckAccess('w',606));
	  
	  
	  //расширенная статистика сотрудника
	  $sm1->assign('can_view_kompl_extended',   $au->user_rights->CheckAccess('w',359)); 
	  
	  $sm1->assign('can_view_acc_extended',   $au->user_rights->CheckAccess('w',240)); 
	  
	   $sm1->assign('can_view_bill_extended',   $au->user_rights->CheckAccess('w',95));
	  
	  $sm1->assign('can_view_bill_in_extended',   $au->user_rights->CheckAccess('w',620));
	  
	   $sm1->assign('can_view_debts',   $au->user_rights->CheckAccess('w',89));
	   
	   
	   //статистика адм-ра
	   
	    $sm1->assign('can_view_pms',   $au->user_rights->CheckAccess('w',1));
		
		 $sm1->assign('can_view_pribyl',   $au->user_rights->CheckAccess('w',1));
	  
	 
	  
	 
	  	  
	  	  
	  $content=$sm1->fetch('index.html');
	  //phpinfo();
	  
	  
	  $smarty->assign('has_border',true);
	   
	  $smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	  $smarty->assign('content',$content);
	  $smarty->display('page.html');
	  unset($smarty);
	  
	  
	  ?>
      <script type="text/javascript">
	  $(function(){
		 /* $.ajax({
			  async: true,
			  url: "/js/checknew.php",
			  type: "GET",
			  data:{
				  "action":"do_holyday_messages"
			  },
			  beforeSend: function(){
				
			  },
			  success: function(data){
				
			  },
			  error: function(xhr, status){
				
			  }	 
			});*/
			
			
			$.ajax({
			  async: true,
			  url: "/js/old_mess_del.php",
			  type: "GET",
			  data:{
				  action: "try_del",
				  kind: 0	
			  },
			  beforeSend: function() {
			   
			  },
			  success: function(data) {
				  //alert(data);
			  },
			  error: function() {
			   // alert("");
			  }
		  });
	  });
	  </script>
      <?
	
	}
}



/*var_dump($au->GetProfile());
echo '<p>';
var_dump($_SESSION);
echo '<p>';
print_r($_COOKIE);
echo '<p>';*/

?>

<?
$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>