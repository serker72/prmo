<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('cash_in_codegroup.php');
require_once('cash_in_codeitem.php');

//очтет приход наличных
class AnCashIn{
	public $prefix='_10';
	
	public function ShowData(  $org_id, $pdate1, $pdate2, $template, DBDecorator $dec, $extended_an=0, $do_it=false, $can_print=false, $pagename='files.php'){
	 
		$_pcg=new CashInCodeGroup;
		$_pci=new CashInCodeItem;
		
		$sm=new SmartyAdm;
		$alls=array();
		
		if($do_it){
			//найдем весь список статей расходов
			$pcg=$_pcg->GetItemsArrFlatted(0);
			//var_dump($pcg);
			//найдем сумму по каждой статье!
			$value_pays=0;
			foreach($pcg as $k=>$v){
				$value=0;
				
				 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				//найдем расх. наличных - расход
				$sql='select sum(p.value) 
					from cash_in as p
					where p.is_confirmed_given=1 and   p.org_id="'.$org_id.'"
					    and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id="'.$v['id'].'"
						';
				//echo $sql.'<br>';		
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$value+=(float)$f[0]; 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				$v['value']=$value;
				
				$value_pays+=$value;
				
				$pcg[$k]=$v;	
			}
			
			 
				
			$sm->assign('value_pays',number_format($value_pays,2,'.',' '));
			
			if($extended_an==1){
				//детализация расходов по каждому пункту
				
				foreach($pcg as $k=>$v){
						//print_r($v); echo '<br>';	
						
						$v['docs']=$this->GetDocsCash($pdate1, $pdate2, $org_id, $v['id']);
						
						//print_r($v['docs']); echo '<br>';	
						
						$pcg[$k]=$v;
				}
						
				
			}
				
		}
		  
		  
		
		$sm->assign('items',$pcg);
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		//	if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			//if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			//if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('prefix',$this->prefix); 
		$sm->assign('can_print', $can_print);
		$sm->assign('do_it', $do_it);
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	
	//список  расходов нал за период
	public function GetDocsCash($pdate1, $pdate2, $org_id, $code_id){
		$_ai=new AccItem;
		$_ai_in=new AccInItem;
		
		$alls=array();
		$sql='
			 
			 
			  (select p.id, p.code, value as value, p.given_pdate as given_pdate, p.id as given_no,
			"3" as kind,
			sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
			"" as inu_name_s, "" as inu_login,
			 0 as is_inner_pay
			
				from cash_in as p
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   
			  
				
			where p.is_confirmed_given=1 and p.org_id="'.$org_id.'"
				and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
				and p.code_id ="'.$code_id.'"
			  )
			 
			   
			  
			  order by 4 asc,   6 asc, 1 asc
		';
		
		//echo $sql.'<br><br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($rs);	
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
		 
			
			$alls[]=$f;
		}	
		
		
		 
		
		return $alls;
	}
	
}
?>