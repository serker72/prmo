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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');

require_once('classes/posdimitem.php');
require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');
require_once('classes/sectorgroup.php');

require_once('classes/user_s_item.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/billnotesgroup.php');
require_once('classes/billgroup.php');

require_once('classes/billnotesitem.php');
require_once('classes/acc_notesitem.php');

require_once('classes/billcreator.php');


require_once('classes/invcalcgroup.php');
require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

require_once('classes/propisun.php');


require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/pay_in_group.php');
require_once('classes/pay_in_item.php');

require_once('classes/komplitem.php');

require_once('classes/cashgroup.php');

require_once('classes/cash_bill_position_group.php');

require_once('classes/acc_item.php');

require_once('classes/phpmailer/class.phpmailer.php');

require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/usercontactdataitem.php');
require_once('classes/useritem.php');

require_once('classes/payforaccgroup.php');



require_once('classes/user_s_group.php');


require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');
 

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/trust_notesgroup.php');
require_once('classes/trust_notesitem.php');


require_once('classes/trust_group.php');
require_once('classes/trust_item.php');

require_once('classes/trust_positem.php');


require_once('classes/bill_in_item.php');
require_once('classes/bill_in_positem.php');
require_once('classes/bill_in_posgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');


require_once('classes/paygroup.php');

require_once('classes/propisun.php');
require_once('classes/propis_drob.php');
require_once('classes/propis_drob1.php');
require_once('classes/propis_drob2.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/bdetailsitem.php');

require_once('classes/supcontract_item.php');

require_once('classes/suppliersgroup.php');

require_once('classes/maxformer.php');

require_once('classes/period_checker.php');
require_once('classes/trust_notesitem.php');


require_once('classes/supplier_ruk_item.php');


$_orgitem=new OrgItem;

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new BillItem;
$_acc=new AccItem;
$_bpi=new BillPosItem;
$_position=new PosItem;

$_kp=new KomplItem;

$_sectors=new SectorGroup;
$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new BillCreator;


$_sector=new SectorItem;

$_trust=new TrustItem;


$_supgroup=new SuppliersGroup;
$_opf=new OpfItem;
$_supplier=new SupplierItem;

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['mode'])){
	if(!isset($_POST['mode'])){
		$mode=0;
	}else $mode=abs((int)$_POST['mode']);
}else $mode=abs((int)$_GET['mode']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 
if(isset($_GET['printmodes'])){
	$printmodes=$_GET['printmodes'];
}

//массив режимов печати счета
$_printmodes=explode(',',$printmodes);

//массив режимов печати реализации
$_acceptance_printmodes=array();

//массив печатаемых реализаций
$_acceptances=array();



if(!isset($_GET['document_id'])){
	if(!isset($_POST['document_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}else $id=abs((int)$_POST['document_id']); 
}else $id=abs((int)$_GET['document_id']);
$document_id=$id;  
 
if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);



$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);


 
//режим счета 
if($mode==0){
	$editing_user=$_trust->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
 
}else{
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
}


//отправляемый массив имен файлов (краткое и полное имя)
$filenames_to_send=array();


if($mode==0){
	 
		//работаем со счетом
		
		 $sm1=new SmartyAdm;
			
		  $_bpg=new TrustPosGroup;
		  $bpg=$_bpg->GetItemsByIdPrintArr($editing_user['id']);
		  $bpg_1=$_bpg->GetItemsByIdArr($editing_user['id']);
		  //print_r($bpg);
		  $our_bills=array();
		  foreach($bpg_1 as $k=>$v){
		  	$arr=array('bill_id'=>$v['bill_id'], 'supplier_bill_no'=>$v['supplier_bill_no'], 'supplier_bill_pdate'=>$v['supplier_bill_pdate']);
		  	if(!in_array($arr, $our_bills)) $our_bills[]=$arr;
		  }
		  
		  $_pn=new PropisUn;
		  foreach($bpg as $k=>$v){
			  $bpg[$k]['summa_p']=trim($_pn->propis($v['summa']));
			  
			  $ost=round(($v['summa']-(int)$v['summa'])*1000);
			  
			 /* echo $ost.' ';
			  echo ($ost%100).' ';
			  echo ($ost%10).' ';
			   */
			   
			  
			  if($ost>0){
				  $ostt=$ost;
				 $class=new PropisDrob2;
				if(($ost%10)==0){
					  //echo 'sotye';
					  $ostt=floor($ost/10);
					  $class=new PropisDrob1;
				}
				
				if(($ost%100)==0){
					$class= new PropisDrob;
					$ostt=floor($ost/100);
					//echo 'десятые';
				}
				
				if((($ost%100)!=0)&&(($ost%10)!=0)){
					$class=new PropisDrob2;
				
					//echo 'tysachnye';
				}
				$bpg[$k]['summa_p'].=' и '.trim($class->propis($ostt));
				
			  }
			  
			  
		  }
		  $sm1->assign('positions',$bpg);
		  $sm1->assign('positions1',$bpg_1);
		  $sm1->assign('our_bills',$our_bills);
		  
		  	  //руководитель, гл.бух.
	//добавим подписи, печати
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['pdate']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['pdate']), 2);
		
		$orgitem['chief']=$sri_1['fio'];
		$orgitem['print_sign_dir']=$sri_1['sign'];
		
		$orgitem['main_accountant']=$sri_2['fio'];
		$orgitem['print_sign_buh']=$sri_2['sign'];
		  
		  $sm1->assign('org', $orgitem); //$orgitem['name']);
		  $sm1->assign('order',$editing_user);
		  
		  $_si=new SupplierItem;
		  $si=$_si->GetItemById($bill['supplier_id']);
		  
		  
		  $_opf_sup=new OpfItem;
		  $opfsup=$_opf_sup->getItemById($si['opf_id']);
		  
		  $letter=array();
		  $letter['id']=$editing_user['id'];
		   $letter['code']=$bill['code'];
		   
		    $letter['given_no']=$editing_user['given_no'];
		   $letter['supplier_bill_no']=$bill['supplier_bill_no'];
		  $letter['pdate']=date("d.m.Y",$editing_user['pdate']);
		  
		  
		  $letter['srok']=date("d.m.Y",$editing_user['pdate']+60*60*24*30);
		  
		  $letter['supplier_bill_pdate']=date("d.m.Y",$bill['supplier_bill_pdate']);
		  
		  $_user_s=new UserSItem;
		  $user_s=$_user_s->getitembyid($editing_user['user_id']);
		  
		  $letter['fio']=$user_s['name_s'];
		
		  /*$letter['supplier_name']=$opfsup['name'].' "'.$si['full_name'].'"';
		  $letter['organization']=$opfitem['name'].' "'.eregi_replace('Торговый Дом Строительная Ярмарка', 'ТД "Строительная Ярмарка',$orgitem['full_name']).'", ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  */
		  
		  if(strlen($si['print_name'])>0) $letter['supplier_name']=$opfsup['name'].' '.$si['print_name'].'';
		  else $letter['supplier_name']=$opfsup['name'].' "'.$si['full_name'].'"';
		  
		  
		  if(strlen($si['print_name'])>0)  $letter['organization']=$opfitem['name'].' '.($orgitem['print_name']).', ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  else   $letter['organization']=$opfitem['name'].' "'.($orgitem['full_name']).'", ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  
		  $_bd=new BDetailsItem;
		  $bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id'],'is_basic'=>1));
		  
		  if($bank===false){
			  $bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id']));
		  }
		  
		  $letter['rs']=$bank['rs'];
		  $letter['bank']=$bank['bank'];
		  
		  $letter['city']=$bank['city'];
		  $letter['bik']=$bank['bik'];
		  
		  $letter['ks']=$bank['ks'];
		  
		 
		  
		  $letter['pasp_ser']=$user_s['pasp_ser'];
		  $letter['pasp_no']=$user_s['pasp_no'];
		  $letter['pasp_kem']=$user_s['pasp_kem'];
		  $letter['pasp_kogda']=$user_s['pasp_kogda'];
		  
		  
		  $letter['chief']=$orgitem['chief'];
		  $letter['main_accountant']=$orgitem['main_accountant'];
		  
		  
		  $sm1->assign('letter',$letter);
		
		
		
		
		foreach($_printmodes as $k=>$v){
			if(($v==0)||($v==1)){
				//echo $content;
				
				if($v==1) $sm1->assign('printmode',2); //выводим печать
				$content=$sm1->fetch('letter.html');
				$tmp='trust_'.$v.'_'.time();
			
				$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
				fputs($f, $content);
				fclose($f);
				
				$cd = "cd ".ABSPATH.'/tmp';
				exec($cd);
				
				
				$comand = "wkhtmltopdf-i386 --page-size A4 --orientation Portrait --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
				
			 
			 
		 
				exec($comand);
		
			/*header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="Доверенность_'.$editing_user['given_no'].'.pdf'.'"');
			readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
			exit();
			*/
			
			/*unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');*/
				unlink(ABSPATH.'/tmp/'.$tmp.'.html');
				unlink(ABSPATH.'/tmp/'.$tmp1.'.html');
				
				//добавим наш файл в массив прикладываемых файлов
				
				$name='Доверенность_'.$editing_user['given_no'].'.pdf';
				if($v==1) $name='Доверенность_'.$editing_user['given_no'].'_с_подписью_печатью.pdf';
				$filenames_to_send[]=array(
					'fullname'=>ABSPATH.'tmp/'."$tmp.pdf",
					'name'=>$name
				);
			}
		}
		
		
}

 
 
 

/*
echo '<pre>';	 
var_dump($filenames_to_send);
echo '</pre>';
*/
 

//рассылаем письма
if((count($_addresses)>0)&&(count($filenames_to_send)>0)){
foreach($_addresses as $a_k=>$address){
	$_filenames=array();
	foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
	
	$org=$_orgitem->Getitembyid($result['org_id']);
	$opf=$_opf->getitembyid($org['opf_id']);	
	
	$mail = new PHPMailer();
	/*$body = "<div>Уважаемый контрагент!</div>
<div>&nbsp;</div>
<div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div>
<div>&nbsp;</div>

<div>Отправляем Вам следующие документы: ".implode(', ',$_filenames).".</div>
<div>&nbsp;</div>
<div>Благодарим Вас за то, что Вы обратились к нам!</div>
<div>С уважением, компания ".$opf['name'].' '.$org['full_name']." .</div>

"; */
	
	//найти ФИО по адресу эл.почты...
	//1) в карте к-та
	$has_cont=false; $user_name='контрагент';
	$_sdi=new SupplierContactDataItem;
	$sdi=$_sdi->GetItemByFields(array('value'=>$address));
	if($sdi!==false){
		$_sci=new SupplierContactItem;
		$sci=$_sci->GetItemById($sdi['contact_id']);
		if($sci!==false){
			$user_name=$sci['name'];
			$has_cont=true;
		}
	}
	
	//2) в карте сотр-ка
	if(!$has_cont){
		$_uci=new UserContactDataItem;
		$_ui=new UserItem;
		$uci=$_uci->GetItemByFields(array('value'=>$address));
		$ui=$_ui->GetItemById($uci['user_id']);
		if($ui!==false) $user_name=$ui['name_s'];
		
	}
	
	
	
	
	$body=$org['feedback_txt'];
	$body=str_replace('%{$contact_name}%', $user_name,$body);
	$body=str_replace('%{$docs}%', implode(', ',$_filenames),  $body);
	$body=str_replace('%{$company_name}%', $org['full_name'],  $body);
	$body=str_replace('%{$opf_name}%', $opf['name'],  $body);
	
	

	$mail->SetFrom($org['feedback_email'], $opf['name'].' '.$org['full_name']);

	$mail->AddAddress(trim($address),  $address);

	$mail->Subject = "документы для Вас!"; 
	$mail->Body=$body;
	foreach($filenames_to_send as $k=>$v) $mail->AddAttachment($v['fullname'], $v['name']);  
	$mail->CharSet = "windows-1251";
	$mail->IsHTML(true);  
	
	if(!$mail->Send())
	{
		echo "Ошибка отправки письма: " . $mail->ErrorInfo;
		//var_dump($org);
	}
	else 
	{
		 echo "Письмо отправлено!";
	}
	
	
	
	
	
}
if($mode==0){
		$log->PutEntry($result['id'],'отправил pdf-документы доверенности на электронную почту',NULL,284,NULL,'Документы: '.implode(', ',$_filenames).'; получатели: '.implode(', ',$_addresses),$document_id);
		$_bni=new TrustNotesItem;
		
		$notes_params=array();
		$notes_params['is_auto']=1;
		$notes_params['user_id']=$document_id;
		$notes_params['pdate']=time();
		$notes_params['posted_user_id']=$result['id'];
		
		$notes_params['note']='Автоматическое примечание: Документы: '.implode(', ',$_filenames).' были отправлены на электронную почту  пользователем '.SecStr($result['name_s'].' '.$result['login']).'; получатели: '.implode(', ',$_addresses).'. ';
		
		$_bni->Add($notes_params);
		
	 
		//echo 'zzzzzzzzzzzz';	
	}
	

}

	 
//очистка
foreach($filenames_to_send as $k=>$v){
	unlink($v['fullname']);	
}

/*echo '<script type="text/javascript"> alert("PDF-документы были отправлены на адреса электроннной почты: '.$_GET['addresses'].'"); window.close();</script>';	*/


			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>PDF-документы были отправлены на следующие адреса:</strong></div>';
			$txt.='<ul>';
			
			foreach($_addresses as $k=>$email){
				$txt.='<li>'.$email.'</li>';
			}
			$txt.='</ul>';
			
			if(count($_filenames)>0){
				$txt.='<div>&nbsp;</div>';
				$txt.='<div><strong>Были приложены следующие файлы:</strong></div>';
				$txt.='<ul>';
				foreach($_filenames as $k=>$file){
					$txt.='<li>'.$file.'</li>';
				}
				$txt.='</ul>';
			}
			
		 
			//$txt.='<p></p>';			
			
			$sm->assign('message', $txt);
			
			$sm->display('page_email.html');
			
?>