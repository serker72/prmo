<?
require_once('abstractitem.php');
require_once('posgroupgroup.php');

//category итем 
class PosGroupItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='catalog_group';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		
		$this->keyname='group_id';	
		$this->subkeyname='parent_group_id';	
	}
	
	
	//удалить
	/*public function Del($id){
		//удалять ВСЕ!!!!
		
		$query = 'delete from catalog_position_in_group where '.$this->keyname.'='.$id.';';
		$it=new nonSet($query);
		
		AbstractItem::Del($id);
	}	**/
	
	//контроль возможности удаления
	public function CanDelete($id){
		$can_delete=true;
		
		
		
		
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($id);
		$arg=array(); $arg[]=$id;
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		//echo 'select count(*) from catalog_position where group_id in ("'.implode(', ',$arg).'")';
		
		
		$set=new mysqlSet('select count(*) from catalog_position where group_id in ('.implode(', ',$arg).')');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		$set=new mysqlSet('select count(*) from bill_position as p inner join bill as b on b.id=p.bill_id where p.position_id in (select id from catalog_position where group_id in ('.implode(', ',$arg).')) and (b.is_confirmed_price=1 or b.is_confirmed_shipping=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
	/*	$set=new mysqlSet('select count(*) from interstore_position as p inner join interstore as b on b.id=p.interstore_id where p.position_id in (select id from catalog_position where group_id in ('.implode(', ',$arg).')) and (b.is_confirmed=1 or b.is_confirmed_wf=1 or b.is_confirmed_fill_wf=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;*/
		
		
		$set=new mysqlSet('select count(*) from komplekt_ved_pos as p inner join komplekt_ved as b on b.id=p.komplekt_ved_id  where b.status_id in(2, 12, 13) and p.position_id in (select id from catalog_position where group_id in ('.implode(', ',$arg).'))');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		/*$set=new mysqlSet('select count(*) from sh_i_position as p inner join sh_i as b on b.id=p.sh_i_id where p.position_id in (select id from catalog_position where group_id in ('.implode(', ',$arg).')) and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;*/
		
		$set=new mysqlSet('select count(*) from acceptance_position as p inner join acceptance as b on b.id=p.acceptance_id where p.position_id in (select id from catalog_position where group_id in ('.implode(', ',$arg).'))  and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		return $can_delete;
	}
	
}
?>