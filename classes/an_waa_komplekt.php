<?

require_once('abstractgroup.php');
require_once('komplitem.php');
require_once('komplblink.php');
require_once('authuser.php');
require_once('komplnotesgroup.php');
require_once('komplnotesitem.php');
require_once('komplconfgroup.php');

//  
class AnWaaKomplekt extends AbstractGroup {
	
	protected $_auth_result;
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved';
		$this->pagename='an_waa.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_auth_result=NULL;
		
	}
	
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$can_edit=false, $can_delete=false,$pdate1=NULL, $pdate2=NULL, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL, $can_create=false, $can_eq=false, $do_show=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		$_au=new AuthUser;
		//$_aures=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$_kov=new KomplItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					 sup.full_name as supplier_name, opf.name as opf_name, 
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					utv.id as utv_id, utv.name_s as utv_name, utv.login as utv_login
				from '.$this->tablename.' as p
					 left join supplier as sup on p.supplier_id=sup.id 
					left join opf on opf.id=sup.opf_id
					left join user as mn on p.manager_id=mn.id
					left join user as utv on p.cannot_an_id=utv.id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
				 	 left join supplier as sup on p.supplier_id=sup.id 
					left join opf on opf.id=sup.opf_id
					left join user as mn on p.manager_id=mn.id
					left join user as utv on p.cannot_an_id=utv.id
					';
				 
		$db_flt1=$dec->GenFltSql(' and ');
		 
		
		if((strlen($db_flt1)>0)||(strlen($date_flt)>0)){
			if(strlen($db_flt1)>0){
				$sql.=' where '.$db_flt1;
				$sql_count.=' where '.$db_flt1;		
			}
			
			if(strlen($date_flt)>0){
				if(strlen($db_flt1)>0){
					$sql.=' and '.$date_flt;
					$sql_count.=' and '.$date_flt;
				}else{
					$sql.=' where '.$date_flt;
					$sql_count.=' where '.$date_flt;
				}
			}
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		if($do_show){
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRows();
		
		
	 
		
		$alls=array();
		$_bng=new KomplNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['begin_pdate']=date("d.m.Y",$f['begin_pdate']);
			$f['end_pdate']=date("d.m.Y",$f['end_pdate']);
			
			if($f['pdate']==0) $f['pdate']='-';
			else $f['pdate']=date("d.m.Y",$f['pdate']);
			
			
			if($f['cannot_an_pdate']==0){
				$f['utv_pdate']=' ';	
			}else $f['utv_pdate']=date("d.m.Y H:i:s",$f['cannot_an_pdate']);
			
			//print_r($f);	
			
			
			$color='black';
			$f['blink']=$_kov->kompl_blink->OverallBlink($f['id'], $f['status_id'], $_aures['id'], $_aures['is_supply_user'], $color,NULL,NULL,$f['sector_s_s'],$f['storage_s_s']);
			$f['color']=$color;
			
			//$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			$f['can_annul']=$_kov->DocCanAnnul($f['id'],$reason, $_aures['id'],$f,NULL,NULL,$f['sector_s_s'],$f['storage_s_s'])&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$_kov->GetBindedDocumentsToAnnul($f['id']);
			
			
			$alls[]=$f;
		}
		
		
		}
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$current_storage='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='storage_id3') $current_storage=$v->GetValue();
			if($v->GetName()=='sector_id3') $current_sector=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$as=new mysqlSet('select * from storage order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_storage==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ug',$acts);
		
		if($limited_sector!==NULL){
			 $_sql='select * from sector where id in('.implode(', ',$limited_sector).') order by name asc';
		}else $_sql='select * from sector order by name asc';
		
		
		$as=new mysqlSet($_sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_sector==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('sug',$acts);
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create',$can_create);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('can_eq',$can_eq);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode3=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
}
?>