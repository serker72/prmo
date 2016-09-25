<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');

class AnDs{

	public function ShowData($pdate1, $pdate2, $bank_id, $extended_an=0, $org_id, $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls){
		
		$pdate2+=24*60*60-1;
		
		$_bpm=new BillPosPMFormer;
	
		$_bd=new BDetailsGroup;
		
		$_au=new AuthUser;
		//$_res=$_au->Auth();
		
		
		$sm=new SmartyAdm;
		
		$_org=new OrgItem;
		$_opf=new OpfItem;
		
		$org=$_org->getitembyid($org_id);
		$opf=$_opf->GetItemById($org['opf_id']);
		
		$sm->assign('org', $org);//ORGANIZATION_TITLE);
		$sm->assign('opf', $opf);
		
		
		
		$bank_names=array(); 
		
		$bank_filter='';
		
		
		//var_dump($bank_id);
		if(is_array($bank_id)&&(count($bank_id)>0)) $bank_filter=' and id in ('.implode(', ', $bank_id).') ';
		
		
		
		
		$before_ost=0;
		$total_plus=0; $total_minus=0;
		$after_ost=0;
		
		
		
		
		
		
		
	   	$alls=array();
		$total=0;
		
		//найти все банки организации
		$sql='select * from banking_details where user_id="'.$org_id.'" '.$bank_filter.' order by bank, rs';
		//echo $sql;
		
		if($do_it){
		  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  for($i=0; $i<$rc; $i++){
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  
			  
			  
			  $subs=array();
			  
			  //найдем входящий остаток - вход опл в +, исх. опл в -
			   $sql2='select sum(p.value)
			   from payment as p
			   left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			    where p.org_id="'.$org_id.'" and p.is_incoming=1 and p.org_bdetails_id="'.$f['id'].'" and p.is_confirmed=1 and (p.given_pdate < "'.$pdate1.'" ) order by p.given_pdate asc';
			  $set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
			  $rs2=$set2->GetResult();
			  $g=mysqli_fetch_array($rs2);
			  $vh_plus=(float)$g[0];
			  
			  $sql2='select sum(p.value)
			   from payment as p
			   left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			    where p.org_id="'.$org_id.'" and p.is_incoming=0 and p.org_bdetails_id="'.$f['id'].'" and p.is_confirmed=1 and (p.given_pdate < "'.$pdate1.'" ) order by p.given_pdate asc';
			  $set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
			  $rs2=$set2->GetResult();
			  $g=mysqli_fetch_array($rs2);
			  $vh_minus=(float)$g[0];
			  
			  
			  
			  $doc_begin_ost=$vh_plus-$vh_minus;
			  $bank_begin_ost=$vh_plus-$vh_minus;
			  
			  
			  //переберем все оплаты в период по данному банку
			  $sql2='select p.*,
			  sp.full_name as supplier_name, opf.name as supplier_opf,
			  inu.name_s as inu_name_s, inu.login as inu_login
			   from payment as p
			   left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			    where p.org_id="'.$org_id.'" and p.org_bdetails_id="'.$f['id'].'" and p.is_confirmed=1 and (p.given_pdate between "'.$pdate1.'" and "'.$pdate2.'") order by p.given_pdate asc';
			  $set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
			  $rs2=$set2->GetResult();
			  $rc2=$set2->GetResultNumRows();
			   
			  
			  
			   
			   
			  $bank_plus=0;  $bank_minus=0; 
			  for($j=0; $j<$rc2; $j++){
				  $g=mysqli_fetch_array($rs2);
				  foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				  
								  
				  $g['pdate']=date("d.m.Y",$g['pdate']);
				  $g['given_pdate_unf']=$g['given_pdate'];
				  
				  $g['given_pdate']=date("d.m.Y",$g['given_pdate']);
				  
				  if($g['is_incoming']==1){
				  
				  	$g['plus_unf']=$g['value'];
				  	$g['plus']=number_format(round($g['value'],2),2,'.',$dec_sep);
					
					$g['minus_unf']=0;
				  	$g['minus']=number_format(0,2,'.',$dec_sep);
				  
				  }else{
					$g['plus_unf']=0;
				  	$g['plus']=number_format(0,2,'.',$dec_sep);
					
					$g['minus_unf']=$g['value'];
				  	$g['minus']=number_format(round($g['value'],2),2,'.',$dec_sep);
				   
				  }
				 
				 
				  $total_plus+=$g['plus_unf'];
				  $bank_plus+=$g['plus_unf'];
				  
				  $total_minus+=$g['minus_unf'];
				  $bank_minus+=$g['minus_unf'];
				  
				  $g['begin_ost_unf']=$doc_begin_ost;
				  $g['begin_ost']=number_format(round($g['begin_ost_unf'],2),2,'.',$dec_sep);
				  
				  $g['after_ost_unf']=$doc_begin_ost+$g['plus_unf']-$g['minus_unf'];
				  $g['after_ost']=number_format(round($g['after_ost_unf'],2),2,'.',$dec_sep);
				  
				  
				  
				  $doc_begin_ost+=$g['plus_unf']-$g['minus_unf'];
				  
				  
				  $subs[]=$g;  
			  }
			  
			  $f['plus']=number_format(round($bank_plus,2),2,'.',$dec_sep);
			  
			  $f['minus']=number_format(round($bank_minus,2),2,'.',$dec_sep);
			  //$f['subs']=$subs;
			  
			  $f['begin_ost']=number_format($bank_begin_ost,2,'.',$dec_sep);
			  
			  $f['after_ost']=number_format(round($bank_begin_ost+$bank_plus-$bank_minus,2),2,'.',$dec_sep);
			  
			  
			  //$subs - массив оплат... сделаем цикл по датам!
			  
			  
			  $current_date=$pdate1;
			  
			  $dates=array();
			  $pdate_begin_ost=$bank_begin_ost;
			  do{
				  //$dates[]=$current_date;
				  
				  //оплаты на текущую дату
				  $plus=0; $minus=0;
				  $pays=array();
				  foreach($subs as $k=>$v){
					  if($v['given_pdate_unf']==$current_date){
						  
						  $plus+=$v['plus_unf'];
						  $minus+=$v['minus_unf'];
						  
						  $pays[]=$v;	
					  }
						  
				  }
				  
				  
				  //if(count($pays)>0) var_dump($pays);
				  $dates[]=array(
					  'pdate'=>date('d.m.Y',$current_date),
					  'plus'=>number_format($plus,2,'.',$dec_sep),
					  'minus'=>number_format($minus,2,'.',$dec_sep),
					  'after_ost'=>number_format($pdate_begin_ost+$plus-$minus,2,'.',$dec_sep),
					  'begin_ost'=>number_format($pdate_begin_ost,2,'.',$dec_sep),
					  'subs'=>$pays
				  );
				  
				  //$current_date+=24*60*60;
				  $current_date=strtotime(date('d.m.Y H:i:s',$current_date) .' +1 day');
				   
				  $pdate_begin_ost+=$plus-$minus;
			  }while($current_date<$pdate2);
			  
			  
			  $f['dates']=$dates;
			  
			  $alls[]=$f;
		  }
		}
	   $sm->assign('items',$alls);
		
		
	   
	   
	   
	    $sm->assign('total_plus',number_format(round($total_plus,2),2,'.',$dec_sep));
	    $sm->assign('total_minus',number_format(round($total_minus,2),2,'.',$dec_sep));	
		
		
		//начальный вход. остаток по всем банкам...
		
		$sql2='select sum(p.value)
			   from payment as p
			   left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			    where p.org_id="'.$org_id.'" and p.is_incoming=1 and   p.is_confirmed=1 and (p.given_pdate < "'.$pdate1.'" ) order by p.given_pdate asc';
			  $set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
			  $rs2=$set2->GetResult();
			  $g=mysqli_fetch_array($rs2);
			  $vh_plus=(float)$g[0];
			  
			  $sql2='select sum(p.value)
			   from payment as p
			   left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			    where p.org_id="'.$org_id.'" and p.is_incoming=0   and p.is_confirmed=1 and (p.given_pdate < "'.$pdate1.'" ) order by p.given_pdate asc';
			  $set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
			  $rs2=$set2->GetResult();
			  $g=mysqli_fetch_array($rs2);
			  $vh_minus=(float)$g[0];
		
		$all_banks_begin_ost=$vh_plus-$vh_minus;	  
		
		
		$sm->assign('begin_ost',number_format(round($all_banks_begin_ost,2),2,'.',$dec_sep));//0.00);
		$sm->assign('after_ost',number_format(round($all_banks_begin_ost+$total_plus-$total_minus,2),2,'.',$dec_sep));	
		
		
	   
	   //заполним шаблон полями
		$current_storage='';
		$current_bank_id='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			

			if($v->GetName()=='bank_id') $current_bank_id=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
	   
	   
	   
	   //банки
		$_banks=new BDetailsGroup;
		$sgs=$_banks->GetItemsByIdArr($org_id); //>GetItemsArr(0,1);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$storage_names_selected=array();
		foreach($sgs as $k=>$v){
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['bank'];
			if(in_array($v['id'],$bank_id)) $storage_names_selected[]=$v['bank'];	
		}
		
		$sm->assign('bank_ids',$sender_storage_ids);
		$sm->assign('bank_names',$sender_storage_names);
		$sm->assign('bank_names_selected',$storage_names_selected);
		//var_dump($bank_id);
		$sm->assign('bank_id',$current_bank_id);
	   
	   
	   
	   
	   
	   
	   
	   
	   
	   
	   
		
		
		
		
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>