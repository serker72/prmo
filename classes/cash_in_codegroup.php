<?
require_once('NonSet.php');
require_once('abstractgroup.php');

// ����������� ������
class CashInCodeGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='cash_in_code';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsArr($start_id=0, $current_id=0){
		$arr=array();
		
		$sql='select * from '.$this->tablename.' where parent_id="'.$start_id.'" order by  code  asc, length(code) asc, id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$subs=$this->GetItemsArr($f['id'], $current_id);
			
			$f['count_pos']=count($subs);
			$f['codespos']=$subs;
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//���������� ������ ������ �� ����������
	protected function build_array($pcg, &$arr){
		foreach($pcg as $k=>$v){
			
			if(count($v['codespos'])>0) $this->build_array($v['codespos'], $arr);
			$arr[]=$v;	
		}
		
	}
	
	
	//������ �������
	public function GetItemsArrFlatted($start_id=0, $current_id=0){
		$arr=array();
		
		$pcg=$this-> GetItemsArr($start_id, $current_id);
		
		$this->build_array($pcg, $arr);
		
		
		return $arr;
	}
}
?>