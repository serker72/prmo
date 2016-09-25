<?

require_once('authuser.php');

//генерация меню в зависимости от прав пользователя
class MenuBuilder{
	protected $items;
	
	protected $current_branch_id=0; 
	
	function __construct($res=NULL){
		$this->items=array();
		
		$au=new AuthUser;
		  
	    if($res===NULL) $res=$au->Auth();
		
		
		$set=new mysqlSet('select * from left_menu_new order by parent_id, ord desc,  id asc');
		$rs=$set->Getresult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			
			if($f['object_id']==0){
				$item=new MenuAllItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['description'], $f['url'], $f['ord'], $f['is_messages'], $f['is_uploads'], $res['id'], $f['is_komplekts'],$f['is_pic'], $f['pic'], $f['has_open_tag'], $f['has_close_tag'], $f['has_style_indent'], $f['is_tasks'], $f['is_missions'], $f['is_messenger'], $f['is_memos'], $f['is_petitons'], $f['is_switch'],  $f['has_searchform_after']); //,  $f['is_orders'],  $f['is_my_orders'],  $f['is_pret'],  $f['is_my_pret'],  $f['is_claim'],  $f['is_my_claim'],);	
				$item->SetAuthResult($res);
			}elseif(($f['for_supply_user']==1)&&($res['is_supply_user']==1)){
			
				//echo 'zzzzzzzzzzzzz';	
				$item=new MenuAllItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['description'], $f['url'], $f['ord'], $f['is_messages'], $f['is_uploads'], $res['id'], $f['is_komplekts'],$f['is_pic'], $f['pic'], $f['has_open_tag'], $f['has_close_tag'], $f['has_style_indent'], $f['is_tasks'], $f['is_missions'], $f['is_messenger'], $f['is_memos'], $f['is_petitons'], $f['is_switch'],  $f['has_searchform_after']);
				$item->SetAuthResult($res);
			}else{
				
				$item=new MenuSecItem($f['id'], $f['parent_id'], $f['object_id'], $f['name'], $f['description'], $f['url'], $f['ord'], $f['is_messages'], $f['is_uploads'], $res['id'], $f['is_komplekts'],$f['is_pic'], $f['pic'], $f['has_open_tag'], $f['has_close_tag'], $f['has_style_indent'], $f['is_tasks'],  $f['is_missions'], $f['is_messenger'], $f['is_memos'], $f['is_petitons'], $f['is_switch'],  $f['has_searchform_after']); // $f['is_orders'],  $f['is_my_orders'],  $f['is_pret'],  $f['is_my_pret'],$f['is_claim'],  $f['is_my_claim']);	
				$item->SetAuthResult($res);
			}
			
			$this->AddToMenu($item);
			
		}
		
			
	}
	
	public function SetBranch($current_branch_id){
		$this->current_branch_id=$current_branch_id;
	}
	
	
	
	//добавка итема к меню
	public function AddToMenu(MenuItem $item){
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
?>