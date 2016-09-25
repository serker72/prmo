<?
require_once('abstractgroup.php');
require_once('discr_man.php');
require_once('komplitem.php');

// абстрактная группа
class KomplConfGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_confirm';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
		
	//список утверждений заявки
	public function GetItemsByIdArr($id,$current_id=0,$object_id=84,$komplekt_ved=NULL,$sector_ss=NULL, $storage_ss=NULL){
		$arr=array();
		
		//идем от пользователей, их ролей и доступа к об-ту 84
		
		$_ki=new KomplItem;
		if($komplekt_ved===NULL)
			$komplekt_ved=$_ki->GetItemById($id);
		
		
		
		//echo $sql;
		
		$sql='select role.*,
		kc.pdate, 
		u.id as u_id, u.name_s as u_name_s, u.login as u_login, u.position_s as position_s
		
		from komplekt_ved_confirm_roles as role
		left join '.$this->tablename.' as kc on (role.id=kc.role_id  and kc.'.$this->subkeyname.'="'.$id.'")
		left join user as u on kc.user_id=u.id
		
		order by role.ord desc, role.id';
		
		
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//is_selected
			if($f['u_id']=="") $f['is_selected']=false;
			else $f['is_selected']=true;
			
			$is_active=false;
			$man=new DiscrMan;
			if($f['is_selected']){
				//права на снятие утв-ия
			 
				
				$mode=$man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss, $storage_ss,array('unconfirm_object_id','ss_unconfirm_object_id'))]); 
				
				/*
				//если это роль Рук-ль объекта (5) и у заявки участок - участок механизации (7) - проверить доступ к объекту 551 (555)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==7)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 551,555)));
				
				
				
				//если это роль Рук-ль объекта (5) и у заявки участок - участок мех. цех (9) - проверить доступ к объекту 550 (554)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==9)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 550,554)));
				
				//если это роль Рук-ль объекта (5) и у заявки участок - участок Метромостстрой (18) - проверить доступ к объекту 591 (593)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==18)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 591,593)));*/
				
				
				$is_active=$mode;
				
				//var_dump($man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 'unconfirm_object_id','ss_unconfirm_object_id'))])); 
			}else{
				
				//права на установку утв-ия
				
				//если это праймари роль, и если у заявки участок - Центральный офис (11), то проверить доступ к супер-объекту 304
				
				$mode=$man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 'confirm_object_id','ss_confirm_object_id'))]);
				
				//if(($f['is_primary']==1)&&($komplekt_ved['sector_id']==11)) $mode=$mode||$man->CheckAccess($current_id,'w',$f['super_object_id']);
				
				
				//если это праймари роль, и если у заявки объект - Битцевский парк (20), то проверить доступ к  объекту 532 (533)
				/*if(($f['is_primary']==1)&&($komplekt_ved['storage_id']==20)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 532,533)));
				
				
				//если это роль Рук-ль объекта (5) и у заявки участок - участок механизации (7) - проверить доступ к объекту 549 (553)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==7)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 549,553)));
				
				
				
				//если это роль Рук-ль объекта (5) и у заявки участок - участок мех. цех (9) - проверить доступ к объекту 548 (552)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==9)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 548,552)));
				
				
				//если это роль Рук-ль объекта (5) и у заявки участок - участок Метромостстрой (18) - проверить доступ к объекту 590 (592)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==18)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 590,592)));
				*/
				
				
				$is_active=$mode;
				 //||$man->CheckAccess($current_id,'w',85)||$man->CheckAccess($current_id,'w',$f['super_object_id']);
				
			}
			
			//выполнить проверку, утверждено ли по первичной роли:
			$is_active=$is_active&&$this->CheckPrimaryRole($id, $f['id']);
			
			
			//если это роль - начальник участка:
			//должна быть особая проверка - если утвердили остальные - то нельзя снять начальника участка
			if($f['is_primary']==1){
				
				$set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id<>"'.$f['id'].'" and komplekt_ved_id="'.$id.'"');	
				
				$rs1=$set1->GetResult();
				$g1=mysqli_fetch_array($rs1);
				if((int)$g1[0]>0) $is_active=$is_active&&false;
						
			}
			
			
			$f['is_active']=$is_active;
			
			
			$f['pdate']=date("d.m.Y H:i:s", $f['pdate']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//сколько галочек утверждения по заявке
	public function CountUtv($id){
		$res=0;
		$sql='select count(*)
		
		from '.$this->tablename.' as kc where kc.'.$this->subkeyname.'="'.$id.'"';
		
		
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		
		$res=(int)$f[0];
		return $res;
	}
	
	//проверить разрешение утверждения: утвердил ли начальник участка
	public function CheckPrimaryRole($komplekt_id, $current_role_id=0){
		$res=true;
		
		//есть ли вообще primary роль
		$set=new mysqlset('select * from komplekt_ved_confirm_roles where is_primary=1 limit 1');	
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		if($rc>0){
			//есть
			$f=mysqli_fetch_array($rs);
			
			//если текущая роль и есть primary - то дать утвердить
			if($current_role_id!=$f['id']){
			   
			  //предусмотреть: нельзя снять утверждение первичной роли, если есть утверждения вторичные?
			
			   
			  //проверяем, есть ли утверждение по найденной первичной роли в данной заявке	
			  $set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id="'.$f['id'].'" and komplekt_ved_id="'.$komplekt_id.'"');	
			  $rs1=$set1->GetResult();
			  $rc1=$set1->GetResultNumRows();
			  $g=mysqli_fetch_array($rs1);
			  
			  if((int)$g[0]>0) $res=true;
			  else $res=false;
			  
			  //если сняли утверждение первичной роли, если по данной роли есть утверждение - дать снять его...
			   $set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id="'.$current_role_id.'" and komplekt_ved_id="'.$komplekt_id.'"');	
			  $rs1=$set1->GetResult();
			  $rc1=$set1->GetResultNumRows();
			  $g=mysqli_fetch_array($rs1);
			  
			   if((int)$g[0]>0) $res=true;
			 
			  
			}
		}
			
			
		return $res;
	}
	
	
	//просто проверка, есть ли праймари роль
	public function HasPrimaryRole($komplekt_id){
		$res=true;
		
		
		
		//есть ли вообще primary роль
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where role_id in(select id from komplekt_ved_confirm_roles where is_primary=1) and komplekt_ved_id="'.$komplekt_id.'"');	
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	
	//просто проверка, есть ли НЕ праймари роль
	public function HasUnPrimaryRole($komplekt_id){
		$res=true;
		
		
		
		//есть ли вообще primary роль
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where role_id in(select id from komplekt_ved_confirm_roles where is_primary=0) and komplekt_ved_id="'.$komplekt_id.'"');	
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//проверка, есть ли хоть 1 утверждение
	public function HasAnyConfirm($komplekt_id){
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"');	
		
		//echo 'select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"';
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//проверка, есть ли ВСЕ утверждения
	public function HasAllConfirm($komplekt_id){
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"');	
		
		//echo 'select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"';
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$conf=((int)$f[0]);
		
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm_roles');
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$roles=((int)$f[0]);
		
		return ($conf==$roles);
	}
	
	
	//проверка, есть ли утв данной заявки данным пол-лем в данной роли
	public function HasConfirmByUserRole($komplekt_ved_id, $user_id, $role_id){
		
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_ved_id.'" and user_id="'.$user_id.'" and role_id="'.$role_id.'"');	
		
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//проверка, есть ли утв данной заявки данной роли
	public function HasConfirmByRole($komplekt_ved_id,  $role_id){
		
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_ved_id.'"  and role_id="'.$role_id.'"');	
		
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	
	//список всех утверждений заявки
	public function GetPointsArr($komplekt_ved_id){
		$arr=array();
		
		$sql='select * from '.$this->tablename.' where komplekt_ved_id="'.$komplekt_ved_id.'" ';
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f;
		}
		
		return $arr;	
	}
	
}
?>