<?
require_once('abstractgroup.php');
require_once('filemessagegroup.php');
require_once('messageitem.php');
require_once('user_to_user.php');
require_once('messagegroup.php');

// список сообщений (
class MessageGroupPrint extends MessageGroup {
	
	protected $mf_tablename;
	protected $folder_tablename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='message';
		$this->mf_tablename='message_in_folder';
		$this->folder_tablename='folder';
	}
	
	     
	//показ всей переписки
	public function ShowAllMessages($id,$template, $current_user_id){
		//$sm=new SmartyAj;
		$res='';
		
		$mi=new MessageItem;
		//предыдущие сообщения
		$message=$mi->GetItemById($id);
		if(($message!==false)&&($message['parent_id']!=0)){
			$messages=array();
			$this->GetNodeArr($message['parent_id'],'m.id', $current_user_id,'parent_id', $messages); 
			//print_r($messages);
			$sm=new SmartyAdm;
			
			$sm->assign('answers',$messages);
			$res.=$sm->fetch($template);
			
		}
		
		//это сообщение
		if($message!==false){
			$ui=new UserItem;
			$user=$ui->GetItemByid($message['from_id']);	
			
			$user_r=$ui->GetItemByid($message['to_id']);	
			
			$message['s_login']= stripslashes($user['login']);
			$message['r_login']= stripslashes($user_r['login']);
			
			$message['s_name_s']= stripslashes($user['name_s']);
			//$message['s_name_d']= stripslashes($user['name_d']);
			
			$message['r_name_s']= stripslashes($user_r['name_s']);
			//$message['r_name_d']= stripslashes($user_r['name_d']);
			
			$message['s_group_id']=$user['group_id'];
			$message['r_group_id']=$user_r['group_id'];
			
			if($message['from_id']==-1) {
				$message['s_login']='GYDEX:Автоматическая система рассылки сообщений';
				$message['s_name_s']='GYDEX:Автоматическая система рассылки сообщений';
			}
			if($message['from_id']==$current_user_id){
				 $message['s_login'].=' (Вы)';
				 
				 $message['s_name_s'].=' (Вы)';
				 
			}
			
			
			if($message['to_id']==$current_user_id){
				 $user_r['login'].=' (Вы)';
				 $user_r['name_s'].=' (Вы)';
			}
			
			//получатели
			$to_users=$mi->FindRecepients($message['id']);
			foreach($to_users as $k=>$v){
				if($v['id']==$current_user_id) {
					$to_users[$k]['login'].=' (Вы)';
					$to_users[$k]['name_s'].=' (Вы)';
				}
			}
		 
			
			$message['to_users']=$to_users;
			
			$message['txt']=(stripslashes($message['txt']));
			
			$message['pdate']=date("d.m.Y H:i:s", $message['pdate']);
			
			//вложения
			$fmg=new FileMessageGroup;
			$files=$fmg->GetItemsByIdArr($message['id']);
			$message['files']=$files;
			
			
			$th_messages=array($message);
			$sm=new SmartyAdm;
				
			$sm->assign('answers',$th_messages);
			$res.=$sm->fetch($template);
		}
		
		//последующие сообщения
		$res.=$this->GetNode($id,'m.parent_id',$template,$current_user_id,'id');
		
		 return $res;
		
	}
	
	//рекурсивный охват ветви сообщений
	public function GetNode($key, $keyfield='id', $template, $current_user_id, $go_keyfield='id'){
		$sm=new SmartyAdm;
		
		
		$sql='select  m.*,
				s.login as s_login, s.group_id as s_group_id, s.name_s as s_name_s, 
				r.login as r_login, r.group_id as r_group_id, r.name_s as r_name_s 
			from
				'.$this->tablename.' as m
				left join user as s on m.from_id=s.id
				left join user as r on m.to_id=r.id
			
		    where '.$keyfield.'="'.$key.'" and(m.from_id="'.$current_user_id.'" or m.to_id="'.$current_user_id.'") order by pdate asc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$mi=new MessageItem;
		
		
		$all=array(); 
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s", $f['pdate']);
			
			//echo '<br>ZZZZZZZZZZZZZZZZZZZZZZ<br>';
			if($f['from_id']==-1){
				 $f['s_login']='GYDEX:Автоматическая система рассылки сообщений';
				 	 $f['s_name_s']='GYDEX:Автоматическая система рассылки сообщений';
				 
			}
			
			if($f['from_id']==$current_user_id) {
				$f['s_login'].=' (Вы)';
					$f['s_name_s'].=' (Вы)';
			}
			if($f['to_id']==$current_user_id){
				 $f['r_login'].=' (Вы)';
				 	 $f['r_name_s'].=' (Вы)';
			}
			
			
			//var_dump( $f);
			
			$to_users=$mi->FindRecepients($f['id']);
			foreach($to_users as $k=>$v){
				if($v['id']==$current_user_id) {
					$to_users[$k]['login'].=' (Вы)';
					$to_users[$k]['name_s'].=' (Вы)';
				}
			}
			
			$f['to_users']=$to_users;
			
			if($f[$go_keyfield]!=0){
				$f['subs']=$this->GetNode($f[$go_keyfield], $keyfield, $template, $current_user_id, $go_keyfield);
			}
			//$f['main_id']=$main_id;
			
			$f['txt']=($f['txt']);
			
			//вложения
			$fmg=new FileMessageGroup;
			$files=$fmg->GetItemsByIdArr($f['id']);
			$f['files']=$files;
			
			
			$all[]=$f;
			
			
		}
		$sm->assign('answers',$all);
		
		
		
		return $sm->fetch($template);	
	}
	 
}
?>