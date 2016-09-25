<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');
 

require_once('../classes/komplnotesgroup.php');
require_once('../classes/komplnotesitem.php');
require_once('../classes/komplreports.php');
require_once('../classes/komplgroup.php');
require_once('../classes/komplitem.php');
require_once('../classes/komplconfitem.php');
require_once('../classes/komplconfroleitem.php');
require_once('../classes/komplpositem.php');
require_once('../classes/komplposgroup.php');
require_once('../classes/user_s_item.php');
 

require_once('../classes/sectoritem.php');
 

require_once('../classes/komplscanconfgroup.php');
require_once('../classes/kompldates.php');

require_once('../classes/komplrent.php');
require_once('../classes/kompsync.php');

require_once('../classes/komplekt_view.class.php');



$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';


if(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>0));
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
	/*		if(
			($k=='contract_no')||
			($k=='contract_pdate')||
			($k=='contract_pdate')) continue;
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';*/
			$si[$k]=iconv('windows-1251', 'utf-8', $v);
		}
		
		//$rret[]='"opf_name":"'.htmlspecialchars($opf['name']).'"';
		$si['opf_name']=iconv('windows-1251', 'utf-8', $opf['name']);
		 
		$ret=json_encode($si);
		
		//$ret='{'.implode(', ',$rret).'}';
	}
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_suppliers")){
	
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	
	
	$ret=$_pg->GetItemsForKomplekt('komplekt/suppliers_list.html',  $dec,true,$all7,$result);
	

	
}



elseif(isset($_POST['action'])&&(($_POST['action']=="find_pos")||($_POST['action']=="pcg_find_pos"))){
	//получим список позиций по фильтру
	$_pg=new PosGroup();
	
	$dec=new DBDecorator;
	//str_replace("\\", "\\\\", $name);
	
	$name=SecStr(str_replace("\\", "\\\\",iconv("utf-8","windows-1251",$_POST['qry'])));
	$group_id=abs((int)$_POST['group_id']);
	
	$except_id=abs((int)$_POST['except_id']);
	$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	//исключить услуги!!!!!!!!
	$uslugi=array();
	  $_pgg=new PosGroupGroup;
	  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
	  
	  $uslugi/*$this->uslugi*/[]=SERVICE_CODE;
	  foreach($arc as $k=>$v){
		  if(!in_array($v['id'],$uslugi/*$this->uslugi*/)) $uslugi/*$this->uslugi*/[]=$v['id'];
		  $arr2=$_pgg->GetItemsByIdArr($v['id']);
		  foreach($arr2 as $kk=>$vv){
			  if(!in_array($vv['id'],$uslugi/*$this->uslugi*/))  $uslugi/*$this->uslugi*/[]=$vv['id'];
		  }
	  }
	  //
	 
	
	//var_dump($uslugi);
	
	if(count($uslugi)>0){
		$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$uslugi));		
		
	}
	
	
	//только активные позиции
	$dec->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
	
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	
	//if($group_id>0) $dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('p.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('p.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('p.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('p.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('p.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('p.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	
	if($_POST['action']=="find_pos") $ret=$_pg->ShowPos('komplekt/pos_in_filter.html', $dec,0,1000,false,false,true);
	else {
			
		$ret=$_pg->ShowPos('komplekt/pos_pcg_in_filter.html', $dec,0,1000000,false,false,true,false,false,false,false,false,abs((float)$_POST['kol']));
	}
	
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="create_pos"))){
	//создание позиции
	if(!$au->user_rights->CheckAccess('w',67)){
		
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	$_pos=new PosItem;
	 
	$params=array();
	
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$params['group_id']=abs((int)$_POST['group_id']);
	
 
 
	$params['is_active']=1;
	
	
	if(abs((int)$_POST['dimension_id'])>0) $params['dimension_id']=abs((int)$_POST['dimension_id']);
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $params['length']=SecStr(iconv("utf-8","windows-1251",$_POST['length']));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $params['width']=SecStr(iconv("utf-8","windows-1251",$_POST['width']));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $params['height']=SecStr(iconv("utf-8","windows-1251",$_POST['height']));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $params['diametr']=SecStr(iconv("utf-8","windows-1251",$_POST['diametr']));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $params['weight']=SecStr(iconv("utf-8","windows-1251",$_POST['weight']));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $params['volume']=SecStr(iconv("utf-8","windows-1251",$_POST['volume']));
	
	
	$code=$_pos->Add($params);
	
	 
	$log->PutEntry($result['id'],'создал позицию каталога',NULL,67,NULL,$params['name'],$code);	
		
	
	
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="put_to_form")){
	$_positem=new PosItem;
	$storage_id=abs((int)$_POST['storage_id']);
	
 
	$_pdi=new PosDimItem;
	$tempid=time();
	foreach($_POST['ids'] as $k=>$v){
		//$ret.=$v.'=>'.$_POST['kols'][$k].'<br>';
		$sm=new SmartyAj;
		
		$tempid++;
		$sm->assign('tempid',$tempid);
		
		$positem=$_positem->GetItemById($v);
		$sm->assign('id',$v);
		$sm->assign('name',$positem['name']);
		
		$pdi=$_pdi->GetItemById($positem['dimension_id']);
		$sm->assign('dim_name',$pdi['name']);
		
		$sm->assign('kol',$_POST['kols'][$k]);
		
		$sm->assign('kol_init',$_POST['kols'][$k]);
		
		$sm->assign('in_free',0);
		$sm->assign('in_bills',0);
		 
		$sm->assign('in_pol',0);
		
		
		$sm->assign('in_free_in',0);
		$sm->assign('in_bills_in',0);
		 
		$sm->assign('in_pol_in',0);
		
		
		
		
		$sm->assign('storage_id',$storage_id);
		if($si!==false) $sm->assign('storage_name',$si['name']);
		else $sm->assign('storage_name','-');
		
		
		$sm->assign('can_modify',true);
		
		$sm->assign('can_delete_positions',true); //$au->user_rights->CheckAccess('w',178)); 
		
		
		$cannot_select_positions=true;
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',301);
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',291);
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',92);
		
		
		$sm->assign('cannot_select_positions', $cannot_select_positions);
		
		$ret.=$sm->fetch('komplekt/put_to_form.html');
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="load_sectors")){
	$storage_id=abs((int)$_POST['storage_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new StorageSector();
	$arr=$_bd->GetBookCategsArr($storage_id, 1);
	 //GetItemsByIdArr($supplier_id,$current_id);
	
	$do_limit_sector=$au->FltSector($result);
	
	$ret='';
	if($current_id==0) $ret.='<option value="0" selected="selected">-выберите-</option>';
	else $ret.='<option value="0">-выберите-</option>';
	foreach($arr as $k=>$v){
		if($do_limit_sector&&($v['id']!=$result['sector_id'])) continue;
		
		if($current_id==$v['id']) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_storages")){
	/*$sector_id=abs((int)$_POST['sector_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new StorageSector();
	$arr=$_bd->GetCategsBookArr($sector_id, 1);
	 //GetItemsByIdArr($supplier_id,$current_id);
	
	//$do_limit_sector=$au->FltSector($result);
	
	$_si=new SectorItem;
	$si=$_si->getitembyid($sector_id);
	
	
	
	$ret='';
	if($current_id==0) $ret.='<option value="0" selected="selected">-выберите-</option>';
	else $ret.='<option value="0">-выберите-</option>';
	foreach($arr as $k=>$v){
		//if($do_limit_sector&&($v['id']!=$result['sector_id'])) continue;
		
		if((($si['s_s']==1)&&($v['s_s']==1))&&!$au->user_rights->CheckAccess('w',380)) continue;
		
		
		if($current_id==$v['id']) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	*/
	
}
//контроль дат
elseif(isset($_POST['action'])&&($_POST['action']=="pdate_check")){
	$pdate=($_POST['pdate']);
	
	$ret=datefromdmy($pdate);
	
	
}
//контроль дат
elseif(isset($_POST['action'])&&($_POST['action']=="pdate_current_check")){
	$begin_pdate=datefromdmy($_POST['begin_pdate']);
	$end_pdate=datefromdmy($_POST['end_pdate'])+24*60*60;
	$now_pdate=DateFromdmy(date('d.m.Y'));
	   //abs((int)$_POST['now_pdate']);
	
	$_bd=new KomplDates;
	
	$ret=0;
	if((strlen($begin_pdate)>0)&&(strlen($end_pdate)>0)){
		//$begin_pdate=datefromdmy($begin_pdate);
	//	$end_pdate=datefromdmy($end_pdate)+24*60*60;
		
		//$now=time();
		
		if(($begin_pdate>$now_pdate+24*60*60)||($now_pdate>$end_pdate)) $ret=1;
		
		if($now_pdate>$begin_pdate) $ret=2;
		
		//проверка на 3 календ дня.
		$min_per=3*24*60*60;
		
		//проверка на 7 раб дней
		//$min_per=$_bd->FindMinPer(7, $begin_pdate);
		
		/*echo ($min_per)/(24*60*60); echo ' ';
		echo ($end_pdate-24*60*60-$begin_pdate)/(24*60*60); echo ' ';
		*/
		if(($end_pdate-24*60*60-$begin_pdate)<$min_per) $ret=3;
		
		
	}elseif((strlen($begin_pdate)>0)){
		$begin_pdate=datefromdmy($begin_pdate);
		//echo $now_pdate.' '.$begin_pdate; 
		if($now_pdate>$begin_pdate) $ret=2;
	}
	
	//$ret=datefromdmy($pdate);
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_active_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
//РАБОТА С ПРИМЕЧАНИЯМИ
if(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$_ki=new KomplItem;
	
	$rg=new KomplNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(338,384))), $au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(348,385))), $result['id'], $au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(534,535)))));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(179,383))));
	
	
	$ret=$sm->fetch('komplekt/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_ki=new KomplItem;
	
	if(!$au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(179,383)))){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new KomplNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по заявке', NULL,179, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes_fake")){
	//добавка примечаний на форму создания заявки
	
	$sm=new SmartyAj;
	
	$sm->assign('tempid',time());
	
	$sm->assign('pdate',date('d.m.Y H:i:s'));
	$sm->assign('user_name_s',$result['name_s']);
	$sm->assign('user_login',$result['login']);
	$sm->assign('note',SecStr(iconv("utf-8","windows-1251",$_POST['note']),9));
	$sm->assign('can_edit',$au->user_rights->CheckAccess('w',179));
	
	$sm->assign('word','notes');
	$sm->assign('named','notes');
	
	$ret=$sm->fetch('komplekt/d_notes_fake.html');
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_ki=new KomplItem;
	
	
	if(!$au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(82,382)))){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KomplNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по заявке', NULL,82,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_ki=new KomplItem;
	
	
	if(!$au->user_rights->CheckAccess('w',$_ki->rd->FindRId($user_id,NULL,NULL,NULL,NULL,NULL,array(82,382)))){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new KomplNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по заявке', NULL,82,NULL,NULL,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_bills_pos")){
	//dostup
	$_kr=new KomplReports;
	
	$id=abs((int)$_POST['id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	$is_incoming=abs((int)$_POST['is_incoming']);
		
	$ret='';
	if($is_incoming==1) $ret.=$_kr->InBillsIn($id,'komplekt/in_bills_in.html',$result['org_id'], true, $komplekt_ved_id, $au->user_rights->CheckAccess('w',87));

	else $ret.=$_kr->InBills($id,'komplekt/in_bills.html',$result['org_id'], true, $komplekt_ved_id, $au->user_rights->CheckAccess('w',87));

	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos")){
	//dostup
	$_kr=new KomplReports;
	
	$id=abs((int)$_POST['id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$ret=$_kr->InSh($id,'komplekt/in_shs.html',$result['org_id'], true, $komplekt_ved_id, $au->user_rights->CheckAccess('w',87));
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_acc_pos")){
	//dostup
	$_kr=new KomplReports;
	
	$id=abs((int)$_POST['id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	$is_incoming=abs((int)$_POST['is_incoming']);
	
	if($is_incoming==1) $ret=$_kr->InAccIn($id,'komplekt/in_accs_in.html',$result['org_id'],true, $komplekt_ved_id, $au->user_rights->CheckAccess('w',87));
	else $ret=$_kr->InAcc($id,'komplekt/in_accs.html',$result['org_id'],true, $komplekt_ved_id, $au->user_rights->CheckAccess('w',87));
	
	
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	$_ti=new KomplItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	$color='black';
	$trust['blink']=$_ti->kompl_blink->OverallBlink($id, $trust['status_id'], $result['id'], $result['is_supply_user'], $color);
	
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	$_scanconf=new KomplScanConf;
	
	if($_ti->DocCanAnnul($id, $rss1, $result['id'])){
		//удаление	
		if($au->user_rights->CheckAccess('w',$_ti->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(83,401)))){
			
			//найти все галочки, снять их
			$_kci=new KomplConfItem; $_ni=new KomplNotesItem; $_krci=new KomplConfRoleItem;
			$points=$_ti->GetPointsArr($id);
			foreach($points as $k=>$v){
				//в журнал, в примечания
				$_kci->Del($v['id']);
				$role=$_krci->GetItemById($v['role_id']);
				
				$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: утверждение заявки в роли  '.SecStr($role['name']).' было снято автоматически в связи с аннулированием заявки',
				'is_auto'=>1,
				'pdate'=>time()
					));	
					
				$log->PutEntry($result['id'],'снято утверждение заявки в связи с аннулированием заявки',NULL,82, NULL, 'роль: '.$role['name'],$id);		
			}
			
			
			
			//сканировать статус (is_active)
			$is_confirmed=(int)$_scanconf->ScanConfirm($id);
			$params=array();
			$params['is_active']=$is_confirmed;
			$_ti->Edit($id, $params);
			
			//аннулировать
			
			
			
			$_ti->Edit($id,array('status_id'=>3));
			
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование заявки',NULL,83,NULL,'заявка № '.$trust['id'].': установлен статус '.$stat['name'],$id);
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);
			
			//внести примечание
			$_ni=new KomplNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));		
		}
	//}elseif($trust['status_id']==3){
	}elseif($_ti->DocCanRestore($id,$rss2)){
		//разудаление
		if($au->user_rights->CheckAccess('w',$_ti->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(132,402)))){
			$_ti->Edit($id,array('status_id'=>11));	
			
			$stat=$_stat->GetItemById(11);
			$log->PutEntry($result['id'],'восстановление заявки',NULL,132,NULL,'заявка № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			//внести примечание
			$_ni=new KomplNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));			
		}
		
	}
	
	if($from_card==0){
	
	  $shorter=abs((int)$_POST['shorter']);
	   $template='komplekt/komplekt_list.html';
	  
	  
	  
	  $acg=new KomplGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	  
	  $ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',81),  $au->user_rights->CheckAccess('w',83),time()-3*12*30*24*60*60, time()+24*60*60,false,true,$au->user_rights->CheckAccess('w',132),NULL,$au->user_rights->CheckAccess('w',81), $au->user_rights->CheckAccess('w',282), $au->user_rights->CheckAccess('w',97), $au->user_rights->CheckAccess('w',862), $au->user_rights->CheckAccess('w',863));
	  
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',$_ti->rd->FindRId(NULL,$editing_user,NULL,NULL,NULL,NULL,array(83,401)));
		if(!$au->user_rights->CheckAccess('w',83)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('komplekt_ved',$editing_user);
		$ret=$sm->fetch('komplekt/toggle_annul_card.html');	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new KomplItem;
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';
        
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new KomplItem;
		
		$_sh->ScanEq($id,$args,$output);
		
		if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
			
		
		$ret=$output;
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_eq")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new KomplItem;
		
		
		foreach($args as $k=>$arg){
		
			$_sh->DoEq($id,array($arg),$output);
		
		}
		// 
        
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_scan_eq")){
	//сканирование выравнивания массовое
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		//$targs=explode(',',$args);
		
		$alls=array();
		
		 
		$_pos=new PosItem;
		foreach($args as $k=>$arg){
			
			
			
			
			$eq_items=array();
			$eq_items=$_ki->ScanEq($id,array( $arg),$output,NULL,false,"");
			
			//echo count($eq_items);
			
			if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
			
			foreach($eq_items as $kk=>$eq_item){
				$pos=$_pos->GetItemById($eq_item['position_id']);
				
				$alls[]=array(
					'code'=>$eq_item['position_id'],
					'name'=>$pos['name'],
					'delta'=>$eq_item['delta'],
					'quantity'=>$eq_item['quantity'],
					'comments'=>$output
				);
			}
		}
		//$ret=$output;
		$sm=new SmartyAj;
		
		$sm->assign('items', $alls);
			
		$ret=$sm->fetch('komplekt/scan_eq.html');	
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}















elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq_usl")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		 
		$_sh=new KomplItem;
		
		$_sh->DoEqUsl($id, $output); //>DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'");/* location.reload();*/</script>';
        
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq_usl")){
	//выравнивание
	$id=abs((int)$_POST['id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($id,NULL,NULL,NULL,NULL,NULL,array(291,399)))){
		
		 
		$_sh=new KomplItem;
		
		$count=$_sh->ScanUslEq($id, $output,null,'',$items_incoming, $items);
		
		
		
		if(($count>0)) $ret=1;
		else $ret='';
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_code")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$except_id=abs((int)$_POST['except_id']);
		$code=SecStr($_POST['code']);
		$sector_id=abs((int)$_POST['sector_id']);
		
		
		$_sh=new KomplItem;
		
		if($_sh->CheckCode($code,$sector_id,$except_id)>0) $ret=1;
		else $ret=0;
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_binded_docs")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$position_id=abs((int)$_POST['position_id']);
		//$code=SecStr($_POST['code']);
		$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
		
		
		/*$_sh=new KomplItem;
		
		if($_sh->CheckCode($code,$sector_id,$except_id)>0) $ret=1;
		else $ret=0;*/
		
		$_kpi=new KomplPosItem;
		
		if($_kpi->CheckBindedDocuments($komplekt_ved_id, $position_id, $binded_docs)>0) $ret=1;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="change_komplekt_position")){
	//проверить, есть ли заявки с таким номером для такого уч.
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	$_ki=new KomplItem;
	
	if($au->user_rights->CheckAccess('w',$_ki->rd->FindRId($komplekt_ved_id,NULL,NULL,NULL,NULL,NULL,array(347,408)))){
		$position_id=abs((int)$_POST['position_id']);
		//$code=SecStr($_POST['code']);
		
		$kol=abs((float)$_POST['kol']);
		$new_position_id=abs((int)$_POST['new_position_id']);
		
		$storage_id=abs((int)$_POST['storage_id']);
		
		
		
		$_kpi=new KomplPosItem;
		
		
		$ret=$_kpi->ChangePosition($position_id, $komplekt_ved_id, $kol, $new_position_id, $storage_id);
		
		//обработать все связанные документы....
		
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new KomplItem;
		
		
		if(!$_ki->DocCanUnconfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_save")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new KomplItem;
		
		
		if(!$_ki->CheckClosePdate($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_position_count_in_bills")){
	
	
		$kvid=abs((int)$_POST['kvid']);
		$position_id=abs((int)$_POST['position_id']);
		$_kr=new KomplReports;
		
		$ret=$_kr->CountInBills($kvid, $position_id);
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_save_sector_storage")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		$_ssgr=new StorageSector;
		$ssgr=$_ssgr->GetItemByFields(array('storage_id'=>abs((int)$_POST['storage_id']), 'sector_id'=>abs((int)$_POST['sector_id']) ));
		if(($ssgr===false)||($ssgr['can_make_komplekt']==0)){
			$ret='Отдел снабжения временно заблокировал создание заявок по выбранному Участку/объекту. Пожалуйста, обратитесь в отдел снабжения для решения данного вопроса';
		}else $ret=0;
		
		
		//если ноль - то все хорошо
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="view_re")){
	
	
		$id=abs((int)$_POST['id']);
		
	 
		$_kr=new KomplRent;
		
		$ret=$_kr->ShowData($id, 'komplekt/re_data.html', true, $au->user_rights->CheckAccess('w',863), $rent_value, $rent_percent);
		
		
		$log->PutEntry($result['id'],'просмотр рентабельности по заявке',NULL,862, NULL, $id,$id);
		
		
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="reload_positions")){
	
	
		$id=abs((int)$_POST['id']);
		
		$sortmode=abs((int)$_POST['sortmode']);
		
		$can_modify=(bool)$_POST['can_modify'];
		$can_delete_positions=(bool)$_POST['can_delete_positions'];
		$cannot_select_positions=(bool)$_POST['cannot_select_positions'];
		
		$_kpg=new KomplPosGroup;
		
		$positions=$_kpg->GetItemsByIdArr($id,0,true,NULL, $sortmode);
		
		$sm=new SmartyAj();
		
		$sm->assign('pos', $positions);
		
		$sm->assign('can_modify', $can_modify);
		$sm->assign('can_delete_positions', $can_delete_positions);
		$sm->assign('cannot_select_positions', $cannot_select_positions);
		
		
		$ret=$sm->fetch('komplekt/positions_list.html');
	 /*
		$_kr=new KomplRent;
		
		$ret=$_kr->ShowData($id, 'komplekt/re_data.html', true, $au->user_rights->CheckAccess('w',863), $rent_value, $rent_percent);
		
		*/
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="check_out_bills")){
	$id=abs((int)$_POST['id']);
	
	$sql='select b.id, b.code from bill  as b
		where 
			b.is_confirmed_price=1
			and b.is_incoming=0
			and (b.id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")
			
				or b.komplekt_ved_id="'.$id.'")';
	$set=new mysqlset($sql);
	$rs=$set->getresult();
	$rc=$set->getresultnumrows();
	
	$arr=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		$arr[]=$f['code'];	
	}
	
	$ret=implode(', ',$arr);
}

elseif(isset($_POST['action'])&&($_POST['action']=="syncro_komplekt")){
	$id=abs((int)$_POST['id']);
	$is_standart=abs((int)$_POST['is_standart']);
	$_ki=new KomplItem;
		
		$current_ki=$_ki->getitembyid($id);
	
	$ks=new KompSync($id, $is_standart, $result['org_id'],  $current_ki['supplier_id'], $result);
	
	$ret=$ks->Sync();
	
	

}

elseif(isset($_POST['action'])&&($_POST['action']=="check_out_docs")){
	 $id=abs((int)$_POST['id']);
	$is_standart=abs((int)$_POST['is_standart']);
	$_ki=new KomplItem;
		
		$current_ki=$_ki->getitembyid($id);
	
	$ks=new KompSync($id, $is_standart, $result['org_id'],  $current_ki['supplier_id'], $result);
	$ret=$ks->GetLeadingDocs($id);
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Komplekt_ViewGroup;
	$_view=new Komplekt_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new Komplekt_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>