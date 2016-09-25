<?
require_once('global.php');
require_once('useritem.php');



//����� ��� ���������� ������ � ������
class SemiAuthUser{
	//����������� ��������� �������
	static protected $profile;
	
	
	//��������� ������ ������ �������������
	public $users_sessions;
	
	//��������� ������ ������� ���� ������������
	public $user_rights;
	
	
	protected $sess_name; //��� ������, ��� ����
	protected $show_errors=true;
	protected $err_mess=Array();
	protected $err_code=1;
	
	protected $max_cookie_time=900; //3900; //����� ����� �������
	
	public function __construct($sess_name='ap_pm'){
		$this->init($sess_name);
	}
	
	//��������� ���� ����
	protected function init($sess_name){
		$this->sess_name=$sess_name;
		
	 
		
		if(!isset($_SESSION[$this->sess_name])) $_SESSION[$this->sess_name]=Array();
		
		$this->err_mess[1]='�������� �����/������!';
		$this->err_mess[2]='�������� �����/������!';
		$this->err_mess[3]='�������� �����/������!';
		$this->err_mess[4]='�������� �����/������!';
		$this->err_mess[5]='���������� �������� ������������!';
		$this->err_mess[6]='���������� ��������������� �������!';
		$this->err_mess[7]='������ ����������� ��� �������������� �������.';
		
		$this->err_mess[8]='��������� ���� �����!';
		$this->err_mess[9]='����� ����� ��� ����������!';
		
		$this->err_mess[10]='������� �������� ������!';
		$this->err_mess[11]='��������� ���� e-mail!';
		 
		$this->err_mess[12]='������ ������ � ��������'; //������ ����, �� ��� ��������
		
		$this->err_mess[13]='����� ������ � ��� ������������� �� ���������!'; //
		
		$this->err_mess[14]='������ �������� ������� ������!'; //�������� ������� ������ ��� ������ �������
		$this->err_mess[15]='������ �������� ������� ������!';
		
	}
	
	//������� ������
	public function DeAuthorize(){
		 self::$profile=NULL;
		@setcookie($this->sess_name."user[0]", '',time()-84000,'/');
		@setcookie($this->sess_name."user[1]", '',time()-84000,'/');
		 
		@setcookie($this->sess_name."user[2]", '',time()-84000,'/');
		
		
	}
	
	
	//������� ����������� (���������� �� ������, ������)
	public function Authorize($login,$password,$rem_me=false){
		if($this->AuthUser($login, $password)){
		//	$_SESSION[$this->sess_name]['username']=$login;
	//		$_SESSION[$this->sess_name]['password']=$password;
			//if($rem_me){
				@setcookie($this->sess_name."user[0]", $login,time()+$this->max_cookie_time,'/');				
				@setcookie($this->sess_name."user[1]", $password,time()+$this->max_cookie_time,'/');	
				@setcookie($this->sess_name."user[2]", '',time()-84000,'/');
				
			//}
		//	unset($_SESSION[$this->sess_name]['org_id']);
			
		}
	}
	
	 
	//������� �������� ����������� (���������� ������, ������ � ��.)
	public function Auth($login ){
		$err_code=1;//����������� �� ���������	
		//print_r( debug_backtrace());
		//echo '<h1>��� </h1>';
		 
		
		if(isset($_COOKIE[$this->sess_name.'user'])){
			
			$value['username'] = $login; // $_COOKIE[$this->sess_name.'user']['0'];
			$value['password'] = $_COOKIE[$this->sess_name.'user']['1'];			
			if(@$this->AuthUser(($value['username']), $value['password']))	{
				 
					@setcookie($this->sess_name."user[0]", $value['username'],time()+$this->max_cookie_time,'/');				
					@setcookie($this->sess_name."user[1]", $value['password'],time()+$this->max_cookie_time,'/');	
				 
				
				$err_code=0;//��� ������
			}else{
				$err_code=2;//����� �� ������� ��� � ��
			}			
		  
		
		}else{	
			$err_code=3;//����� �� ���������, ������� ���		
			
		}
		
		
		if($err_code==0){
			
			
		}
	 
		$this->err_code=$err_code;
		return self::$profile;
	}
	
	//������� �������� ������-������
	//���� ��� �������� ��������, �� ���������� ���, � ��������� �������
	//���� ��� �������� �� ��������, �����-� ����, ������ ������� ����� NULL
	public function AuthUser($login,$password){
		$us=new mysqlSet("select * from user where login=\"".SecStr($login,10)."\" and password=\"".SecStr($password,10)."\" and is_active=1;");
		
		$nr=$us->getResult();
		$rc=$us->GetResultNumRows();
		if($rc>=1){
			$f =  mysqli_fetch_array($nr);
			
			 
			self::$profile=$f;
			return true;
		}else{
			
			self::$profile=NULL;
			 
		    return false;
		}
	}
	
	
	
	
	   
	
	
	
	
	//���������� ������� �������
	public function GetProfile(){
		return self::$profile;
	}
	
	//���������� ������� ������� ����
	public function GetRightsTable(){
		/*if($this->user_rights==NULL) return NULL;
		else{*/
			return $this->user_rights->GetRightsTable();	
		//}
		
	}
	
	//����� ��������� �� ������ �� ���� ������
	public function ShowError($code){
		if(($this->show_errors)&&isset($this->err_mess[$code])) return $this->err_mess[$code];
		else return '';
	}
	
	public function GetErrorCode(){
		return $this->err_code;	
	}
	
	//��������� ����� ������ ������
	public function SetShowErrors($flag){
		$this->show_errors=$flag;
	}
	 
	public function GetSessionName(){
		return $this->sess_name;
	} 
}
?>