<?

require_once('discr_table_object.php');


//полный список объектов для построения таблиц администрирования
//с проверкой возможности администрирования объектов данным пользователем
class DiscrTableObjects{
	protected $user_id;
	protected $obj_ids;
	protected $tables=array();
	
	
	function __construct($user_id, array $obj_ids){
		$this->user_id=$user_id;
		$man=new DiscrMan;
		//CheckAccess($user_id, $right_letter, $object_id)
		
		foreach($obj_ids as $k=>$v){
			if($man->CheckAccess($user_id, 'x', $v)){
				$this->AddObject(new DiscrTableObject($v));	
			}
		}
	}
	
	
	//вывод всех таблиц
	public function Draw($filename, $template){
		$sm=new SmartyAdm;
		
		$arr=array();
		foreach($this->tables as $k=>$v){
			$arr[]=$v->DrawArr();
		}
		
		$sm->assign('ao',$arr);	
		$sm->assign('filename',$filename);
		
		return $sm->fetch($template);
	}
	
	protected function AddObject(DiscrTableObject $dto){
		$this->tables[]=$dto;
	}
	
}
?>