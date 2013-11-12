<?PHP
    
    $excel_report_name = "";
    if($report_name == "vol_comment_month"){
      $excel_report_name = "Trend_for_Volume_of_Comments";
	}else if($report_name == "daily_post_count"){
	  $excel_report_name = "Daily_post_count_thothconnect";
    }else if($report_name == "vol_comment_brand"){
      $excel_report_name = "Trend_for_Volume_of_Comments_compared_by_brands";
    }else if($report_name == "share_voice_brand"){
      $excel_report_name = "Share_of_Voice_messages_compared_to_competitors";
    }else if($report_name == "topics_compare_competitors"){
      $excel_report_name = "Topics_of_messages_compared_with_competitors";
    }else if($report_name == "mood_cc_in_overall"){
      $excel_report_name = "Mood_compared_to_competitors_in_overall";
    }else if($report_name == "mood_cc_in_each"){
      $excel_report_name = "Mood_compared_to_competitors_in_each_subtopics_factors";
    }else if($report_name == "top_domain_product"){
      $excel_report_name = "Top_domian_product";
    }else if($report_name == "top_influencers"){
      $excel_report_name = "Top_Influencers";
    }else if($report_name == "domain_cat_product"){
      $excel_report_name = "Source_of_Conversations_based_on_Thoth_current_categorized ";
    }else if($report_name == "show_content_details"){
		$excel_report_name = "Show_Content_Details_with_URL";	
    }else if($report_name == "acer_report"){
		$excel_report_name = "Acer_Report_Facebook_Activity";	
    }else if($report_name == "acer_report_op"){
		$excel_report_name = "Acer_Report_Online_Support";	
    }else{
		$excel_report_name = $report_name;
	}
    
    //$uri = $this->uri->segment(4);
    
    if($option['type'] == 'excel'){
      header("Content-Type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=".$excel_report_name.".xls");
      header("Pragma: no-cache");
      header("Expires: 0");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>ThothConnect Report</title>

	<style type="text/css">

	::selection{ background-color: #E13300; color: white; }
	::moz-selection{ background-color: #E13300; color: white; }
	::webkit-selection{ background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}
	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}
	h1 {
		color: #444;
		background-color: transparent;
/*		border-bottom: 1px solid #D0D0D0;
*/		font-size: 19px;
		font-weight: normal;
		padding: 0px 15px 5px 15px;
	}
	h2 {
		color: #444;
		background-color: transparent;
		font-size: 16px;
		font-weight: normal;
		padding: 0px 15px 0px 15px;
	}
	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}
	.bottom {
		border-bottom: 1px solid #D0D0D0;
		padding-bottom: 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	#container{
		margin: 0 auto;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	table.data td,th{
		border: 1px solid #D0D0D0;
	}
	table.data th{ background-color: #EEF; }
	table.data td.even { background-color: #EED; }
	</style>
</head>
<body>

<div id="container">
	<h1><?=isset($report_title)?$report_title:null;?></h1>
	<h2>client: <?=isset($client_name)?$client_name:null;?></h2>
	<h2 class="bottom">from: <?=isset($from_date)?$from_date:null;?> to: <?=isset($to_date)?$to_date:null;?></h2>
		
	<div id="body">	  	  
<?=$content;?>	
	</div>

	<p class="footer">ThothConnect Analysis Report rendered by ThothMedia</p>
</div>

</body>
</html>