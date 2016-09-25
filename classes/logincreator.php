<?
require_once('logincrsession.php');

class LoginCreator{
	public $ses;
	protected $tablename;
	protected $digits=4;
	
	function __construct(){
		$this->ses=new LoginCreationSession;	
		$this->tablename='user';
	}
	
	
	//функция возвращает гаранитрованно ближайший свободный логин и бронирует его в сессии
	public function GenLogin($group_id,$current_user_id){
		$set=new mysqlset('select max(login) from '.$this->tablename.' where group_id="'.$group_id.'"  ');
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$set1=new mysqlset('select login_letter from groups where id="'.$group_id.'"');
		
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi("[[:alpha:]]([[:digit:]]{4})",$f[0],$regs);
			//print_r($regs);
			$number=(int)$regs[1];
			$number++;
			
			$test_login=$f1[0].sprintf("%0".$this->digits."d",$number);
			
			while($this->ses->CountLogins($test_login,$current_user_id)>0){
				$number++;
				$test_login=$f1[0].sprintf("%0".$this->digits."d",$number);
			}
			
			$this->ses->AddSession($current_user_id,$test_login);
			$login=$test_login;
		}else{
			
			$f=mysqli_fetch_array($rs);	
			$login=$f[0].sprintf("%0".$this->digits."d",1);
			
			$this->ses->AddSession($current_user_id,$login);
		}
		
		
		return $login;
	}
	
}
?>