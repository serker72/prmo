<?
require_once('abstractgroup.php');
 
require_once('messengeritem.php');
require_once('authuser.php');

require_once('user_to_user.php');

// список сообщений мессенджера
class MessengerGroup extends AbstractGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='messenger_message';
		 
	}
	
	
	//новые сообщения для пользователя
	public function CalcNew($user_id){
		
		$flt='';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($user_id);
		$limited_user=$u_to_u['sector_ids'];
		
		$flt.=' and from_id in('.implode(', ', $limited_user).') '; 
		
		$sql='select count(*) from '.$this->tablename.' where to_id="'.$user_id.'" '.$flt.' and unread="1"';
		
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return $f[0];	
	}
	
	//новые сообщение от пользователя для пользователя
	public function CalcNewFrom($from_user_id, $to_user_id){
		$sql='select count(*) from '.$this->tablename.' where to_id="'.$to_user_id.'" and from_id="'.$from_user_id.'" and unread="1"';
		
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return $f[0];	
	}
	
	

	
	//список адресатов для формы написания письма
	public function DrawAdrArr($current_id, $is_admin=false, $sort_mode=1, $selected_users=array(), $result=NULL, $string_filter=''){
		$arr=array();
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		
		$flt='';
		$flt=' and group_id<>2 and u.is_active=1 ';
		if(strlen($string_filter)>0) $flt.=' and (u.login like "%'.$string_filter.'%" or u.name_s like "%'.$string_filter.'%" /*or u.name_d like "%'.$string_filter.'%" */) ';
		
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($current_id);
		$limited_user=$u_to_u['sector_ids'];
		
		$flt.=' and u.id in('.implode(', ', $limited_user).') '; 
		
		
		
		
		$srt='';
		if($sort_mode==1) $srt=' order by count_of_new_messages desc, is_online desc, u.login asc, u.name_s asc';
		elseif($sort_mode==2) $srt=' order by count_of_new_messages desc, is_online desc, u.name_s asc, u.login asc';
		
	//	if(!$is_admin) $flt=' and (group_id=1 or group_id=2)';
		$sql='select u.*, us.SID, count(mk.id) as count_of_new_messages,
		IF (us.SID IS NOT NULL,1,0) as is_online
		from user as u 
		left join user_session as us on us.user_id=u.id
		left join messenger_message as mk on mk.from_id=u.id and mk.to_id="'.$current_id.'" and mk.unread="1"  
		
		where u.id<>"'.$current_id.'" '.$flt.'
		group by u.id
		 '.$srt;
		
		//$sql='select count(*) from '.$this->tablename.' where to_id="'.$to_user_id.'" and from_id="'.$from_user_id.'" and unread="1"';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		
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
			
			//проверять, есть ли новые сообщения
			//var_dump( $this->CalcNewFrom($f['id'], $result['id']));
			//$f['is_new_messages']=($this->CalcNewFrom($f['id'], $result['id'])>0);
			$f['is_new_messages']=($f['count_of_new_messages']>0);
			
			//проверять, в ссистеме ли 
			//$f['is_online']=($f['SID']!=NULL);
			
			//проверять, выбран ли
			$f['is_selected']=in_array($f['id'], $selected_users);
			
			//if(!$f['is_selected'])
			
			$arr[]=$f;	
		}
		
		
		return $arr;	
	}
	
	
	
	
	//загрузка сообщений чата
	//from == $result['id']
	public function LoadChat($from_message, $days, $from, $to, $template, $is_ajax=false, $result=NULL){
		$arr=array();
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		
		
		$day_flt='';
		if($days>0) $day_flt.=' and m.pdate>="'.(time()-$days*24*60*60-1).'" ';
		
		
		//to может быть двух видов: целое число - письмо одному абоненту, стандартный режим переписки
		//массив идент-ров: только отправленные всем абонентам сообщения.
		$who_flt='';
		if(is_array($to)){
			$_who_flt=array();
			foreach($to as $k=>$v){
				$_who_flt[]=' m.to_id="'.$v.'" ';
					
			}
				
			$who_flt='and	(m.from_id="'.$from.'" and m.to_id IN ('.implode(', ', $to).')) ';
				
				
			$sql='select   m.from_id as from_id,  m.pdate as pdate,  m.txt as txt, 
			0 as unread, count(m.id) as c_m_id,
			s.login as s_login, s.group_id as s_group_id, s.name_s as s_name_s 
			 
			from '.$this->tablename.' as m 
			
			
			left join user as s on m.from_id=s.id
			 
			where
			m.id>"'.$from_message.'"
			
			'.$who_flt.'
			
			'.$day_flt.'
			
			
			group by m.from_id,  m.pdate, m.txt
			having c_m_id='.count($to).'
			order by m.pdate desc
			 ';	
			//echo $sql.'<br>';
		}else{
			$sql='select  m.id as id, m.from_id as from_id, m.to_id as to_id, m.topic as topic, m.pdate as pdate, m.txt as txt, 
			m.unread as unread, 
			s.login as s_login, s.group_id as s_group_id, s.name_s as s_name_s,
			r.login as r_login, r.group_id as r_group_id, r.name_s as r_name_s 
			from '.$this->tablename.' as m 
			
			
			left join user as s on m.from_id=s.id
			left join user as r on m.to_id=r.id
			
			where
			m.id>"'.$from_message.'"
			and			 
			(	(m.from_id="'.$from.'" and m.to_id="'.$to.'") or
			((m.from_id="'.$to.'" /*or m.from_id=-1*/) and m.to_id="'.$from.'") )
			
			'.$day_flt.'
			order by m.pdate desc, m.id desc
			 ';
		}
		
		
		
			
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$_mi=new MessengerItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//проверять, есть ли новые сообщения
			if($f['from_id']==-1) $f['s_login']='Автоматическая система рассылки сообщений';
			if(($to==$f['from_id'])&&($f['unread']==1)){
				$f['unread']=1;	
				
				//$_mi->SetRead($f['id'], $f, $result); //позже
			}else $f['unread']=0;	
			
			
			$f['pdate_unf']=$f['pdate'];
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if(!isset($f['id'])) $f['id']=$i; //для случая коллективных сообщений
			
			$arr[]=$f;	
		}
		
		
		$sm->assign('user_id', $from);
		$sm->assign('items', $arr);
		
		return $sm->fetch($template);
		
	}
	
}
?>