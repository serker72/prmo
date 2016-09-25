<?

require_once('abstractgroup.php');
 

//  
class ProgramGroup extends AbstractGroup {
	protected $connection;
	protected $debug;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='program';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';	
		$this->debug=DEBUG_REDIRECT;	
		
		$this->connection=new MySQLi(ProgramHostName, ProgramUserName, ProgramPassword, ProgramUserName); //$program[$debug_prefix.'database_name']);
		$this->connection->query('set names cp1251');
	}
	
	
	
	public function FindAccess($login, $password, $template, &$matched, $current_org_id, $do_exclude=false){
		$sm=new SmartyAdm;
		
		$debug_prefix=''; if($this->debug) $debug_prefix='debug_';
		
		
		$progs=$this->GetItemsArr();
		
		
		$matched=array();
		if(strlen(trim($login))>0) foreach($progs as $k=>$program){
			$connection=new MySQLi(ProgramHostName, ProgramUserName, ProgramPassword, $program[$debug_prefix.'database_name']);
			$connection->query('set names cp1251');
			
			$query='select id from user where is_active=1 and email_s="'.SecStr($login).'" and email_s<>"" and password="'.($password).'" ';
			
			//echo $query; 
			 
			$result=$connection->query($query);
			$rec_no=$result->num_rows;
			
			if($rec_no==0) continue;
			//echo $rec_no;
			
			//echo ProgramHostName, ProgramUserName, ProgramPassword, $program[$debug_prefix.'database_name'];
			
			$program['login']=$login;
			$program['password']=$password;
			 
			if($program['has_orgs']==0) { 
				
			 
					  
						
					 	$program['org_id']=0;
						if($do_exclude){ if($program[$debug_prefix.'database_name']!=DBName) $matched[]=$program;}else{ $matched[]=$program; }
						
					 
			}else{
				//проверять организации	
				$f=mysqli_fetch_array($result);
					
				 
				$query1='select s.id as org_id, s.full_name, so.name as opf_name
					from supplier as s 
					left join opf as so on so.id=s.opf_id
					inner join supplier_to_user as su on su.org_id=s.id
				where
					s.is_org=1 and s.is_active=1 and su.user_id="'.$f['id'].'" ';
				
				//echo $query1.'<br>';
				
				$result2=$connection->query($query1);
				$rec_no2=$result2->num_rows;	
				
				for($j=0; $j<$rec_no2; $j++){
					$g=mysqli_fetch_array($result2);
					
					$program['org_id']=$g['org_id'];
					$program['opf_name']=$g['opf_name'];
					$program['full_name']=$g['full_name'];
					
					if($do_exclude){ 
						if(($program[$debug_prefix.'database_name']==DBName)
						&&($current_org_id==$program['org_id'])){
								
							
						}else $matched[]=$program;
					}else $matched[]=$program;
				}
				
			}
			
			 
			 
			
		
		}
		
		
		$sm->assign('matched', $matched);
		
		//print_r($matched);
		
		return $sm->fetch($template);	
		
	}
	
	
		//итемы в тегах option
	public function GetItemsArr(){
		$arr=array();
		$sql='select * from '.$this->tablename.' where is_active<>0 order by id asc';
		
		$rs=$this->connection->query($sql);
		//$set=new mysqlSet($sql);
		//$tc=$set->GetResultNumRows();
		$tc=$rs->num_rows;
		if($tc>0){
			 
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				
				$arr[]=$f; 
			}
		}
		
		 
		return $arr;
	}
	
}
?>