<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


require_once('../classes/help.class.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}



$ret='';
if(isset($_POST['action'])&&($_POST['action']=="help")){
	//����� ������� �� �����
	//$file=basename(($_POST['file']));
	$log=new ActionLog;
	
	$_file=$_POST['file'];
	
	$files=explode(';', $_file);
	
	foreach($files as $file){
	
		
		$file=basename(trim($file));
		$_hi=new HelpElemItem;
		$hi=$_hi->TestItemByFilename($file);
		
		$hi1=$_hi->getitembyfields(array('filename'=>$file));
		
		
		if(!($hi1===false)&&($hi===false)){
			/*header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	*/	
			continue;
		}
	
		
		
		//echo ABSPATH.'help/'.$file;
		if($f=fopen(ABSPATH.'help/'.$file,'r')){
			
			$file = fread($f, filesize(ABSPATH.'help/'.$file)); 
			
			
			$file=str_replace('src="', 'src="/help/', $file);
			
			
			//$file=eregi_replace("[.]*\<body lang=RU\>","",$file);
			
			//echo $file;
			//$ret=$file;
			
			/*$dom=new DOMDocument;
			$dom->encoding='windows-1251';
			$dom->loadHTML($file);
			
			$dom->recover=true;
			//$dom->normalizeDocument();
			 
			$nodes=$dom->getElementsByTagName('body');
			$node= $nodes->item(0);
			
			 
			$ret.='<h1>'.$hi['name'].'</h1>'; 
			$ret.=iconv("utf-8","windows-1251",$dom->saveXML( $node));
			*/
				$ret.='<h1>'.$hi['name'].'</h1>'; 
			$ret.=$file;
	 
			fclose($f);	
		}
		
		
		$title=SecStr(iconv("utf-8","windows-1251",$_POST['title']));
		$description=SecStr(iconv("utf-8","windows-1251",$_POST['description']));
		
		$description='������ ���������: '.$title.', ���������: '.$description;
		
		$log->PutEntry($result['id'],'������ �������', NULL, $hi['object_id'],NULL,$description,NULL);
			
	}
	
	
	
}
	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>