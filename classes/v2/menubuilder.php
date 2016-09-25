<?

require_once('menuitem.php');
require_once('menusecitem.php');
require_once('menuallitem.php');

//генерация меню в зависимости от прав пользователя
class  GYDEX_MenuBuilder{
	protected $items;
	protected $place_kind=2;
	
	function __construct($place_kind=2, $res=NULL){
		$this->items=array();
		$this->place_kind=$place_kind;
		
		$au=new authuser;
		  
	    if($res===NULL) $res=$au->Auth();
		
		$sql='select * from gydex_menu where place_kind="'.$this->place_kind.'"  order by parent_id, ord desc,  id asc';
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$rs=$set->Getresult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			
			if($f['object_id']==0){
				$item=new  GYDEX_MenuAllItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['description'], $f['url'], $f['ord'],  $f['is_pic'], $f['pic'], $f['module_constant']);  
				$item->SetAuthResult($res);
			
			}else{
				
				$item=new  GYDEX_MenuSecItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['description'], $f['url'], $f['ord'],  $f['is_pic'], $f['pic'], $f['module_constant']);  
				$item->SetAuthResult($res);
			}
			
			$this->AddToMenu($item);
			
		}
		
		
		 
			
	}
	
	
	//добавка итема к меню
	public function AddToMenu(GYDEX_MenuItem $item){
		$this->items[]=$item;	
	}
	
	
	//построение меню
	public function BuildMenu($user_id, $current_id=0){
		$arr=array();
		
	 
		$arr=$this->ConstructBranch(0, $current_id);
	
		return $arr;
	}
	
	
	protected function ConstructBranch($parent_id, $current_id=0){
		$arr=array();
		foreach($this->items as $k=>$v){
			
			
			if($v->parent_id==$parent_id){
				
					
			 
					 
				 $point=array();
				 $point['item']=$v->DeployItem($current_id);
				 if($point['item']!==NULL){
				 	
					$point['subs']=$this->ConstructBranch($v->id, $current_id);
					
					$was=false;
					foreach($point['subs'] as $pp){
						if($pp['item']['is_active']) $was=$was||true;	
					}
					if($was) $point['item']['is_active']=true;
					
					
					
					$arr[]=$point;
				 }
			}
		}
		
		/*echo '<pre>';
		print_r($arr);
		echo '</pre>';*/		
		return $arr;
	}
	
}
?>