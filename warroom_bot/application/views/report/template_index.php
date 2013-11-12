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
	
	.option_week{
		display:none;
	}
	</style>
	
	<script type="text/javascript" src="<?=base_url();?>/assets/js/jquery-1.7.2.min.js"></script>
</head>
<body>

<div id="container">
	<form class="bottom" method="post" accept-charset="utf-8">
		<h1><?=isset($report_title)?$report_title:null;?></h1>
		<h2>report title: 
			<select name="title" id="title" size="1">
				<option value="vol_comment_month">1. Trends of volumn of comments</option>
				<option value="vol_comment_brand">2. Trends of volumn of comments by brands</option>
				<option value="share_voice_brand">3. Share of voice messages compared to competitors</option>
				<option value="topics_compare_competitors">4. Topic of messages compared with competitors</option>
				<option value="mood_cc_in_overall">5. Mood compared to competitors in overall</option>
				<option value="mood_cc_in_each">6. Mood compared to competitors in each subtopics/factors</option>
				<option value="show_content_details">7. Hightlight contents Posts/comments</option>
				<option value="top_domain_product">8. Top domain names by each product</option>
				<option value="domain_cat_product">9. Source of conversations based on categories</option>
				<option value="top_influencers">10. Top influencers</option>
				<option value="devider">.....................</option>
				<option value="daily_post_count">11. Daily Post/Comment Count by websites</option>
				<option value="devider">.....................</option>
				<option value="acer4u_report">Acer#1: Acer4u Report</option>
				<option value="acer_report">Acer#2. Acer Facebook Activity</option>
				<option value="acer_report_op">Acer#3. Acer Online Support</option>
				<option value="acer_twitter_activity">Acer#4. Acer Twitter Activity</option>
				<option value="acer_pagelike_monthly">Acer#5.1. Acer Page Growth Facebook Monthly</option>
				<option value="acer_pagelike_weekly">Acer#5.1. Acer Page Growth Facebook Weekly</option>
				<option value="acer_follower_monthly">Acer#5.2. Acer Follower Growth Twitter Monthly</option>
				<option value="acer_follower_weekly">Acer#5.2. Acer Follower Growth Twitter Weekly</option>
				<option value="acer_pagelike_compare_monthly">Acer#5.3. Acer Compare Page Growth Facebook Monthly</option>
				<option value="acer_pagelike_compare_weekly">Acer#5.3. Acer Compare Page Growth Facebook Weekly</option>
				<option value="acer_follower_compare_monthly">Acer#5.4. Acer Compare Follower Growth Twitter Monthly</option>
				<option value="acer_follower_compare_weekly">Acer#5.4. Acer Compare Follower Growth Twitter Weekly</option>
				<option value="acer_facebook_top_post">Acer#6. Acer Facebook Top Post</option></select>
		</h2>
		<h2>client: 
			<select name="client" id="client" size="1">
				<option selected value="0">select</option>
				<option value="7">Samsung</option>
				<option value="9">Acer</option>
			</select>
		</h2>
		<h2 class="option_month">month : 
		<select name="month" id="month" size="1">
			<?php
			for($i=1;$i<=12;$i++)
			{ ?>
				<option value="<?=$i;?>" <?php echo $i==date('n')-1?'selected':null; ?>><?=date("F",mktime(0,0,0,$i));?></option>
			<?php }
			?>
		</select>
			year : 2012
		</h2>
		<h2 class="option_week">
			Week : <select name="week" id="week">
				<option value="week1">Week 1</option>
				<option value="week2">Week 2</option>
				<option value="week3">Week 3</option>
				<option value="week4">Week 4</option>
			</select>
		</h2>
		<h2>
		<input type="radio" name="view_type" value="html" checked> view online &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="view_type" value="excel"> export to excel
		</h2>
	
		<h2><input type="submit" name="submit" value="Generate &rarr;"></h2>
	</form>
	
	<div id="body">	  	  
<?=$content;?>	
	</div>

	<p class="footer">ThothConnect Analysis Report rendered by ThothMedia</p>
</div>
<script type="text/javascript">
$(function(){

	$('#title').change(function(){
		var value = $(this).val();
		
		if(value == 'acer_pagelike_monthly' || value == 'acer_follower_monthly' || value == 'acer_pagelike_compare_monthly' || value == 'acer_follower_compare_monthly'){
			$('.option_month').show();
			$('.option_week').hide();
		}else if(value == 'acer_pagelike_weekly' || value == 'acer_follower_weekly' || value == 'acer_pagelike_compare_weekly' || value == 'acer_follower_compare_weekly'){
			$('.option_month').show();
			$('.option_week').show();
		}	
	
	});

});
</script>
</body>
</html>