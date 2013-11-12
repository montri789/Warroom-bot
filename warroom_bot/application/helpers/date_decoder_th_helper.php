<?php
function thYear_decoder($str)
{
	if((int)$str < 100)
	{
		$year = 2500 + (int)$str - 543;
		return $year;
	}
	
	if((int)$str > 2000)
	{
		return (int)$str - 543;
	}
	
	return false;
}
function enYear_decoder($str)
{
	if((int)$str < 100)
	{
		$year = 2000 + (int)$str;
		return $year;
	}
	
	
	
	return false;
}
function thMonth_decoder($str,$type='cut')
{
	$strTHMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
	$strTHMonthFull = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
	if($type=='full') $strTHMonth = $strTHMonthFull;
	else $strTHMonth = $strTHMonthCut;
	foreach($strTHMonth as $k=>$v)
	{
		if(strcmp($str,$v) == 0) return $k;
	}
	
	return null;
}

function thMonth_decoder2($str,$type='cut')
{
	$strTHMonthCut = Array("","มค","กพ","มีค","เมย","พค","มิย","กค","สค","กย","ตค","พย","ธค");
	$strTHMonthFull = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
	if($type=='full') $strTHMonth = $strTHMonthFull;
	else $strTHMonth = $strTHMonthCut;
	foreach($strTHMonth as $k=>$v)
	{
		if(strcmp($str,$v) == 0) return $k;
	}
	
	return null;
}

function enMonth_decoder($str,$type='full')
{
	$strENMonthFull =
		Array("","January","February","March","April","May","June","July","August","September","October","November","December");
	$strENMonthCut =
		Array("","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	if($type=='cut') $strENMonth = $strENMonthCut;
	else $strENMonth = $strENMonthFull;
	foreach($strENMonth as $k=>$v)
	{
		if(strcmp($str,$v) == 0) return $k;
	}
	return null;
}

function onlyNum($str)
{
	$b="0123456789";

	$n=strlen($str);
	$x=strlen($b);
	$newstr="";
	for($i=0;$i<=$n;$i++)
	{
		for($j=0;$j<=$x;$j++)
		{
			if($str[$i]==$b[$j])
			{
				$newstr.=$b[$j];
			}
		}
	}
	return $newstr;
}
function dateEnText($date){
	if(trim($date) == "Today"){
		return date("Y-m-d");
	}else if(trim($date) == "Yesterday"){
		return date("Y-m-d",strtotime("-1 day",strtotime(date("Y-m-d"))));
	}else{
		return $date;
	}
}
function dateThText($date,$tn = NULL){

	if(is_numeric($date)){
		return date("Y-m-d",strtotime("-".$date." day",strtotime(date("Y-m-d"))));
	}else{
		if(trim($date) == "วันนี้"){
			return date("Y-m-d");
		}else if(trim($date) == "เมื่อวานนี้" || trim($date) == "เมื่อวาน"){
			return date("Y-m-d",strtotime("-1 day",strtotime(date("Y-m-d"))));
		}else if(trim($date) == "เมื่อวานซืน"){
			return date("Y-m-d",strtotime("-2 day",strtotime(date("Y-m-d"))));
		}else if($date == "ชั่วโมงที่แล้ว" || $date == "ชั่วโมงก่อน"  || $date == "ชั่วโมง"){
			return date("Y-m-d H:i:s",strtotime("-".$tn." hours",strtotime(date("Y-m-d H:i:s"))));
		}else if($date == "นาทีที่แล้ว" || $date == "นาทีก่อน" || $date == "นาที"){
			return date("Y-m-d H:i:s",strtotime("-".$tn." minute",strtotime(date("Y-m-d H:i:s"))));
		}else if($date == "ครึ่งชั่วโมงก่อน"){
			return date("Y-m-d H:i:s",strtotime("-30 minute",strtotime(date("Y-m-d H:i:s"))));
		}else if($date == "วินาที" || $date == "วินาทีก่อน" ||  $date == "วินาที"){
			return date("Y-m-d H:i:s",strtotime("-".$tn." seconds",strtotime(date("Y-m-d H:i:s"))));
		}else if($date == "วัน" || $date == "วันก่อน"){
			return date("Y-m-d",strtotime("-".$tn." day",strtotime(date("Y-m-d"))));
		}else if($date == "เดือน"){
			return date("Y-m-d",strtotime("-".$tn." month",strtotime(date("Y-m-d"))));
		}else if($date == "ปี"){ 
			return date("Y-m-d",strtotime("-".$tn." year",strtotime(date("Y-m-d"))));
		}else{
			$day = array("วันจันทร์"=>"Monday","วันอังคาร"=>"Tuesday","วันพุธ"=>"Wednesday","วันพฤหัส"=>"Thursday","วันศุกร์"=>"Friday","วันเสาร์"=>"Saturday","วันอาทิตย์"=>"Sunday ");
			
			$date =  date('Y-m-d',strtotime('last '.$day[$date.""]));
			
			return $date;
		}
	}
}

function convertTHDate($date){  // ตุลาคม 13, 2008, 12:27:10   // วันนี้ เวลา 02:38:42
	
	$date = str_replace(",","",$date);
	$post_date = explode(" ",$date);
				
	if(trim($post_date[0]) == "เมื่อวานนี้" || trim($post_date[0]) == "วันนี้"){
		$date = dateThText($post_date[0])." ".$post_date[4];
	}else{	
		$dd = $post_date[4];
		$mm = thMonth_decoder($post_date[3],"full");
		$yy = $post_date[5];
		$tt = $post_date[6];	
				
		$date = $yy."-".$mm."-".$dd." ".$tt;
	}
			
	return $date;
}

function validate($date){
		
	$pattern = '/(\d{4})-(\d{1,2})-(\d{1,2})[ (\d{2}):(\d{2})[:(\d{2})]*]*/';
	
	
	if(preg_match($pattern,$date)){
		return true;
	}
	else{
		return false;
	}

}



?>