<?

require_once('abstractgroup.php');

//  ������ edinic izmereniya ��������
class PosDimGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='catalog_dimension';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
	}
	
	
	//������ �������
	public function GetItemsArr(){
		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		
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
	
}
?>