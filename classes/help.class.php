<?

require_once('authuser.php');

//генерация меню в зависимости от прав пользователя
class HelpBuilder{
	protected $items;
	
	protected $current_branch_id=0; 
	
	function __construct($res=NULL){
		$this->items=array();
		
		$au=new AuthUser;
		  
	    if($res===NULL) $res=$au->Auth();
		
		
		$set=new mysqlSet('select * from help_menu order by parent_id, ord desc,  id asc');
		$rs=$set->Getresult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
						
			if($f['object_id']==0){
				$item=new HelpAllItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['filename'],   $f['ord']); 
				$item->SetAuthResult($res);
			}else{
				
				$item=new HelpSecItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['filename'],   $f['ord'] );
				$item->SetAuthResult($res);
			}
			
			$this->AddToMenu($item);
			
		}
		
			
	}
	
	public function SetBranch($current_branch_id){
		$this->current_branch_id=$current_branch_id;
	}
	
	
	
	//добавка итема к меню
	public function AddToMenu(HelpItem $item){
		$this->items[]=$item;	
	}
	
	
	//построение меню
	public function BuildMenu($user_id, $current_id=0){
		$arr=array();
		$arr=$this->ConstructBranch($this->current_branch_id, $current_id);
		
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
						//var_dump($pp['item']['is_active']); echo '<br>';
						if($pp['item']['is_active']) $was=$was||true;	
					}
					if($was) $point['item']['is_active']=true;
					
					
					//echo $current_id.' vs '.$point['item']['id'].'<br>';
				/*	echo '<strong>total ';
					var_dump($was); echo '</strong>'; 
					
					echo '<br><br>';*/
					
					$arr[]=$point;
				 }
			}
		}
		return $arr;
	}
	
	
}

class HelpAllItem extends HelpItem{
	public function DeployItem($current_id=0){
		return $this->CodeItem($current_id);
	}

}


class HelpSecItem extends HelpItem{
	public function DeployItem($current_id=0){
		$arr=NULL;
		
		$au=new AuthUser;
		if($au->user_rights->CheckAccess('w', $this->object_id)){
			$arr=$this->CodeItem($current_id);	
		
		}
		
		return $arr;
	}

}



class HelpItem{
	public $id;
	public $parent_id;
	public $object_id;
	public $name;
	public $filename;
 
	public $ord;
 
	 
	protected $_auth_result;
	
 
	function __construct($id, $parent_id, $object_id, $name, $filename,  $ord){
		$this->id=$id;
		$this->parent_id=$parent_id;
		$this->object_id=$object_id;
		$this->name=$name;
		$this->filename=$filename;
		 
		$this->ord=$ord;
		 
		
		
	}
	
	public function DeployItem(){}
	
	protected function CodeItem($current_id=0){
		
		$au=new AuthUser;
		  
	 
	   
		
		$res=array('id'=>$this->id, 
					 'parent_id'=>$this->parent_id, 
					 'object_id'=>$this->object_id, 
					 'name'=>$this->name,
					 'filename'=>$this->filename,
					 'ord'=>$this->ord,
					 'is_active'=>(int)($current_id==$this->id)
					 
					 );	
		return $res;
	}
	
	public function SetAuthResult($result){
		$this->_auth_result=$result;	
	}
}


//элемент
class HelpElemItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='help_menu';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	public function TestItemById($id){
		$test_item=$this->GetItemById($id);
		
		if($test_item===false) return false;
		
		$au=new AuthUser;
		if(($test_item['object_id']!=0)&&!$au->user_rights->CheckAccess('w', $test_item['object_id'])){
			return false;
		}
		
		return $test_item;
		
	}
	
	public function TestItemByFilename($filename){
		$test_item=$this->GetItemByFields(array('filename'=>$filename));
		
		
		if($test_item===false) return false;
		
		$au=new AuthUser;
		if(($test_item['object_id']!=0)&&!$au->user_rights->CheckAccess('w', $test_item['object_id'])){
			return false;
		}
		
		return $test_item;
	}
	
}
?>