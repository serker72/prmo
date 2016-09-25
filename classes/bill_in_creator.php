<?
require_once('billcrsession.php');
require_once('billcreator.php');


class BillInCreator extends BillCreator{
	public $ses;
	protected $tablename;
	protected $digits=5;
	protected $begin;
	
	function __construct(){
		$this->ses=new BillCreationSession;	
		$this->tablename='bill';
		$this->begin='���';
	}
	
	
	//������� ���������� �������������� ��������� ��������� ����� � ��������� ��� � ������
	public function GenLogin($current_user_id){
		//login - code
		
		$set=new mysqlset('select max(code) from '.$this->tablename.' where code REGEXP "^'.$this->begin.'[0-9]+"');
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			
			//$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi($this->begin."([[:digit:]]{5})",$f[0],$regs);
			//print_r($regs);
			
			
			$number=(int)$regs[1];
			//print_r($regs); die();
			$number++;
			
			$test_login=$this->begin.sprintf("%0".$this->digits."d",$number);
			
			while($this->ses->CountLogins($test_login,$current_user_id)>0){
				$number++;
				$test_login=$this->begin.sprintf("%0".$this->digits."d",$number);
			}
			
			$this->ses->AddSession($current_user_id,$test_login);
			$login=$test_login;
		}else{
			
			//$f=mysqli_fetch_array($rs);	
			$login=$this->begin.sprintf("%0".$this->digits."d",1);
			
			$this->ses->AddSession($current_user_id,$login);
		}
		
		
		return $login;
	}
	
}
?>