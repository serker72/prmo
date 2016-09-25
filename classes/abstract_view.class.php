<?
require_once('abstractgroup.php');
require_once('abstractitem.php');


//������ ������� ������������
class Abstract_ViewGroup extends AbstractGroup {
	protected $col_tablename;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_view';
		$this->col_tablename='supplier_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	//������ �������� ������������
	public function GetColsArr($user_id=0){
		$alls=array();
		
		//� ���� �� ������ ������������?
		 
		$flt='';
		$scan_zero=false;
		$sql='select count(*) from '.$this->tablename.' as v where v.user_id="'.$user_id.'" ';
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		 
		$f=mysqli_fetch_array($rs);
		if((int)$f[0]==0){
			//������������ ���...
			//��������� �������
			//���� ���������� ������� - ������ �� �����������
			if($user_id==0) $scan_zero=false; else $scan_zero=true;
				
			
		}
		
		if($scan_zero){
			//������� ����
			 $sql='select v.*, vf.name, vf.colname from
			'.$this->tablename.' as v inner join
			'.$this->col_tablename.' as vf on v.col_id=vf.id
			where v.user_id="0" 
			order by v.ord asc
			';
			 
			/*$sql='select * from '.$this->col_tablename.'
			where id not in(select distinct col_id from '.$this->tablename.' where user_id=0)
			order by v.ord asc';*/
			
					
		}else{
			//��������� ����
			 $sql='select v.*, vf.name, vf.colname from
			'.$this->tablename.' as v inner join
			'.$this->col_tablename.' as vf on v.col_id=vf.id
			where v.user_id="'.$user_id.'" 
			order by v.ord asc
			'; 
			
			/*$sql='select * from '.$this->col_tablename.'
			where id not in(select distinct col_id from '.$this->tablename.' where user_id="'.$user_id.'")
			order by v.ord asc';*/
		}
		
		//echo $sql.'<br>';
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	//������ �������� ��� ������������
	public function GetColsUnArr($user_id=0){
		$alls=array();
		
		//� ���� �� ������ ������������?
		 
		$flt='';
		$scan_zero=false;
		$sql='select count(*) from '.$this->tablename.' as v where v.user_id="'.$user_id.'" ';
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		 
		$f=mysqli_fetch_array($rs);
		if((int)$f[0]==0){
			//������������ ���...
			//��������� �������
			//���� ���������� ������� - ������ �� �����������
			if($user_id==0) $scan_zero=false; else $scan_zero=true;
				
			
		}
		
		if($scan_zero){
			//������� ����
			/*$sql='select v.*, vf.name, vf.colname from
			'.$this->tablename.' as v inner join
			'.$this->col_tablename.' as vf on v.col_id=vf.id
			where v.user_id="0" 
			order by v.ord asc
			';
			*/
			$sql='select * from '.$this->col_tablename.'
			where id not in(select distinct col_id from '.$this->tablename.' where user_id=0)
			 ';
			
					
		}else{
			//��������� ����
			/*$sql='select v.*, vf.name, vf.colname from
			'.$this->tablename.' as v inner join
			'.$this->col_tablename.' as vf on v.col_id=vf.id
			where v.user_id="'.$user_id.'" 
			order by v.ord asc
			';*/
			
			$sql='select * from '.$this->col_tablename.' as v
			where id not in(select distinct col_id from '.$this->tablename.' where user_id="'.$user_id.'")
			 ';
		}
		
		//echo $sql;
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	
	//������� ����� ��������� ������������
	public function Clear($user_id){
		if($user_id!=0){
			new NonSet('delete from '.$this->tablename.' where user_id="'.$user_id.'"');	
			
		}
	}
	
	
}

//������� ������� ������������
class Abstract_ViewItem extends AbstractItem{
	protected function init(){
		$this->tablename='supplier_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class Abstract_ColItem extends AbstractItem{
	protected function init(){
		$this->tablename='supplier_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class Abstract_ColGroup extends AbstractGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>