<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/users_activity.php');

require_once('../classes/user_s_group.php');
require_once('../classes/cachereports.php');
require_once('../classes/an_index.php');
 

 require_once('../classes/suppliersgroup.php');
  require_once('../classes/orgsgroup.php');
require_once('../classes/an_supplier.php');
require_once('../classes/an_pm_graf.php');
require_once('../classes/an_re.php');

require_once('../classes/db_decorator.php');
 
//��������!
$year=date('Y');
		$quarts=array(
			array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year)),
			array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year)),
			
			array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year)),
			
			array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year)),
			
		
		); 

if(isset($_GET['mode'])){
	$mode=abs((int)$_GET['mode']);	
}else $mode=3;



$_pdate=mktime(0,0,0,date('m'),date('d'), date('Y'));

//���������� ������� ����� � ����������� ������ �� ������� ������
function LogData($mode, $pdate11, $pdate12){
	echo '������ � '.date('d.m.Y H:i:s', $pdate11). ' do '.date('d.m.Y H:i:s', $pdate12);
	
	$_ua=new UsersActivity;
	$_cache=new CacheReportsItem;
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	
	$_ug=new UsersSGroup;
	$users=$_ug->GetItemsArr(0,1);
	
	//print_r($users);
	
	foreach($users as $user){
		
		//echo ' ��������� '. $user['name_s'];
		
		$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>$mode, 'user_id'=>$user['id']));	
		
		$decorator=new DBDecorator;
			  
		$decorator->AddEntry(new SqlEntry('pdate',$pdate11, SqlEntry::BETWEEN,$pdate12));
		$decorator->AddEntry(new SqlEntry('login',SecStr($user['login']), SqlEntry::E));
		 
		$ua=$_ua->ShowLog('',$decorator,new DbDecorator(),0,100, $total);
		
		//echo '�������� '.$total."\n";
		 
		
		if($m===false) $_cache->Add(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total, 'user_id'=>$user['id'])); 
		else $_cache->Edit($m['id'], array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total, 'user_id'=>$user['id']));
	}
	
}

//���������� ������� ����� � ����������� ������ �� +/-
function LogDataPm($mode, $pdate11, $pdate12){
	echo '������ � '.date('d.m.Y H:i:s', $pdate11). ' do '.date('d.m.Y H:i:s', $pdate12);
	
	$suppliers=array();
	
	$_cache=new CacheReportsItem;
	
	$_sg=new SuppliersGroup;
	
	$_ap=new AnPmGraf;
	 
	
	$_orgs=new OrgsGroup;
	$orgs=$_orgs->GetItemsArr(0,1);
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	
	
	
	foreach($orgs as $org){
		$sg=$_sg->GetItemsWithOpfArr(false, $org['id']);
		
		
		foreach($sg as $k=>$supplier){
			$dec=new DBDecorator;
			
			
			//if($supplier['full_name']!='����') continue;
			
			
			$_ap->ShowData($supplier['id'], $org['id'],$pdate11, $pdate12,'',$dec, '', true,  false, '.', $alls,  
			true,
			 1,1, 1,'', true,NULL, $total_pm, $total_marja);
			
			//� ������
			//echo $supplier['full_name'].' ����� +- '.($total_pm).'<br>'; //-$alls['total_marja']).'<br>';
			
			$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>$mode, 'supplier_id'=>$supplier['id']));	
				
				if($m===false){
					 $_cache->Add(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total_pm, 'supplier_id'=>$supplier['id']));  
					 
					// print_r(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$value, 'supplier_id'=>$supplier['id']));
					 
				}else $_cache->Edit($m['id'], array('pdate'=>$marker, 'kind'=>$total_pm, 'value'=>$value, 'supplier_id'=>$supplier['id'])); 
			
			
			
			
		}
		
	}
	
}



//���������� ������� ����� � ����������� ������ �� ��
function LogDataRe($mode, $pdate11, $pdate12){
	echo '������ � '.date('d.m.Y H:i:s', $pdate11). ' do '.date('d.m.Y H:i:s', $pdate12);
	
	$suppliers=array();
	
	$_cache=new CacheReportsItem;
	
	$_sg=new SuppliersGroup;
	
	$_ap=new AnRe;
	 
	
	$_orgs=new OrgsGroup;
	$orgs=$_orgs->GetItemsArr(0,1);
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	
	
	
	foreach($orgs as $org){
		$sg=$_sg->GetItemsWithOpfArr(false, $org['id']);
		
		
		foreach($sg as $k=>$supplier){
			$dec=new DBDecorator;
			
			
			//if($supplier['full_name']!='����') continue;
			
			
			 
			 $_ap->ShowData($pdate11, $pdate12, $supplier['id'], '', $dec,  '', true, false,'.',$alls,NULL,$total_pribyl);
			
			//� ������
			//echo $supplier['full_name'].' �������� '.($total_pribyl).'<br>'; //-$alls['total_marja']).'<br>';
			
			$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>$mode, 'supplier_id'=>$supplier['id']));	
				
				if($m===false){
					 $_cache->Add(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$total_pribyl, 'supplier_id'=>$supplier['id']));  
					 
					// print_r(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$value, 'supplier_id'=>$supplier['id']));
					 
				}else $_cache->Edit($m['id'], array('pdate'=>$marker, 'kind'=>$total_pribyl, 'value'=>$value, 'supplier_id'=>$supplier['id'])); 
			
			
			
			
		}
		
	}
	
}


//���� ������ �� ������� �� ���
if($mode==3){
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//���� ������ �� ������ �� ����
elseif($mode==4){
	$pdate11=$_pdate;
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//���� ������ �� ������� �� �������
elseif($mode==6){
	 
	$pdate11=$_pdate; $pdate12=$_pdate+24*60*60; 
	
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//���� ������ �� ������� �� �����
elseif($mode==5){
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogData($mode, $pdate11, $pdate12);
	 
}
//���� ������ �� ������� �� ������
elseif($mode==7){
	$pdate11= strtotime("last Monday");
			
	$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	LogData($mode, $pdate11, $pdate12);
	 
}

//����� �� �����������
elseif($mode==13){
	$pdate11=$_pdate;
	$pdate12=$_pdate+24*60*60;
	
	/*LogData($mode, $pdate11, $pdate12);*/
	
	//��������� � ���������� ������ �� �����������...
	//����� ����������� ���� ��� ��������
	
	
	
	$suppliers=array();
	
	$_cache=new CacheReportsItem;
	
	$_sg=new SuppliersGroup;
	
	$_as=new AnSupplier;
	 
	
	$_orgs=new OrgsGroup;
	$orgs=$_orgs->GetItemsArr(0,1);
	
	$marker=mktime(0, 0,0,date('m'),date('d'), date('Y'));
	
	foreach($orgs as $org){
		$sg=$_sg->GetItemsWithOpfArr(false, $org['id']);
		
		
		foreach($sg as $k=>$supplier)
		{
				$value=$_as->OstBySup($supplier['id'], $pdate12, $org['id']);
				
				//echo $supplier['full_name'].' = '.$value.'<br>';
				 
				$m=$_cache->GetItemByFields(array('pdate'=>$marker, 'kind'=>$mode, 'supplier_id'=>$supplier['id']));	
				
				if($m===false){
					 $_cache->Add(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$value, 'supplier_id'=>$supplier['id']));  
					 
					// print_r(array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$value, 'supplier_id'=>$supplier['id']));
					 
				}else $_cache->Edit($m['id'], array('pdate'=>$marker, 'kind'=>$mode, 'value'=>$value, 'supplier_id'=>$supplier['id'])); 
		
		}
		//print_r($sg);	
	}
	 
}

//+- �� ���, �������� �� �-��
elseif($mode==18){
	
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogDataPM($mode, $pdate11, $pdate12);
	
	
}

//+- �� ���, �������� �� 
elseif($mode==17){
	
	$pdate11=$_pdate; $pdate12=$_pdate+24*60*60; 
	
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;
	
	
	LogDataPM($mode, $pdate11, $pdate12);
	
	
}

//+- �� �����, �������� �� 
elseif($mode==16){
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	
	LogDataPM($mode, $pdate11, $pdate12);
	
	
}

//+- �� ������, �������� �� 
elseif($mode==15){
	
$pdate11= strtotime("last Monday");
			
	$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	
	LogDataPM($mode, $pdate11, $pdate12);
	
	
}

//+- �� �������, �������� �� 
elseif($mode==14){
	
	$pdate11=$_pdate;
	$pdate12=$_pdate+24*60*60;
	
	LogDataPM($mode, $pdate11, $pdate12);
	
	
}


//+- �� ���, �������� �� �-��
elseif($mode==23){
	
	$pdate11=mktime(0,0,0,1,1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	LogDataRe($mode, $pdate11, $pdate12);
	
	
}

//+- �� ���, �������� �� 
elseif($mode==22){
	
	$pdate11=$_pdate; $pdate12=$_pdate+24*60*60; 
	
	$quart=array();
	foreach($quarts as $quart){
		
		if(($_pdate>=$quart['pdate_beg_unf'])&&($_pdate<=$quart['pdate_end_unf'])) break;
	}
	
	
	$pdate11=$quart['pdate_beg_unf'];
	$pdate12=$_pdate+24*60*60;
	
	
	LogDataRe($mode, $pdate11, $pdate12);
	
	
}

//+- �� �����, �������� �� 
elseif($mode==21){
	$pdate11=mktime(0,0,0,date('m', $_pdate),1,date('Y', $_pdate));
	$pdate12=$_pdate+24*60*60;
	
	
	LogDataRe($mode, $pdate11, $pdate12);
	
	
}

//+- �� ������, �������� �� 
elseif($mode==20){
	
$pdate11= strtotime("last Monday");
			
	$pdate12=mktime(0,0,0,date('m'),date('d'),date('Y'));
	
	
	LogDataRe($mode, $pdate11, $pdate12);
	
	
}

//+- �� �������, �������� �� 
elseif($mode==19){
	
	$pdate11=$_pdate;
	$pdate12=$_pdate+24*60*60;
	
	LogDataRe($mode, $pdate11, $pdate12);
	
	
}
 
 
 
 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>