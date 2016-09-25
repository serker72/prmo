<?
require_once('abstractitem.php');
require_once('pergroup.php');

//����� ��� �������� �������� ��� (�� ����� ��������� �������)
class PeriodChecker{
	
	
	public function GetDate(){
		//����� - ����� ���������� �� ������� ��������
		
		return '28.02.2014';	
	}
	
	
	//�������� ����, �� ������ �������� � �������� ������
	public function CheckDateByPeriod($pdate, $org_id, &$rss, $periods=NULL){
		$rss='';
		$res=true;
		
		if($periods===NULL){
			$_pg=new PerGroup;
			$periods=$_pg->GetItemsByIdArr($org_id,0,1);	
		}
		
		foreach($periods as $k=>$v){
			if(($pdate>=$v['pdate_beg_unf'])&&($pdate<=$v['pdate_end_unf'])){
				$rss.=' �������� � �������� ������ � '.$v['pdate_beg'].' �� '.$v['pdate_end'];
				$res=$res&&false;
				break;	
			}
		}
		
		return $res;
	}
	
	
	//������� ���� �������� �������, � ����� ��� ������ �� ����
	public function GetCurrentPeriod(&$current_pdate_beg, &$current_pdate_end){
		
		$current_pdate_beg=0; $current_pdate_end=0;
		
		//��������!
		$year=date('Y');
		$month=array(
			array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year)),
			array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year)),
			
			array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year)),
			
			array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year)),
			
		
		);
		
		$pdate=time();
		foreach($month as $k=>$v){
			if(($pdate>=$v['pdate_beg_unf'])&&($pdate<=$v['pdate_end_unf'])){
				$current_pdate_beg= $v['pdate_beg_unf'];
				$current_pdate_end=$v['pdate_end_unf'];
				break;	
			}
		}
	}
	
	
}
?>