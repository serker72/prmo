<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');

class AnPay{

	public function ShowData($supplier_id, $org_id, $pdate1, $pdate2, $template, DBDecorator $dec,$pagename='files.php', $manager=''){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		
		
		$sm=new SmartyAdm;
		$_org=new OrgItem;
		$_opf=new OpfItem;
		$org=$_org->getitembyid($org_id);
		$opf=$_opf->GetItemById($org['opf_id']);
		
		$sm->assign('org', stripslashes($opf['name'].' '.$org['full_name']));
		
		$sm->assign('manager',$manager);
		
		$sm->assign('supplier_name',$supplier['name']);
		
		$sm->assign('dogovor','№'.$supplier['contract_no'].' от '.$supplier['contract_pdate']);
		
		$sm->assign('period',date("d.m.Y H:i:s",$pdate1).' - '.date("d.m.Y H:i:s",$pdate2));
		
		
		
		
		
		
		
		
		//найдем остатки до и после
		//до
		//оплаты
		$before_ost=0;
		
		
		//после
		$after_ost=0;
		
		
		//найдем всех контрагентов, у кот. есть оплаты!
		
		
		
		$sql='select distinct s.* from supplier as s inner join payment as p on p.supplier_id=s.id
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" ';
		if($supplier_id>0) $sql.=' and s.id="'.$supplier_id.'"';
		$sql.=' order by s.name asc';
		
		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$alls=array();
		$total_dolg=0; $total_plus=0;
		for($i=0; $i<$rc; $i++){
			
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//перебор оплат контрагента
			
			$subs=array();
			
			
			//переберем оплаты
			$sql1='select * from payment where supplier_id="'.$f['id'].'" and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'") and is_confirmed=1';
			$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			for($j=0; $j<$rc1; $j++){
				$g=mysqli_fetch_array($rs1);
				$g['pdate']=date("d.m.Y H:i:s",$g['pdate']);
				//print_r($g);
				$g['kind']=1;
				$g['total']=$g['value'];
				$total_plus+=$g['total'];
				$subs[]=$g;	
			}
			
			
			
			
			
			$f['subs']=$subs;	
			$alls[]=$f;
		}
		
		$sm->assign('total_plus',$total_plus);
		$sm->assign('total_dolg',$total_dolg);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		//	if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			//if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//kontragent
		$as=new mysqlSet('select * from supplier where is_org=0 and org_id="'.$org_id.'" order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_supplier==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('sg',$acts);
		
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
}
?>