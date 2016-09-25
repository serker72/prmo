<?
require_once('global.php');
require_once('useritem.php');



//класс для клиентской работы с юзером
class SemiAuthUser{
	//статическая структура профиля
	static protected $profile;
	
	
	//экземпляр класса сессий пользователей
	public $users_sessions;
	
	//экземпляр класса таблицы прав пользователя
	public $user_rights;
	
	
	protected $sess_name; //имя сессии, где юзер
	protected $show_errors=true;
	protected $err_mess=Array();
	protected $err_code=1;
	
	protected $max_cookie_time=900; //3900; //время жизни кукисов
	
	public function __construct($sess_name='ap_pm'){
		$this->init($sess_name);
	}
	
	//установка всех имен
	protected function init($sess_name){
		$this->sess_name=$sess_name;
		
	 
		
		if(!isset($_SESSION[$this->sess_name])) $_SESSION[$this->sess_name]=Array();
		
		$this->err_mess[1]='Неверный логин/пароль!';
		$this->err_mess[2]='Неверный логин/пароль!';
		$this->err_mess[3]='Неверный логин/пароль!';
		$this->err_mess[4]='Неверный логин/пароль!';
		$this->err_mess[5]='Невозможно добавить пользователя!';
		$this->err_mess[6]='Невозможно отредактировать профиль!';
		$this->err_mess[7]='Ошибка авторизации при редактировании профиля.';
		
		$this->err_mess[8]='Заполните поле Логин!';
		$this->err_mess[9]='Такой логин уже существует!';
		
		$this->err_mess[10]='Слишком короткий пароль!';
		$this->err_mess[11]='Заполните поле e-mail!';
		 
		$this->err_mess[12]='Ошибка работы с профилем'; //ошибка есть, но код потеряли
		
		$this->err_mess[13]='Новый пароль и его подтверждение не совпадают!'; //
		
		$this->err_mess[14]='Введен неверный текущий пароль!'; //неверный текущий пароль при правке профиля
		$this->err_mess[15]='Введен неверный текущий пароль!';
		
	}
	
	//функция выхода
	public function DeAuthorize(){
		 self::$profile=NULL;
		@setcookie($this->sess_name."user[0]", '',time()-84000,'/');
		@setcookie($this->sess_name."user[1]", '',time()-84000,'/');
		 
		@setcookie($this->sess_name."user[2]", '',time()-84000,'/');
		
		
	}
	
	
	//функция авторизации (авторизуем по логину, паролю)
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
	
	 
	//функция проверки авторизации (опрашивает сессии, кукисы и пр.)
	public function Auth($login ){
		$err_code=1;//авторизация не выполнена	
		//print_r( debug_backtrace());
		//echo '<h1>АВТ </h1>';
		 
		
		if(isset($_COOKIE[$this->sess_name.'user'])){
			
			$value['username'] = $login; // $_COOKIE[$this->sess_name.'user']['0'];
			$value['password'] = $_COOKIE[$this->sess_name.'user']['1'];			
			if(@$this->AuthUser(($value['username']), $value['password']))	{
				 
					@setcookie($this->sess_name."user[0]", $value['username'],time()+$this->max_cookie_time,'/');				
					@setcookie($this->sess_name."user[1]", $value['password'],time()+$this->max_cookie_time,'/');	
				 
				
				$err_code=0;//нет ошибок
			}else{
				$err_code=2;//юзера из кукисов нет в бд
			}			
		  
		
		}else{	
			$err_code=3;//никто не залогинен, кукисов нет		
			
		}
		
		
		if($err_code==0){
			
			
		}
	 
		$this->err_code=$err_code;
		return self::$profile;
	}
	
	//функция проверки логина-пароля
	//если все проверки пройдены, то возвращает тру, и обновляет профиль
	//если все проверки НЕ пройдены, возвр-т фолс, вместо профиля будет NULL
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
	
	
	
	
	   
	
	
	
	
	//возвращает текущий профиль
	public function GetProfile(){
		return self::$profile;
	}
	
	//возвращает текущую таблицу прав
	public function GetRightsTable(){
		/*if($this->user_rights==NULL) return NULL;
		else{*/
			return $this->user_rights->GetRightsTable();	
		//}
		
	}
	
	//вывод сообщения об ошибке по коду ошибки
	public function ShowError($code){
		if(($this->show_errors)&&isset($this->err_mess[$code])) return $this->err_mess[$code];
		else return '';
	}
	
	public function GetErrorCode(){
		return $this->err_code;	
	}
	
	//установка флага показа ошибок
	public function SetShowErrors($flag){
		$this->show_errors=$flag;
	}
	 
	public function GetSessionName(){
		return $this->sess_name;
	} 
}
?>