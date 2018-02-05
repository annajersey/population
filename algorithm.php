<?php
/*
Plugin Name:  Calculate population
Version: 1.0
Description: Calculate population Form
*/
function calculate(){ 

 //algorithm start
 global $group_num;
 global $result;
 $group_num = $_POST['group_num'] ? (int) $_POST['group_num'] : 3; 
 if(isset($_POST['groups'])){
	$year= $_POST['year'] ? $_POST['year'] : date('Y');
	$groups[0]=$_POST['groups'];
	$groups[0]['year']=$year;
	for($i=1; $i<$_POST['runs']; $i++){
		$newborn=0; $year++;
		$groups[$i]=$groups[$i-1];
		$groups[$i]['year']=$year;
		for($k=$group_num; $k>=1; $k--){
			//echo '<b>k: '.$k.'</b>  ';
			if($groups[$i][$k]['birth-ratio']) $newborn+=convertToDecimal($groups[$i][$k]['birth-ratio'])*$groups[$i][$k]['current']; 
			
			if($k>1){
				$groups[$i][$k]['current']=$groups[$i][$k]['current']+$groups[$i][$k-1]['current']*convertToDecimal($groups[$i][$k-1]['aging-ratio'])-$groups[$i][$k]['current']*convertToDecimal($groups[$i][$k]['aging-ratio']);
			}else{
				$groups[$i][$k]['current']=$groups[$i][$k]['current']+$newborn-$groups[$i][$k]['current']*convertToDecimal($groups[$i][$k]['aging-ratio']);
			}
		}
	}
	$result=array();
	$result[0][0]='Year';
	foreach($groups[0] as $key=>$group){if($key=='year') continue;
		$result[0][$key]=$group['label'];
	}
	$result[0][sizeof($groups[0])]='Total';
	foreach($groups as $i=>$pack){
		$total=0;
		$result[$i+1][0]=$pack['year'];
		foreach($pack as $key=>$group){ if($key=='year') continue;
			$total+=$group['current'];
			$result[$i+1][$key]=round($group['current'],2);
		}
		$result[$i+1][sizeof($pack)]=round($total,2);
	}
	
 }	//algorithm end

	
	if(isset($_REQUEST['format']) && $_REQUEST['format']==1){ //download as csv
			ini_set('max_execution_time', 0);
			set_time_limit(0);
			ini_set('memory_limit', '999M');
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=file.csv");
			$output = fopen("php://output", "w");
			fputcsv($output,array('Label of group','Initial number of population','Ratio of aging','Ratio of births'));
			foreach ($_POST['groups'] as $row)
			fputcsv($output, $row); 
			fputcsv($output, array());
			fputcsv($output, array('Result'));
			foreach ($result as $row)
			fputcsv($output, $row); 
			fclose($output);
			die();
	}elseif(isset($_REQUEST['format']) && $_REQUEST['format']==2){ //download as excel
		ini_set('max_execution_time', 0);
		set_time_limit(0);
		ini_set('memory_limit', '999M');
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		require_once "PHPExcel/PHPExcel.php"; 
		require_once "PHPExcel/PHPExcel/IOFactory.php";
		$name ="sheet name";
		$objPHPExcel = new PHPExcel();
		$objWriter  = array();
		$objWorkSheet = $objPHPExcel->createSheet();            
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle($name);
		$groups= $_POST['groups'];
		$groups[] = array('','','','');
		$groups[] = array('Result','','','');
		$result = array_merge($groups,$result);
		array_unshift($result,array('Label of group','Initial number of population','Ratio of aging','Ratio of births'));
		$objPHPExcel->getActiveSheet()->fromArray($result);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment; filename=filename.xls");
		header('Cache-Control: max-age=0');
		ob_get_clean();
		$objWriter->save('php://output');
		ob_end_flush();

		die();
	}	


}
function output(){
	global $group_num;
	global $result;
	calculate();
	wp_enqueue_style('pupulation_calculate', plugin_dir_url( __FILE__ ) . 'styles.css' );
	echo '<div class="wrapper"><div id="ajax-loader"></div><h2 class="entry-title pop-title">Calculate Population</h2><div id="pop-container">';
	include(sprintf("%s/template.php", dirname(__FILE__)));  //show form and result table
	echo '</div></div>';
}
function ajax_output(){
	global $group_num;
	global $result;
	calculate();
	wp_enqueue_style('pupulation_calculate', plugin_dir_url( __FILE__ ) . 'styles.css' );	
	include(sprintf("%s/template.php", dirname(__FILE__)));  //show form and result table
}
function convertToDecimal ($fraction)
    {
        if(strpos($fraction,"/")){ 
		$numbers=explode("/",$fraction);
        return $numbers[0]/$numbers[1];
		}else{
			return $fraction;
		}
    }
add_shortcode( 'pupulation_calculate', 'output' );	
add_action( 'wp_ajax_calculate', 'ajax_output' );
add_action( 'wp_ajax_nopriv_calculate', 'ajax_output' );
add_action('template_redirect', 'download_file');
function download_file() {
	if(isset($_REQUEST['format']) && isset($_POST['groups'])) calculate();
}