<?
require_once('abstractgroup.php');
require_once('filemessagegroup.php');
require_once('messageitem.php');
require_once('user_to_user.php');


require_once('message_view.class.php');

// список сообщений
class MessageGroup extends AbstractGroup {
	
	protected $mf_tablename;
	protected $folder_tablename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='message';
		$this->mf_tablename='message_in_folder';
		$this->folder_tablename='folder';
		
		$this->_view=new Message_ViewGroup;
	}
	
	
	public function CalcNew($user_id){
		return $this->CalcInbox($user_id,1,1);	
	}
	
	
	//подсчет сообщений
	public function CalcInbox($user_id, $folder_id=1, $is_new=1){
		if(($is_new==0)||($is_new==1)) $set=new mysqlSet('select count(*) from '.$this->mf_tablename.' where folder_id="'.$folder_id.'" and message_id in(select id from '.$this->tablename.' where to_id="'.$user_id.'") and unread="'.$is_new.'"');
		else $set=new mysqlSet('select count(*) from '.$this->mf_tablename.' where folder_id="'.$folder_id.'" and message_id in(select id from '.$this->tablename.' where to_id="'.$user_id.'")');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return $f[0];
	}
	
	public function CalcOut($user_id, $folder_id=1, $is_new=1){
		if(($is_new==0)||($is_new==1)) $set=new mysqlSet('select count(*) from '.$this->mf_tablename.' where folder_id="'.$folder_id.'" and message_id in(select id from '.$this->tablename.' where from_id="'.$user_id.'") and unread="'.$is_new.'"');
		else $set=new mysqlSet('select count(*) from '.$this->mf_tablename.' where folder_id="'.$folder_id.'" and message_id in(select id from '.$this->tablename.' where from_id="'.$user_id.'")');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return $f[0];
	}
	
	//список папок с количеством сообщений
	public function GetFoldersArr($user_id){
		$arr=Array();
		$set=new mysqlSet('select * from '.$this->folder_tablename.' as f order by f.ord asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['id']==1){
				$f['total']=$this->CalcInbox($user_id, $f['id'], 2);
				$f['unread']=$this->CalcInbox($user_id, $f['id'], 1);
			}else{
				$f['total']=$this->CalcOut($user_id, $f['id'], 2);
				$f['unread']=$this->CalcOut($user_id, $f['id'], 1);
			}
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список сообщений в папке
	public function GetMessagesArr($user_id, $folder_id, DBDecorator $dec,$from=0, $to_page=ITEMS_PER_PAGE,$your=' (Вы)'){  //$from=0, $to_page=10){
		$arr=array();
		
		
		if($folder_id==1){
			//сообщения для пользователя
			$sql='select m.id as id, m.from_id as from_id, m.to_id as to_id, m.topic as topic, m.pdate as pdate, m.txt as txt, 
			tf.pdate as f_pdate, tf.unread as unread, 
			s.login as s_login, s.group_id as s_group_id, s.name_s as s_name_s,
			r.login as r_login, r.group_id as r_group_id, r.name_s as r_name_s 
			from '.$this->tablename.' as m 
			inner join  '.$this->mf_tablename.' as tf on (m.id=tf.message_id and tf.folder_id="'.$folder_id.'") 
			
			left join user as s on m.from_id=s.id
			left join user as r on m.to_id=r.id
			
			where m.to_id="'.$user_id.'" ';
			
			
			$sql_count='select count(*) from '.$this->tablename.' as m 
			inner join  '.$this->mf_tablename.' as tf on (m.id=tf.message_id and tf.folder_id="'.$folder_id.'") 
			left join user as s on m.from_id=s.id
			left join user as r on m.to_id=r.id
			
			where m.to_id="'.$user_id.'" ';
			
		}else{
			//сообщения от пользователя
			$sql='select m.id as id, m.from_id as from_id, m.to_id as to_id, m.topic as topic, m.pdate as pdate, m.txt as txt, 
			tf.pdate as f_pdate, tf.unread as unread, 
			
			s.login as s_login, s.group_id as s_group_id, s.name_s as s_name_s, 
			r.login as r_login, r.group_id as r_group_id, r.name_s as r_name_s 
			
			from '.$this->tablename.' as m 
			inner join  '.$this->mf_tablename.' as tf on (m.id=tf.message_id and tf.folder_id="'.$folder_id.'") 
			
			left join user as s on m.from_id=s.id
			left join user as r on m.to_id=r.id
			where m.from_id="'.$user_id.'" 
			
			';
			$sql_count='select count(*) from '.$this->tablename.' as m 
			inner join  '.$this->mf_tablename.' as tf on (m.id=tf.message_id and tf.folder_id="'.$folder_id.'") 
			left join user as s on m.from_id=s.id
			left join user as r on m.to_id=r.id
			where m.from_id="'.$user_id.'" ';
			
		}
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
				
		$set=new mysqlSet($sql,$to_page,$from,$sql_count);
		//echo $sql;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$total=$set->GetResultNumRowsUnf();
		$result=Array();
		
		
			$pages='';
		
		//page
		$navig = new PageNavigator('messages.php',$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		if($folder_id==1) $navig->SetFirstParamName('from');
		else $navig->SetFirstParamName('from_2');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		//if($folder_id!=1) $pages.='#tabs-2';
		
		$all=array();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['pdate']=date("d.m.Y H:i:s", $f['pdate']);
			
			
			if($folder_id==1) $f['is_new']=$f['unread'];
			if($f['from_id']==-1) {
				$f['s_login']='GYDEX:Автоматическая система рассылки сообщений';
				$f['s_name_s']='GYDEX:Автоматическая система рассылки сообщений';
			}
			
			if($user_id==$f['from_id']){
				 $f['s_login'].=$your;
				 $f['s_name_s'].=$your;
			}
			if($user_id==$f['to_id']){
				 $f['r_login'].=$your;
				 $f['r_name_s'].=$your;
			}

			
			
			//вложения
			$fmg=new FileMessageGroup;
			$files=$fmg->GetItemsByIdArr($f['id']);
			$f['files']=$files;
			//print_r($f);
			
			$result[]=$f;
		}
		
		$arr['items']=$result;
		$arr['pages']=$pages;
		
		//показ конфигурации
		$arr['view']= $this->_view->GetColsArr($this->_auth_result['id']);   //$thresult['id']);
		$arr['unview']= $this->_view->GetColsUnArr($this->_auth_result['id']);
		
	//	print_r($this->_view->GetColsArr($this->_auth_result['id']));
		
		
		return $arr;
	}
	
	
	//список адресатов для формы написания письма
	public function DrawAdrArr($current_id, $is_admin=false, $sort_mode=1){
		$arr=array();
		$flt='';
		$flt=' and is_active=1  and group_id<>2 ';
		
		$srt='';
		if($sort_mode==1) $srt=' order by login asc, name_s asc';
		elseif($sort_mode==2) $srt=' order by name_s asc, login asc';
		
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($current_id);
		$limited_user=$u_to_u['sector_ids'];
		
		$flt.=' and id in('.implode(', ', $limited_user).') '; 
		
		
	//	if(!$is_admin) $flt=' and (group_id=1 or group_id=2)';
		$set=new mysqlSet('select * from user where id<>"'.$current_id.'" '.$flt.' '.$srt);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr[]=array('id'=>0, 'login'=>'-выберите-', 'name_s'=>'-выберите-');
		
		if($is_admin){
			//$arr[]=array('id'=>-1, 'login'=>'-все пользователи-');
			$arr[]=array('id'=>-2, 'login'=>'-все сотрудники -', 'name_s'=>'-все сотрудники -');
		//	$arr[]=array('id'=>-3, 'login'=>'-все дилеры D-');
		}else{
			$arr[]=array('id'=>-2, 'login'=>'-все сотрудники-', 'name_s'=>'-все сотрудники -');
		}

		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;	
		}
		
		
		return $arr;	
	}
	
	
	
	
		//показ цепочки писем 
	public function GetChain($parent_id, $template, $template1, $current_user_id, $folder_id=1){
		$sm=new SmartyAj;
		$mi=new MessageItem;
		
		$message=$mi->GetItemById($parent_id);
		$ui=new UserItem;
		$user=$ui->GetItemByid($message['from_id']);	
		
		$user_r=$ui->GetItemByid($message['to_id']);	
		
				
		//если сообщение во входящих, то пометим его прочитанным...
		//пометим сообщение как прочитанное
		if($folder_id==1){
			//$mi->SetRead($parent_id, $message);	
		}
		if($message['from_id']==-1) {
			$user['login']='GYDEX:Автоматическая система рассылки сообщений';
			$user['name_s']='GYDEX:Автоматическая система рассылки сообщений';
		}
		if($message['from_id']==$current_user_id) {
			$user['login'].=' (Вы)';
			$user['name_s'].=' (Вы)';
		}
		$sm->assign('s_login',stripslashes($user['login']));
		
		$sm->assign('s_user',($user));
		
		if($message['to_id']==$current_user_id) {
			$user_r['login'].=' (Вы)';
			$user_r['name_s'].=' (Вы)';
		}

		$sm->assign('r_login',stripslashes($user_r['login']));
		
		$sm->assign('r_user',($user_r));
		
		
		$sm->assign('message',(stripslashes($message['txt'])));
		
		
		//найти этот id по всей цепочке сообщений
		
		
		$sm->assign('id',$parent_id);
		$sm->assign('message_id',stripslashes($parent_id));
		
		//вложения
		$fmg=new FileMessageGroup;
		$files=$fmg->GetItemsByIdArr($parent_id);
		$sm->assign('files',$files);
		
		//поле ответа Кому
		if($message['from_id']!=$current_user_id)	$sm->assign('to_id',$message['from_id']);
		elseif($message['to_id']!=$current_user_id)	 $sm->assign('to_id',$message['to_id']);
		else $sm->assign('to_id',$message['from_id']);
		
		
		$to_users=$mi->FindRecepients($message['id'], $message);
		foreach($to_users as $k=>$v){
			if($v['id']==$current_user_id) {
				$to_users[$k]['login'].=' (Вы)';
				$to_users[$k]['name_s'].=' (Вы)';
			}
		}
		$sm->assign('to_users',$to_users);
		
		if(($folder_id==1)&&($message['from_id']!=-1)){
			 $sm->assign('can_answer',true);
		}
		$sm->assign('has_showall_block',true);
		
		return $sm->fetch($template);	
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
			$sm=new SmartyAj;
			
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
			//var_dump($mi->FindRecepients($message['id'], $message));
			
			$message['txt']=(stripslashes($message['txt']));
			
			$message['pdate']=date("d.m.Y H:i:s", $message['pdate']);
			
			//вложения
			$fmg=new FileMessageGroup;
			$files=$fmg->GetItemsByIdArr($message['id']);
			$message['files']=$files;
			
			
			$th_messages=array($message);
			$sm=new SmartyAj;
				
			$sm->assign('answers',$th_messages);
			$res.=$sm->fetch($template);
		}
		
		//последующие сообщения
		$res.=$this->GetNode($id,'m.parent_id',$template,$current_user_id,'id');
		
		 return $res;
		
	}
	
	//рекурсивный охват ветви сообщений
	public function GetNode($key, $keyfield='id', $template, $current_user_id, $go_keyfield='id'){
		$sm=new SmartyAj;
		
		
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
	
	
	//рекурсивный охват ветви сообщений - МАССИВ
	public function GetNodeArr($key, $keyfield='id', $current_user_id, $go_keyfield='id', &$messages){
		
		
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
			
			
			if($f['from_id']==$current_user_id){
				 $f['s_login'].=' (Вы)';
				 $f['s_name_s'].=' (Вы)'; 
			}
			if($f['to_id']==$current_user_id) {
				$f['r_login'].=' (Вы)';
				$f['r_name_s'].=' (Вы)';
			}
			
			 
			$to_users=$mi->FindRecepients($f['id']);
			foreach($to_users as $k=>$v){
				if($v['id']==$current_user_id) {
					$to_users[$k]['login'].=' (Вы)';
					$to_users[$k]['name_s'].=' (Вы)';
				}
			}
			
			$f['to_users']=$to_users;
			
		//	var_dump( $f[$go_keyfield]);
			
			if($f[$go_keyfield]!=0){
				/*$f['subs']=*/
				
				$this->GetNodeArr($f[$go_keyfield], $keyfield, $current_user_id, $go_keyfield, $messages); //FindChain($f['id'],$template1,$main_id, $current_user_id);
			}
			//$f['main_id']=$main_id;
			
			$f['txt']=($f['txt']);
			
			//вложения
			$fmg=new FileMessageGroup;
			$files=$fmg->GetItemsByIdArr($f['id']);
			$f['files']=$files;
			
			
			$all[]=$f;
			
			
		}
		foreach($all as $k=>$v) $messages[]=$v;
		return $all;
	}
}
?>