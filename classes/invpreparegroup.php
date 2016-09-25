<?
require_once('abstractgroup.php');
//require_once('iswf_group.php');
require_once('db_decorator.php');

// абстрактная группа
class InvPrepareGroup extends AbstractGroup {
	
	//установка всех имен
	protected function init(){
		$this->tablename='table';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	public function GetItemsByDateStorSec($pdate, $storage_id, $sector_id, $org_id,$is_id=0, DBDecorator $dec=NULL){
		$alls=array();
		
		$pdate+=24*60*60;
		//$_iwg=new IswfGroup;
		
		//получим номенклатуру на складе
		if($dec!==NULL){
		  $db_flt=$dec->GenFltSql(' and ');
		  if(strlen($db_flt)>0){
			  $db_flt=' and '.$db_flt;	
		  }
		}
			
		$sql2='select sum(ap.quantity) as quantity_as_is, 
				ap.position_id as id, ap.position_id as position_id, ap.position_id as pl_position_id, ap.name as position_name, ap.dimension as dim_name, dim.id as dimension_id 
				from acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				inner join bill as b on b.id=a.bill_id
				inner join catalog_position as cat on cat.id=ap.position_id
				left join catalog_dimension as dim on ap.dimension=dim.name 
			  where 
				a.is_incoming=1 and  
				a.is_confirmed=1 and a.org_id="'.$org_id.'"
				and a.pdate<="'.$pdate.'"
				 
				  and a.sector_id="'.$sector_id.'" '.$db_flt.'
				
				 group by ap.position_id  
				 order by position_name asc, ap.position_id asc
				 ';
				 
				 
				 
		$set=new mysqlSet($sql2);		
		//echo $sql2;
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//var_dump($f);
			
			$f['quantity_as_is']=(float)$f['quantity_as_is'];
			//получить суммарное количество по позиции
			
			
			//получим всего списано по данной позиции
			/*$set1=new mysqlSet('select sum(quantity) from interstore_position where 
			position_id="'.$f['id'].'"
			and pl_position_id="'.$f['pl_position_id'].'" 
			and interstore_id in(select id from interstore where is_confirmed=1 and org_id="'.$org_id.'"  and pdate<="'.$pdate.'")');
			*/
			
			//всего реализовано данной позиции:
			$sql1='select sum(ap.quantity) as quantity_as_is, 
				ap.position_id as id, ap.position_id as position_id,   ap.name as position_name, ap.dimension as dim_name, dim.id as dimension_id 
				from acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				inner join bill as b on b.id=a.bill_id
				inner join catalog_position as cat on cat.id=ap.position_id
				left join catalog_dimension as dim on ap.dimension=dim.name 
			  where 
				a.is_incoming=0 and  
				a.is_confirmed=1 and a.org_id="'.$org_id.'"
				and a.pdate<="'.$pdate.'"
				 
				and ap.position_id="'.$f['id'].'"
				 and a.sector_id="'.$sector_id.'" 
				
				 group by ap.position_id 
				 ';
				 
			//echo $sql1;	 
				 
			$set1=new mysqlSet($sql1);	
			
			$rs1=$set1->GetResult();
			
			$g=mysqli_fetch_array($rs1);
			
			//вычтем из склада
			$f['quantity_as_is']-=(float)$g[0];
			$f['quantity_initial']=$f['quantity_as_is'];
			
			
			
			
			
			//if($f['quantity_as_is']<0) $f['quantity_as_is']=0;
			//if($f['quantity_as_is']<=0) continue; //надо ли это????????
			
			$f['quantity_fact']=$f['quantity_as_is']; //0;
			
			
			$f['quantity_by_program']=$f['quantity_as_is'];
			$f['in_acc']=0;
			$f['in_wf']=0;
			
			
			$alls[]=$f;
		}
		
		return $alls;	
	}
	
}
?>