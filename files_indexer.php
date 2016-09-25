<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Документ без названия</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');



require_once('classes/supcontract_item.php');

require_once('classes/filecontents.php');
		

require_once('files_indexer_includes.php');


if(isset($_GET['from1'])) $from1=abs((int)$_GET['from1']);
else $from1=0;

if(isset($_GET['from2'])) $from2=abs((int)$_GET['from2']);
else $from2=0;

$step1=1; $step2=5;

$classes_list=array();
 
$classes_list[]=new ContractOrgItem;
$classes_list[]=new ContractItem;
$classes_list[]=new Supplier_Akt_Item;
$classes_list[]=new Supplier_Sh_Item;
$classes_list[]=new SupplierFileItem;
$classes_list[]=new SpItem;
$classes_list[]=new SpSItem; 
$classes_list[]=new FilePoItem;
$classes_list[]=new FileLetItem;
$classes_list[]=new FilePmItem;
$classes_list[]=new ContractUchItem;
$classes_list[]=new UserPasportItem;
$classes_list[]=new BillFileItem;
$classes_list[]=new AccFileItem;
$classes_list[]=new PayFileItem;
$classes_list[]=new PayInFileItem;
$classes_list[]=new TrustFileItem;
$classes_list[]=new InvFileItem;
$classes_list[]=new InvCalcFileItem;
$classes_list[]=new PosFileItem;
$classes_list[]=new KvFileItem;

//$classes_list[]=new KpFileItem;

$classes_list[]=new cashfileitem;
$classes_list[]=new cashinfileitem;
//$classes_list[]=new PrikazFileItem;


$classes_list[]=new Sched_HistoryFileItem;
$classes_list[]=new SchedFileItem;

if(count($classes_list)>$from1){
	$current_class=$classes_list[$from1];
	
	
		
	$sql='select * from '.$current_class->GetTableName().'';
	$sql_count='select count(*) from '.$current_class->GetTableName().'';
	
	$set=new mysqlset($sql, $step2, $from2, $sql_count);
	$total=$set->GetResultNumRowsUnf();
	$rc=$set->GetResultNumRows();
	$rs=$set->GetResult();
	
	//echo $rc.' '.$total;
	
	if($rc==0){
		$message='Кончились файлы таблицы '.$current_class->GetTableName();
		$path='files_indexer.php?from1='.($from1+$step1).'&from2=0';
	
	}else{
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_ct=new FileContents($f['orig_name'],  $current_class->GetStoragePath().$f['filename']);
			$contents='';
			
			try {
    			$contents=$_ct->GetContents();
				echo('Файл: '.$f['orig_name'].' Путь:'. $current_class->GetStoragePath().$f['filename'].' Содержимое: '. $contents.'<br>') ;
			
				$current_class->Edit($f['id'], array('text_contents'=>SecStr($contents)));
			} catch (Exception $e) {
				echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			}
			
			
			
		}
		
		$message='Продолжаю индексацию таблицы '.$current_class->GetTableName();
		$path='files_indexer.php?from1='.($from1).'&from2='.($from2+$step2);
	}
		
	
}else{
	$message='Кончились классы, конец очереди!!!';
		$path='/';
		
}


 
 

?>

<strong><?=$message?></strong>
<a href="<?=$path?>">продолжить работу</a>
            <script type="text/javascript">
			function Away(){
				location.href='<?=$path?>';
				
			
			}
			window.setTimeout(function(){ Away(); }, 3000);
			</script>

</body>
</html>