<?
require_once('global.php');
require_once('resizing.php');

class ResizeApplyer{

	
	public function MakePhoto($file,$action,$folder,$addtoname=''){
		
		
		
		if(@isset($action['doit'])||(@$action['doit'])){
			$r=new ImgResizing($file['tmp_name']);
			//echo 'qq'; ���-�� ������
			$pre=$action['pre']; 
			
			if($action['do_resize']){
				//��������
				//������� ������_�����
				//if($pre=='') $pre=time();

				

				//$newname=$folder.'/'.$pre.$r->MakeNewName(time(), $file['name']);

				$newname=$folder.'/'.$pre.$r->MakeNewName($addtoname, $file['name']);
				//echo $newname;
				if($action['resize_kind']==0){
					//������ �� �������
					if($action['resize_params']['cutit']) $r->ResizeCut($newname,$pre, $action['resize_params']['w'], $action['resize_params']['h']);
					else $r->ResizeByMaxSize($newname,$pre, $action['resize_params']['w'], $action['resize_params']['h']);
				}else if($action['resize_kind']==1){
					//������ �� %
					//echo '�������: '.$action['resize_params']['percent'];
					$r->ResizePercent($newname,$pre, $action['resize_params']['percent']);
				}
				
			}else{
				//�� ��������! ������ ��������
				$ims=GetImageSize($file['tmp_name']);
				if($ims){
					
					
					switch($ims[2]){
						case 1:
							$extension='.gif';
						break;
						case 2:
							$extension='.jpg';
						break;
						case 3:
							$extension='.png';
						break;
					}
						
					
					
					
				//	$pref = time();
					$newname=SecureCyr(eregi_replace('([[:alnum:]])(\\.[[:alnum:].]*)?$','\\1'.'-'.$addtoname.$extension,$file['name']));
					
					//echo '�������!';
					
					copy($file['tmp_name'],$folder.'/'.$pre.$newname);
				}
			}
			unset($r);
		}
		
		
		
	}
	
}
?>