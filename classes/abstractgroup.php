<?
require_once('NonSet.php');
require_once('MysqlSet.php');
require_once('authuser.php');

// ����������� ������
class AbstractGroup {
	protected $tablename;//='mmenu';
	protected $pagename;//='viewsubs.php';	
	protected $subkeyname;//='mmenu';
	protected $vis_name;
	protected $_auth_result;
	
	
	public function __construct(){
		$this->init();
	}
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='table';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	//������ �������
	public function GetItems($mode=0,$from=0,$to_page=10){
		
		$txt='';

		
		return $txt;
	}
	
	//������ �������
	public function GetItemsById($id, $mode=0,$from=0,$to_page=10){
		
		$txt='';

		
		return $txt;
	}
	
	
	//������ �������
	public function GetItemsByIdTemplate($id, $template, $current_id=0, $is_shown=0){
		
		$txt='';
		$arr=$this->GetItemsByIdArr($id, $current_id, $is_shown);
		$sm=new SmartyAdm;
		$sm->assign('items', $arr);
		$sm->assign('id', $id);
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by  id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'="1" order by  id asc');
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
	
	
	//������ �������
	public function GetItemsTemplate($template, $current_id=0,  $is_shown=0){
		
		$txt='';
		$arr=$this->GetItemsArr($current_id,  $is_shown);
		$sm=new SmartyAdm;
		$sm->assign('items', $arr);
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	//������ �������
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by  id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by  id asc');
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
	
	
	//������� ������
	public function CalcItemsById($id, $mode=0){
		
		if($mode==0){
			$query='select count(*) from '.$this->tablename.' where '.$this->subkeyname.'='.$id.';';
		}else{
			$query='select count(*) from '.$this->tablename.' where '.$this->vis_name.'=1 and '.$this->subkeyname.'='.$id.';';
		}
		
		$countt=new mysqlSet($query);
		$rez=$countt->getResult();
		$re = mysqli_fetch_array($rez);
		unset($countt);
		return $re['count(*)'];
	}
	
	
	
	//��������� ������������ ���� id
	public function SetIdName($name='mid'){
		$this->subkeyname=$name;
	}
	
	//��������� ����� ��������
	public function SetPageName($name='subs.php'){
		$this->pagename=$name;
	}
	
	//��������� ����� �������
	public function SetTableName($name='subs'){
		$this->tablename=$name;
	}
	
	public function GetTableName(){
		return $this->tablename;
	}
	
	//��������� ������������ ���� id
	public function GetIdName(){
		return $this->subkeyname;
	}
	
	
	
	//����� � ����� option
	public function GetItemsOpt($current_id=0,$fieldname='name', $do_no=false, $no_caption='-��������-'){
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
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	
	//��������� ����ov �� ������ �����
	public function GetItemsByFieldsArr($params){
		$res=array();
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		$item=new mysqlSet('select * from '.$this->tablename.' where '.$qq.';');
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		//unset($item);
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v){
				$f[$k]=stripslashes($v);	
			}
			$res[]=$f;
		}
		
		
		return $res;
	}
	
	
	public function SetAuthResult($result){
		$this->_auth_result=$result;	
	}
	
	public function GetPageName(){ return $this->pagename; }
}
?>