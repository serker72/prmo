<?
require_once('abstractgroup.php');

// абстрактная группа
class UsersGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		
		 if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by name_s asc, id asc');
		 else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'=1 order by name_s asc, id asc');
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
			
		}
		
		return $arr;
	}
	
	//итемы в тегах option
	public function GetItemsOpt($current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-'){
		$txt='';
		$sql='select * from '.$this->tablename.' order by '.$fieldname.' asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname].' ('.$f['login'].') '.$f['position_s']))."</option>";
			}
		}
		return $txt;
	}
	
	
	
	//список кураторов
	public function GetCuratorsArr($current_id=0, $fieldname='curator_obor_id'){
		$sql='select * from user where is_active=1 or id in (select distinct '.$fieldname.' from supplier) order by name_s asc, login asc';
		
		//echo $sql;
		
		$alls=array();
		$as=new mysqlSet($sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		
		
		$alls[]=array('id'=>0, 'text'=>'-выберите-', 'is_current'=>($current_id==0), 'is_active'=>1);
		
		for($i=0; $i<$rc; $i++){	
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($v as $k=>$v) $f[$k]=stripslashes($v);
			$f['text']=$f['name_s'].' ('.$f['login'].')';
			
			$alls[]=$f;
		}
		
		
		return $alls;
	}
	
	
	
	
	//список пол-лей по декоратору массив
	public function GetItemsByDecArr( DBDecorator $dec){
		$arr= array();
			
		$sql='select u.*  from '.$this->tablename.'  as u
	 
	 
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		 
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$arr[]=$f;
			}
		}
		return $arr;
	}

}
?>