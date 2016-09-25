<?
//�����-������������� ����� �������� ��� �������� ����� ��������
class Configurat{

	protected $settings=array();

	
	//��������� ���� ����
	function __construct(array $settings){
		$this->settings=$settings;
	}
	
	
	//���������� ������� ��� �����
	public function BuildFormArr(){
		$arr=array();
		
		//$itm[]=array('caption' 'fieldtype' 'value'
		
		foreach($this->settings as $k=>$v){
			if(eregi("_CAPTION$",$k)) continue;	
			//echo " $k ";
			$inputtype='';
			switch($v['type']){
				case 'bool':
					$inputtype='checkbox';
				break;
				case 'string':
					$inputtype='text';
				break;
				case 'text':
					$inputtype='textarea';
				break;
				default:
					$inputtype='text';
				break;
			};
			
			
			$_caption=$this->FindCaption($k);
			if($_caption!==NULL)
				$caption=$this->settings[$_caption]['value'];
			else
				$caption='';
						
			$arr[]=array('caption'=>$caption, 'fieldtype'=>$inputtype, 'value'=>$v['value'], name=>$k);
		}
		
		return $arr;
	}
	
	//����� ����� �� �������
	public function BuildForm($template){
		$sm=new SmartyAdm;
		$sm->assign('items', $this->BuildFormArr());
		return $sm->fetch($template);
	}
	
	
	//��������� �-�� ������ �������� ������� � ������� ����
	protected function FindCaption($name){
		$res=NULL;
		foreach($this->settings as $k=>$v){
			if(!eregi("_CAPTION$",$k)) continue;
			if($name."_CAPTION"==$k){
				$res=$k;
				break;	
			}
		}
		
		return $res;
	}
	
	
};
?>