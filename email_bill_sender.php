<canvas></canvas><?
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

require_once('classes/supplier_ruk_item.php');

+require_once('classes/suppliercontactitem.php');
+require_once('classes/suppliercontactdataitem.php');

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


 
//режим счета 
if($mode==0){
	$editing_user=$_bill->GetItemByFields(array('id'=>$id, 'is_incoming'=>0));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);

//режим реализации
}elseif($mode==2){
	 
	$editing_user=$_acc->GetItemByFields(array('id'=>$id, 'is_incoming'=>0));
	 
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	
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
		
		
		//даты
		$editing_user['pdate_unf']=$editing_user['pdate'];
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		$m='';
		switch(date('m',$editing_user['pdate_unf'])){
			case 1:
				$m='января';
			break;
			case 2:
			$m='февраля';
			break;
			case 3:
			$m='марта';
			break;
			case 4:
			$m='апреля';
			break;
			case 5:
			$m='мая';
			break;
			case 6:
			$m='июня';
			break;
			case 7:
			$m='июля';
			break;
			case 8:
			$m='августа';
			break;
			case 9:
			$m='сентября';
			break;
			case 10:
			$m='октября';
			break;
			case 11:
			$m='ноября';
			break;
			case 12:
			$m='декабря';
			break;	
			
		}
		$editing_user['pdate_print']=date('d ', $editing_user['pdate_unf']).$m.date(' Y',$editing_user['pdate_unf']);
		
		 
		//реквизиты компании
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetBasic($result['org_id']);
		$editing_user['bd_print']=$bdi;
		
		//
		$_org=new OrgItem;
		$org=$_org->GetItemById($result['org_id']);
		
		//добавим подписи, печати
		$_sri=new SupplierRukItem;
			$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['pdate_unf']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['pdate_unf']), 2);
		
		$org['chief']=$sri_1['fio'];
		$org['print_sign_dir']=$sri_1['sign'];
		
		$org['main_accountant']=$sri_2['fio'];
		$org['print_sign_buh']=$sri_2['sign'];
		
		
		//данные для печатной формы - представитель организации и его телефон:
		$_cont=new SupplierContactItem;
		$cont=$_cont->GetItemByFields(array('supplier_id'=>$org['id']));
		$org['cont']=$cont['name'];
		$_phone=new SupplierContactDataItem;
		$phone=$_phone->getitembyfields(array('contact_id'=>$cont['id'], 'kind_id'=>3));
		$org['phone']=$phone['value'];
		
		
		$phone=$_phone->getitembyfields(array('contact_id'=>$cont['id'], 'kind_id'=>5));
		$org['email']=$phone['value'];
		
		
		$editing_user['org']=$org;
		$editing_user['org']['bill_comments']=str_replace('%{$bill_data}%', 'по счету на оплату № '.$editing_user['supplier_bill_no'].' от '.$editing_user['pdate_print'].' ', $editing_user['org']['bill_comments']);
		
		
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($org['opf_id']);
		$editing_user['opf']=$opf;
		
		//поставщик
		$_si=new supplieritem();
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$editing_user['supplier']=$si;
		
		$sopf=$_opf->GetItemById($si['opf_id']);
		$editing_user['si_opf']=$sopf;
		
		
			//грузополучатель
		$si1=$_si->GetItemById($editing_user['ship_supplier_id']);
		$editing_user['ship_supplier']=$si1;
		
		$sopf1=$_opf->GetItemById($si1['opf_id']);
		$editing_user['ship_si_opf']=$sopf1;
		
		
		//реквизиты
		$sbdi=$_bdi->getitembyid($editing_user['bdetails_id']);
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'].' ('.$cu['login'].')';
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		if($editing_user['pdate_shipping_plan']==0) $editing_user['pdate_shipping_plan']='-';
		else $editing_user['pdate_shipping_plan']=date("d.m.Y", $editing_user['pdate_shipping_plan']);
		
		if($editing_user['pdate_payment_contract']==0) $editing_user['pdate_payment_contract']='-';
		else $editing_user['pdate_payment_contract']=date("d.m.Y", $editing_user['pdate_payment_contract']);
		
	
		
		//фактическая дата поставки - увязана с реализации
		$_acg=new AccGroup;
			
		$dec2=new DBDecorator;
		
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$dec2->AddEntry(new UriEntry('is_confirmed_acc',1));
		
		$_acg->SetAuthResult($result);
		$ships=$_acg->ShowPos($id,'bills/fact_dates.html', $dec2, $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96));
		
		$sm1->assign('fact_days',$ships); 	
		
		
		
		//фактическая дата оплаты - увязана с оплатами
		$_pays=new PayInGroup;
		$_pays->SetPagename('ed_bill.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$_pays->SetAuthResult($result);
		$pays=$_pays->ShowPos($editing_user['id'], $editing_user['supplier_id'], 'bills/fact_pays.html', $dec2, $au->user_rights->CheckAccess('w',272), $au->user_rights->CheckAccess('w',279), $au->user_rights->CheckAccess('w',277),   $au->user_rights->CheckAccess('w',96),true,false, $au->user_rights->CheckAccess('w',280), $au->user_rights->CheckAccess('w',278),true, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $total_cost, $_bill->CalcPayed($editing_user['id']));
		
		//добавим также инв. акты
		$_invg=new InvCalcGroup;
		$_invg->SetPageName('ed_bill.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed_inv',1, SqlEntry::E));
		$dec2->AddEntry(new SqlEntry('p.supplier_id',$editing_user['supplier_id'], SqlEntry::E));
		
		$_invg->SetAuthResult($result);
		$pays.=$_invg->ShowPosByBill($editing_user['id'],'bills/fact_invs.html',$dec2,0,10000, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),true,false,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461));
		
			
		$sm1->assign('fact_pays',trim($pays)); 	
		
		
		
		
		
		if($editing_user['supplier_bill_pdate']==0) $editing_user['supplier_bill_pdate']='-';
		else $editing_user['supplier_bill_pdate']=date("d.m.Y", $editing_user['supplier_bill_pdate']);
		
		
		//склады	
		$sectors=$_sectors->GetItemsArr(0,1);
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($sectors as $k=>$v){
			
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		
		$sm1->assign('group_id', $editing_user['sector_id']); 
		
		$sm1->assign('group_ids', $st_ids);
		$sm1->assign('group_names', $st_names);
		$sm1->assign('sectors', $sectors);
		
		
		
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($si['opf_id']); 
		$editing_user['supplier_id_string']=$opfitem['name'].' '.$si['full_name'];
		
		
		/*$supgroup=*/
		$_supgroup->GetItemsForBill('bills/suppliers_list.html', new DBDecorator, false, $supgroup, $result); //GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		
		//реквизиты - получить список по тек. поставщику
		//bdetails
		$_bdg=new BDetailsGroup;
		$bdg=$_bdg->GetItemsByIdArr($editing_user['supplier_id'], $editing_user['bdetails_id']);
		$editing_user['bdetails']=$bdg;
		
		//договор п-ка
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($editing_user['supplier_id'], $editing_user['contract_id'],0);
		$editing_user['condetails']=$scg;
		
		//подставить даты договора
		$_sci=new SupContractItem;
		$sci=$_sci->GetItemById($editing_user['contract_id']);
		$editing_user['contract_no']=$sci['contract_no'];
		$editing_user['contract_pdate']=$sci['contract_pdate'];
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new BillPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		
		//print_r($bpg);
		//for($i=0; $i<1000; $i++) $bpg[]=array('id'=>$i, 'name'=>'test');
		$number_per_page=27;
		if($editing_user['org_id']==33) $number_per_page=39;
		
			$was_last=false; $num_of_pages=0;

		//позиции для печати
				//позиции для печати
				$cter=1;
				foreach($bpg as $k=>$v){
					if($cter==$number_per_page){
						 $bpg[$k]['break_after']=true;
						 $num_of_pages++;
					}
					elseif(($cter>$number_per_page)&&((($cter-$number_per_page)%56)==0)){
						  $bpg[$k]['break_after']=true;
						  $num_of_pages++;
					}
					
					if($editing_user['org_id']==33){
						//если это последний лист и позиция >40й, следовательно поставить разрыв листа
						//как понять, что это последний лист???
						
				 
						
						//намбер пер пэдж - 1 страница, 56 - на страницу всего
						if(($cter>$number_per_page)){
							
						 	
						  $was_on=$number_per_page+($num_of_pages-1)*56;
						  
						//  echo $was_on;
						  if((count($bpg)-$was_on)<=56){
							 
							  //$co=count($bpg)-$cter;
							  if(($cter-$was_on)>50){
							  //if
								  if(!$was_last) {
									  $bpg[$k]['break_after']=true;
									  $was_last=true;
								  }
							  }
						  }
						}
					}
					
					$cter++;
				}
			
		
		$sm1->assign('positions',$bpg);


		
		//стоимость и итого
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
		
		require_once('classes/propis.php');
		require_once('classes/propis1.php');
		require_once('classes/propis_cifr_kop.php');
		$_pn=new PropisUn(); $_pp=new Propis; $_pp1=new Propis1; $_pck=new PropisCifrKop;
		
		$summa_propis=trim($_pp->propis(floor($total_cost)));
	 
		
		$summa_propis= mb_convert_case(substr($summa_propis, 0, 1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 1,strlen($summa_propis));
		
		 
		$sm1->assign('total_cost_rub_propis', $summa_propis);
		//$sm1->assign('total_cost_kop_propis',  $_pck->propis(round(100*((float)$total_cost-floor($total_cost)))));
		
		
		if($editing_user['org_id']==33) {
			$sm1->assign('total_cost_kop_propis', $_pp1->propis(round(100*((float)$total_cost-floor((float)$total_cost)))   /*' '. $_pck->propis(round(100*((float)$total_cost-floor((float)$total_cost))) */  ) );	
		}else{
			$sm1->assign('total_cost_kop_propis', ' '. $_pck->propis(round(100*((float)$total_cost-floor((float)$total_cost)))  ) );
		}
		$sm1->assign('printmode',$printmode);
		
		
		
		//коррекция +/-
		$sm1->assign('can_modify_pms',($editing_user['is_confirmed_price']==1)&&
		($editing_user['is_confirmed_shipping']==1)&&
		$_bill->HasShsorAccs($editing_user['id'])&&
		$au->user_rights->CheckAccess('w',523));
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',94);
		if(!$au->user_rights->CheckAccess('w',94)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',131);
			if(!$au->user_rights->CheckAccess('w',131)) $reason='недостаточно прав для данной операции';
		
		
		
		
		//$sm1->assign('org',$orgitem['name']);
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		//костыль для замены ТД СЯ на ТД "СЯ
		$orgitem_fact=$orgitem;
		$orgitem_fact['full_name']=eregi_replace('Торговый Дом Строительная Ярмарка', 'Торговый Дом "Строительная Ярмарка',$orgitem_fact['full_name']);
		$sm1->assign('print_org_fact' ,$orgitem_fact);
		
		$sm1->assign('print_org_opf' ,$opf);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		//если у счета утверждены цены - просматривать можно при наличии прав 365 (выдача +/- в счете)
		//в других статусах: 130 (работа с +/-)
		if($editing_user['is_confirmed_price']==1){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
		
		
		$sm1->assign('not_changed_pos',true);
		
		//есть ли реализации, расп.
		$sm1->assign('has_rasp_or_post',$_bill->HasR($editing_user['id']));
		$sm1->assign('rasp_or_post_list',$_bill->HasRList($editing_user['id']));
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_active'=>1));
		$supgroup=$_supgroup->GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('pos',$supgroup);
		
	
		
		//реестр утв. отгр. исход. счетов (для включения в доставку, экспедирование)
		//bills_to_cash
		$_bills=new BillGroup;
		$dec_bills=new DBDecorator();
		
		$dec_bills->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$dec_bills->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		$dec_bills->AddEntry(new SqlEntry('p.is_confirmed_shipping',1, SqlEntry::E));
		
		$bills_to_cash1=$_bills->ShowPos('bills/bills_list.html', 
			$dec_bills,
			0,
			100000, 
			false, 
			false, 
			false, 
			'', 
			false,
			false, 
			true, 
			false, 
			false,
			NULL,
			NULL, 
			false, 
			false, 
			false, 
		$bills_to_cash);
		$sm1->assign('bills_to_cash',$bills_to_cash);
		
		
		//уже готовые (утв.) доставки, экспед-ия по счету
		$_cg1=new CashGroup;
		
		$dec_c1=new DBDecorator();
		$dec_c1->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		
		$dec_c1->AddEntry(new SqlEntry('p.id','select distinct cash_id from cash_to_bill where bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
		
		$dec_c1->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));	
		$dec_c1->AddEntry(new SqlEntry('p.kind_id',2, SqlEntry::E));
		
		$cash1=$_cg1->ShowAllPos('cash/cash_list.html', $dec_c1, 
			
			$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
			$au->user_rights->CheckAccess('w',846) ,0, 1000,
			$au->user_rights->CheckAccess('w',842) ,  
			false ,true,false, 
			$au->user_rights->CheckAccess('w',847) ,  
			$au->user_rights->CheckAccess('w',843),
			
			$au->user_rights->CheckAccess('w',835),
			$au->user_rights->CheckAccess('w',844),
			$au->user_rights->CheckAccess('w',845),
			$dostavki
		
		);
		$sm1->assign('dostavki',$dostavki);
		//также нужны другие смежные счета
		$sm1->assign('another_nested_d', $_cg1->GetNestedBills($editing_user['id'], 2));
		
		
		//exped
		$dec_c1=new DBDecorator();
		$dec_c1->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		
		$dec_c1->AddEntry(new SqlEntry('p.id','select distinct cash_id from cash_to_bill where bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
		
		$dec_c1->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));	
		$dec_c1->AddEntry(new SqlEntry('p.kind_id',3, SqlEntry::E));
		
		$cash1=$_cg1->ShowAllPos('cash/cash_list.html', $dec_c1, 
			
			$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
			$au->user_rights->CheckAccess('w',846) ,0, 1000,
			$au->user_rights->CheckAccess('w',842) ,  
			false ,true,false, 
			$au->user_rights->CheckAccess('w',847) ,  
			$au->user_rights->CheckAccess('w',843),
			
			$au->user_rights->CheckAccess('w',835),
			$au->user_rights->CheckAccess('w',844),
			$au->user_rights->CheckAccess('w',845),
			$exped
		
		);
		$sm1->assign('exped',$exped);
		//также нужны другие смежные счета
		$sm1->assign('another_nested_e', $_cg1->GetNestedBills($editing_user['id'], 3));
		
		
		//времена работы (для экспед-ия)
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"09");
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"00");
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',"18");
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',"00");
		
		
		
		
		
		//Примечания
		$rg=new BillNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed_price']==1, $au->user_rights->CheckAccess('w',339), $au->user_rights->CheckAccess('w',349), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',191)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',283)&&($editing_user['is_confirmed_price']==1)); 
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',292)); 
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',302)&&in_array($editing_user['status_id'],$_editable_status_id)&&$_bill->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		
		
		//кнопка доступна, если есть права и не утв-на отгрузка счета
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',188)&&(($editing_user['is_confirmed_price']==0)&&($editing_user['status_id']!=3)));
		
		$sm1->assign('can_change_pdate_shipping_plan', in_array($editing_user['status_id'],$_editable_status_id)||(($editing_user['is_confirmed_shipping']==0)&&($editing_user['status_id']!=3)));
		
		
		
		
		
		//можно ли отключать автовыравнивание?
		
		$sm1->assign('can_super_neq',$au->user_rights->CheckAccess('w',485));
		
		$sm1->assign('can_neq', 
			$au->user_rights->CheckAccess('w',474)
		);
		
		
		//можно ли отключать автоаннулирование?
		$sm1->assign('can_super_an',$au->user_rights->CheckAccess('w',539));
		
		$sm1->assign('can_an', 
			$au->user_rights->CheckAccess('w',538)
		);
		
		
		//можно ли создать входящий счет
		$sm1->assign('can_create_incoming_bill', $au->user_rights->CheckAccess('w',608)); 
		
		//можно ли создать затраты $can_make_cash
		$sm1->assign('can_make_cash', $au->user_rights->CheckAccess('w',835)); 
		
		
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',190)); 
		
		$sm1->assign('can_email_pdf',$au->user_rights->CheckAccess('w',860));
		 
	 
		
	 
 
		
		//header
		$sm_h=new SmartyAdm;
		
		$sm_h->assign('bill', $editing_user);
		$header=$sm_h->fetch('bills/bill_edit_header.html');
		$tmp1='h'.time();
		
		$f1=fopen(ABSPATH.'/tmp/'.$tmp1.'.html','w');
		fputs($f1, $header);
		fclose($f1);
		
		
		
		
		
		
		foreach($_printmodes as $k=>$v){
			if(($v==0)||($v==1)){
				//echo $content;
				
				if($v==1) $sm1->assign('printmode',2); //выводим печать
				
				if(($editing_user['org_id']==33) ) $content=$sm1->fetch('bills/bill_edit_nt_print.html');
				elseif( ($editing_user['org_id']==272)) $content=$sm1->fetch('bills/bill_edit_sya_print.html');
				else $content=$sm1->fetch('bills/bill_edit_print.html');
				
				$tmp='bill_'.$v.'_'.time();
			
				$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
				fputs($f, $content);
				fclose($f);
				
				$cd = "cd ".ABSPATH.'/tmp';
				exec($cd);
				
				
				$comand = "wkhtmltopdf-i386 --page-size A4 --orientation Portrait --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
				
				
				//$comand = "wkhtmltopdf-12 --page-size A4 --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm --header-html ".SITEURL.'/tmp/'.$tmp1.'.html'."   ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
			 
		 
				exec($comand);
		
			//header('Content-type: application/pdf');
			//header('Content-Disposition: attachment; filename="Счет_'.$editing_user['code'].'.pdf'.'"');
			//readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
			
			/*unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');*/
				unlink(ABSPATH.'/tmp/'.$tmp.'.html');
				unlink(ABSPATH.'/tmp/'.$tmp1.'.html');
				
				//добавим наш файл в массив прикладываемых файлов
				
				$name='Счет_'.$editing_user['supplier_bill_no'].'.pdf';
				if($v==1) $name='Счет_'.$editing_user['supplier_bill_no'].'_с_подписью_печатью.pdf';
				$filenames_to_send[]=array(
					'fullname'=>ABSPATH.'tmp/'."$tmp.pdf",
					'name'=>$name
				);
			}
		}
		
		
}


//**************************************** -обрабатываем реализации ************************************************/


if(
	(($mode==0)&&(in_array(2, $_printmodes)||in_array(3, $_printmodes))) //наличие связ реализаций по счету
){
	$sql='select id from acceptance where org_id="'.$result['org_id'].'" and bill_id="'.$id.'" and is_confirmed=1 and is_incoming=0';
	$set=new mysqlset($sql);
	$rs=$set->getresult();
	$rc=$set->getresultnumrows();
	
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		$_acceptances[]=$f['id'];	
	}
	
	
	
	//отгр док без подписи
	if(in_array(2, $_printmodes)){
		$_acceptance_printmodes[]=0; //акт
		$_acceptance_printmodes[]=2; //с/ф
		$_acceptance_printmodes[]=4;	//накл
	}
	
	//отгр док с подптсью
	if(in_array(3, $_printmodes)){
		$_acceptance_printmodes[]=1;	//акт
		$_acceptance_printmodes[]=3;	//с/ф
		$_acceptance_printmodes[]=5;	//накл
	}
	

}

elseif($mode==2){
	//printmodes=0,1,2,3,4,5	
	$_acceptance_printmodes=$_printmodes;
	
	$_acceptances[]=$id;
	
}

//	print_r($_acceptance_printmodes);

//формируем файлы реализаций
foreach($_acceptances as $acceptance_k=>$acceptance_id){
	
	
	$editing_user=$_acc->GetItemByFields(array('id'=>$acceptance_id, 'is_incoming'=>0));	
	$id=$acceptance_id;
	
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	
	
	$sm1=new SmartyAdm;
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	
	
		//склад
	$sector=$_sector->GetItemById($editing_user['sector_id']); //$bill['storage_id']);
	$sm1->assign('sector_id_string' ,$sector['name']);
	$sm1->assign('sector_id' ,$editing_user['sector_id']);
	
	
	$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
	
	
	
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['given_pdate']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['given_pdate']), 2);
		
		$orgitem['chief']=$sri_1['fio'];
		$orgitem['print_sign_dir']=$sri_1['sign'];
		
		$orgitem['main_accountant']=$sri_2['fio'];
		$orgitem['print_sign_buh']=$sri_2['sign'];
		
	
	
	$sm1->assign('print_org' ,$orgitem);
	
	
	//костыль для замены ТД СЯ на ТД "СЯ
	$orgitem_fact=$orgitem;
	$orgitem_fact['full_name']=eregi_replace('Торговый Дом Строительная Ярмарка', 'Торговый Дом "Строительная Ярмарка',$orgitem_fact['full_name']);
	$sm1->assign('print_org_fact' ,$orgitem_fact);
	
	$sm1->assign('print_org_opf' ,$opf);
	
	
	//рекв. орг.
	require_once('classes/bdetailsitem.php');
	$_bd=new BDetailsItem;
	$print_org_bdetail=$_bd->GetBasic($orgitem['id']);
	$sm1->assign('print_org_bdetail' ,$print_org_bdetail);
	
	
	//поставщик
	$supplier=$_supplier->GetItemById($bill['supplier_id']);
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($supplier['opf_id']);
	$sm1->assign('supplier_id' ,$bill['supplier_id']);
	$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
	$sm1->assign('print_supplier' ,$supplier);
	$sm1->assign('print_supplier_opf' ,$opf);
	
		$_sri=new SupplierRukItem;
		$sri_3=$_sri->GetActualByPdate($supplier['id'],date("d.m.Y", $editing_user['given_pdate']), 1);
		$sri_4=$_sri->GetActualByPdate($supplier['id'], date("d.m.Y",$editing_user['given_pdate']), 2);
		
		$sm1->assign('print_supplier_chief' ,$sri_3['fio']);
		$sm1->assign('print_supplier_main_accountant' ,$sri_4['fio']);
		
		
	//покупатель=грузополучатель?
	$sm1->assign('suppliers_are_equal', $bill['suppliers_are_equal']);
	//грузополучатель
	$supplier1=$_supplier->GetItemById($bill['ship_supplier_id']);
	$opf1=$_opf->GetItemById($supplier1['opf_id']);
	$sm1->assign('ship_supplier_id' ,$bill['ship_supplier_id']);
	$sm1->assign('ship_supplier_id_string' ,$opf1['name'].' '.$supplier1['full_name']);
	$sm1->assign('print_ship_supplier' ,$supplier1);
	$sm1->assign('print_ship_supplier_opf' ,$opf1);
	
	
	//реквизиты для печати
	require_once('classes/bdetailsitem.php');
	$_bdetail=new BDetailsItem;
	$bdetail=$_bdetail->GetItemById($bill['bdetails_id']);
	$sm1->assign('print_bdetail' ,$bdetail);
	
	
	$sm1->assign('sdelka_string', 'Исходящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
	
	
	//дог-р
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($bill['contract_id']);
	$sm1->assign('contract_no', $sci['contract_no']);
	$sm1->assign('contract_pdate', $sci['contract_pdate']);
	
	
	
	//даты
	$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
	
	
	
	
	//кем создано
	require_once('classes/user_s_item.php');
	$_cu=new UserSItem();
	$cu=$_cu->GetItemById($editing_user['manager_id']);
	if($cu!==false){
		$ccu=$cu['name_s'].' ('.$cu['login'].')';
	}else $ccu='-';
	$sm1->assign('created_by',$ccu);
	
	
	if($editing_user['given_pdate']>0){
		$sm1->assign('given_pdate_date',date('d',$editing_user['given_pdate']));
		$m='';
		switch(date('m',$editing_user['given_pdate'])){
			case 1:
				$m='января';
			break;
			case 2:
			$m='февраля';
			break;
			case 3:
			$m='марта';
			break;
			case 4:
			$m='апреля';
			break;
			case 5:
			$m='мая';
			break;
			case 6:
			$m='июня';
			break;
			case 7:
			$m='июля';
			break;
			case 8:
			$m='августа';
			break;
			case 9:
			$m='сентября';
			break;
			case 10:
			$m='октября';
			break;
			case 11:
			$m='ноября';
			break;
			case 12:
			$m='декабря';
			break;	
			
		}
	
	
		$sm1->assign('given_pdate_month',$m);
		$sm1->assign('given_pdate_year',date('Y',$editing_user['given_pdate']));
	}
	
	
	if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
	else $editing_user['given_pdate']='-';
	
	if($editing_user['print_pdate']>0) $editing_user['print_pdate']=date("d.m.Y",$editing_user['print_pdate']);
	else $editing_user['print_pdate']='-';
	
	 
	
	
	
	
	
	//Примечания
	$rg=new AccNotesGroup;
	$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',342), $au->user_rights->CheckAccess('w',351),$result['id']));
	$sm1->assign('can_notes',true);
	$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',236)/*&&($editing_user['is_confirmed']==0)*/);
	
	
	
	//допустимые доли превышения позиций
	$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',233));
	$sm1->assign('PPUP',PPUP);
	
	$sm1->assign('has_usl',$_acc->HasUsl($editing_user['id'])); 
	$sm1->assign('has_tov',$_acc->HasTov($editing_user['id'])); 
	 
	$sm1->assign('acc',$editing_user);
	
	
	
	foreach($_acceptance_printmodes as $kk=>$print_mode){
		/*
		//отгр док без подписи
		if(in_array(2, $_printmodes)){
			$_acceptance_printmodes[]=0; //акт
			$_acceptance_printmodes[]=2; //с/ф
			$_acceptance_printmodes[]=4;	//накл
		}
		
		//отгр док с подптсью
		if(in_array(3, $_printmodes)){
			$_acceptance_printmodes[]=1;	//акт
			$_acceptance_printmodes[]=3;	//с/ф
			$_acceptance_printmodes[]=5;	//накл
		}
		*/
		
		//echo $print_mode.'<br>';
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new AccPosGroup;
		
		//4,5 - тов накл.
		 if(($print_mode==4) || ($print_mode==5)) $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,true,false);
		//0, 1 - акт
		elseif(($print_mode==0)|| ($print_mode==1))  $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,false,true);
		else 
		 $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0);
		//print_r($bpg);
		
		 if(($print_mode==4) || ($print_mode==5)){
			if(count($bpg)==0) continue; //пустую накладную не отправлять 
		 }
		
		//echo $print_mode.'<br>';
		
		foreach($bpg as $k=>$v){
			
			$v['price_pm_formatted']=number_format($v['price_pm'],2,'.',' ');
			$v['total_formatted']=number_format($v['total'],2,'.',' ');
			$bpg[$k]=$v;	
		}
		
		$sm1->assign('positions',$bpg);
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
	
		
		require_once('classes/propis.php');
		$_pn=new PropisUn(); $_pp=new Propis;
		$sm1->assign('count_propis',$_pn->propis(count($bpg)));
		
		
		$summa_propis=trim( $_pp->propis(floor($total_cost)));
		
		$summa_propis= mb_convert_case(substr($summa_propis, 0, 1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 1,strlen($summa_propis));
		
		$sm1->assign('total_cost_rub_propis',$summa_propis);
		
		 // strtoupper(substr(  $_pp->propis(floor($total_cost)), 1,1)). substr(  $_pp->propis(floor($total_cost)), 2, strlen($_pp->propis(floor($total_cost)))));
		$sm1->assign('total_cost_kop_propis',round(100*((float)$total_cost-floor($total_cost))));
		
		
		/*
		cols_by_two
		costs_by_two
		nds_sums_by_two
		totals_by_two
		cols_by_all
		costs_by_all
		nds_sums_by_all
		totals_by_all
		
		cols_by_one
		costs_by_one
		nds_sums_by_one
		totals_by_one
		
		*/
		
		$cols_by_two=0;
		$costs_by_two=0;
		$nds_sums_by_two=0;
		$totals_by_two=0;
		
		$cols_by_all=0;
		$costs_by_all=0;
		$nds_sums_by_all=0;
		$totals_by_all=0;
		
		$cols_by_one=0;
		$costs_by_one=0;
		$nds_sums_by_one=0;
		$totals_by_one=0;
		
		$ic=0;
		foreach($bpg as $k=>$v){
			if($ic==0){
				$cols_by_one=$v['quantity'];
				$costs_by_one=$v['total']-$v['nds_summ'];
				$nds_sums_by_one=$v['nds_summ'];
				$totals_by_one=$v['total'];	
			}else{
				$cols_by_two+=$v['quantity'];
				$costs_by_two+=$v['total']-$v['nds_summ'];
				$nds_sums_by_two+=$v['nds_summ'];
				$totals_by_two+=$v['total'];	
			}
			
			$cols_by_all+=$v['quantity'];
			$costs_by_all+=$v['total']-$v['nds_summ'];
			$nds_sums_by_all+=$v['nds_summ'];
			$totals_by_all+=$v['total'];
				
			$ic++;	
		}
		
		
		 
		
		//позиции для накладной
		$print_positions=array();
		
		$cter=1; $page=1;
		$cols_by_page=0;
		$costs_by_page=0;
		$nds_sums_by_page=0;
		$totals_by_page=0;
		
		$cols_by_all=0;
		$costs_by_all=0;
		$nds_sums_by_all=0;
		$totals_by_all=0;
		
		$_posdi=new PosDimItem;
		
		foreach($bpg as $k=>$v){
			//1 23
			
			$cols_by_page+=$v['quantity'];
			$costs_by_page+=$v['total']-$v['nds_summ'];
			$nds_sums_by_page+=$v['nds_summ'];
			$totals_by_page+=$v['total'];	
			
			
			$cols_by_all+=$v['quantity'];
			$costs_by_all+=$v['total']-$v['nds_summ'];
			$nds_sums_by_all+=$v['nds_summ'];
			$totals_by_all+=$v['total'];
			
			$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
			$v['okei']=$posdi['okei'];
			
			//var_dump($posdi);
			$v['price_pm_wo_nds']=number_format($v['price_pm']-$v['nds_price'],2,'.',' ');
			$v['total_wo_nds']=number_format($v['total']-$v['nds_summ'],2,'.',' ');
			$v['nds_summ_f']=number_format($v['nds_summ'],2,'.',' ');
			$v['total_f']=number_format($v['total'],2,'.',' ');
			
			$v['cols_by_page']=$cols_by_page;
				$v['costs_by_page']=$costs_by_page;
				$v['nds_sums_by_page']=$nds_sums_by_page;
				$v['totals_by_page']=$totals_by_page;
			
			$v['costs_by_page_f']=number_format($costs_by_page,2,'.',' ');
				$v['nds_sums_by_page_f']=number_format($nds_sums_by_page,2,'.',' ');
				$v['totals_by_page_f']=number_format($totals_by_page,2,'.',' ');
			
			$v['break_after']=false;
			if($cter==1){
				
				
				
				$cols_by_page=0;
				$costs_by_page=0;
				$nds_sums_by_page=0;
				$totals_by_page=0;	
				
				$v['break_after']=true;	
				$page++;			
			}elseif(($cter>1)&&(($cter-1)%33==0)){
				
				
				
				
				$cols_by_page=0;
				$costs_by_page=0;
				$nds_sums_by_page=0;
				$totals_by_page=0;	
				
				$v['break_after']=true;	
				$page++;		
					
			}
			$v['page']=$page;
			
			
			
			$print_positions[]=$v;
			$cter++;	
		}
		$sm1->assign('print_positions', $print_positions);
		
			
		$sm1->assign('cols_by_all',$cols_by_all);
		$sm1->assign('costs_by_all',number_format($costs_by_all,2,'.',' '));
		$sm1->assign('nds_sums_by_all',number_format($nds_sums_by_all,2,'.',' '));
		
		$sm1->assign('totals_by_all', number_format($totals_by_all,2,'.',' '));
		
		
		
		//позиции для c/ф
		$print_positions1=array();
		
		$cter=1; $page=1;
		 
		$sm1->assign('to_pay',number_format($totals_by_all-$nds_sums_by_all,2,'.',' '));
		
		foreach($bpg as $k=>$v){
			//1 11
			
			
			$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
			$v['okei']=$posdi['okei'];
			
			$v['price_pm_wo_nds']=number_format($v['price_pm']-$v['nds_price'],2,'.',' ');
			$v['total_wo_nds']=number_format($v['total']-$v['nds_summ'],2,'.',' ');
			$v['nds_summ_f']=number_format($v['nds_summ'],2,'.',' ');
			$v['total_f']=number_format($v['total'],2,'.',' ');
			
			
			$v['break_after']=false;
			if($cter==8){
				
				
			 
				$v['break_after']=true;	
				$page++;			
			}elseif(($cter>8)&&(($cter-8)%17==0)){
				
				
				
			 
				
				$v['break_after']=true;	
				$page++;		
					
			}
			$v['page']=$page;
			
			
			
			$print_positions1[]=$v;
			$cter++;	
		}
		
		//избегаем висячей подписи...
		//если позиций было от 4 по 8...
		if((count($print_positions1)>=4)&&(count($print_positions1)<=8)){
			//перед последней позицией... т.е. в предпоследней! поставить  $v['break_after']=true;	
			foreach($print_positions1 as $k=>$v){	
				if($k==(count($print_positions1)-2)) $v['break_after']=true;
				$print_positions1[$k]=$v;
			}
		}
		
		//две страницы и более страниц - вычесть 8, вычесть целое число раз по 17, если осталось более 12 - то после 12й позиции поставить разрыв!
		if(count($print_positions1)>8){
			$rest=count($print_positions1)-8-floor( (count($print_positions1)-8)/17)*17;
			if($rest>12){
				$seek=	8+   floor( (count($print_positions1)-8)/17)*17     +12;
				foreach($print_positions1 as $k=>$v){
					if($k==($seek-1)) $v['break_after']=true;
					$print_positions1[$k]=$v;
				}
			}
		}
		
		
		$sm1->assign('print_positions1', $print_positions1);
		
		
		//закрепленные входящие оплаты для вывода в с/ф
		$_pac=new PayForAccGroup;
		$binded_payments=$_pac->GetPayForSF($id);
		$sm1->assign('binded_payments', $binded_payments);
		
		
		if(($print_mode==1)||($print_mode==3)||($print_mode==5)) $sm1->assign('do_print_sign', 1); //есть подпись
		else  $sm1->assign('do_print_sign', 0);
		
		$sm1->assign('do_print_summ', 1);
		
		$user_form='';
		//акт. если не было услуг - пропускаем	
		if(($print_mode==0)||($print_mode==1)){
			if(!$_acc->HasUsl($editing_user['id'])) continue;
			$user_form=$sm1->fetch('acc/acc_edit_akt.html');
		}
		elseif(($print_mode==2)||($print_mode==3)){
			//с/ф
			
			$user_form=$sm1->fetch('acc/acc_edit_fakt.html');	
		}
		elseif(($print_mode==4)||($print_mode==5)){
			//накл
			if(!$_acc->HasTov($editing_user['id'])) continue;
			
			 if(($editing_user['org_id']==33)) $user_form=$sm1->fetch('acc/acc_edit_nt_print.html');
			 else $user_form=$sm1->fetch('acc/acc_edit_print.html');	
		}
		
		$tmp='acc_'.$print_mode.'_'.time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $user_form);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		if(($print_mode==0)||($print_mode==1)) $ori=' --orientation Portrait ';
		else $ori=' --orientation Landscape ';
		
		$comand = "wkhtmltopdf-i386 --page-size A4 ".$ori." --encoding windows-1251 --margin-top 5mm --margin-bottom 0mm --margin-left 10mm --margin-right 10mm   ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
	
	
		exec($comand);
	
		//header('Content-type: application/pdf');
		//header('Content-Disposition: attachment; filename="Реализация_'.$editing_user['id'].'.pdf'.'"');
		//readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
		
		//unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		
		/*
		//отгр док без подписи
		if(in_array(2, $_printmodes)){
			$_acceptance_printmodes[]=0; //акт
			$_acceptance_printmodes[]=2; //с/ф
			$_acceptance_printmodes[]=4;	//накл
		}
		
		//отгр док с подптсью
		if(in_array(3, $_printmodes)){
			$_acceptance_printmodes[]=1;	//акт
			$_acceptance_printmodes[]=3;	//с/ф
			$_acceptance_printmodes[]=5;	//накл
		}
		*/
		$name1="Реализация";
		switch($print_mode){
			case 0:
				$name1='Акт';
			break;
			case 1:
				$name1='Акт';
			break;
			
			case 2:
				$name1='Счет-фактура';
			break;
			case 3:
				$name1='Счет-фактура';
			break;
			
			case 4:
				$name1='Тов_накл';
			break;
			case 5:
				$name1='Тов_накл';
			break;
		};
		
		 
		$name=$name1.'_'.$editing_user['given_no'].'.pdf';
		if(($print_mode==1)||($print_mode==3)||($print_mode==5)) $name=$name1.'_'.$editing_user['given_no'].'_с_подписью_печатью.pdf';
		$filenames_to_send[]=array(
			'fullname'=>ABSPATH.'tmp/'."$tmp.pdf",
			'name'=>$name
		);
		
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
		$log->PutEntry($result['id'],'отправил pdf-документы счета на электронную почту',NULL,860,NULL,'Документы: '.implode(', ',$_filenames).'; получатели: '.implode(', ',$_addresses),$document_id);
		$_bni=new BillNotesItem;
		
		$notes_params=array();
		$notes_params['is_auto']=1;
		$notes_params['user_id']=$document_id;
		$notes_params['pdate']=time();
		$notes_params['posted_user_id']=$result['id'];
		
		$notes_params['note']='Автоматическое примечание: Документы: '.implode(', ',$_filenames).' были отправлены на электронную почту  пользователем '.SecStr($result['name_s'].' '.$result['login']).'; получатели: '.implode(', ',$_addresses).'. ';
		
		$_bni->Add($notes_params);
		
	}elseif($mode==2){
		$log->PutEntry($result['id'],'отправил pdf-документы реализации на электронную почту',NULL,861,NULL,'Документы: '.implode(', ',$_filenames).'; получатели: '.implode(', ',$_addresses),$document_id);
		$_ani=new AccNotesItem;
		$notes_params=array();
		$notes_params['is_auto']=1;
		$notes_params['user_id']=$document_id;
		$notes_params['pdate']=time();
		$notes_params['posted_user_id']=$result['id'];
		
		$notes_params['note']='Автоматическое примечание: Документы: '.implode(', ',$_filenames).' были отправлены на электронную почту  пользователем '.SecStr($result['name_s'].' '.$result['login']).'; получатели: '.implode(', ',$_addresses).'. ';
		$_ani->Add($notes_params);
		
		
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