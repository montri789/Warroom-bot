<?php
    $topics = array('Price','Range & Availability','Quality','Customer Service','Community & Corporate','Brand & Trust','Promotion');
    $subjects = array('Mobile','Camera','TV','Smart TV','Notebook','Washing Machine','Refrigerator','Aircon','Monitor','Ultrabook','PremiumNotebook','bwprinter','compactcam','BDHT','Air Purifier','Color Laser Printer','NX1000');

    $index = 0;
    
    foreach($subjects as $s)
    {
		$list = array();	
		$talk = array();
		
		$check_array = array();
		
		foreach($data as $d)
		{
		    if(strstr($d['subject'],$s) == false) continue;
		    	    
		    $subject = trim($d['subject']);
		    $e = explode('-',$subject);
		    $d['topic'] = trim($e[1]);
		    $str = explode(' ',$subject);
		    $d['brand'] = trim($str[0]);
		    
		    $key = trim($e[1])."".trim($str[0]);
		    
		    if(!in_array($key,$check_array)){
			$check_array[] = $key;
			
			$list[]= $d;
		    }
		}
		
		?>
		<table class="data" border="0" cellspacing="0" cellpadding="5">
		    <tr>
			<th><?=$s?></th>
		<?php
			$brands = array();
			$total  = array();
			
			foreach($list as $l)
			{ 
			    if(in_array($l['brand'],$brands)) continue;
			    $brands []= $l['brand'];
			    $total[$l['brand']] = 0;
			    ?>
			    <th><?=$l['brand'];?></th>
				<th><?=$l['brand'];?> %</th>
			<?php 
			}
		?>
			</tr>
		<?php
			foreach($topics as $k=>$t)
			{
				// calculate total
				foreach($brands as $b)
				{
					$count = 0;
					foreach($list as $l)
					{
						if($l['brand'] != $b || $l['topic'] != $t) continue;
						$count = $l['count'];
					}
					$total[$b] += $count;
				}
			}
			
			foreach($topics as $k=>$t)
			{ 
			    $td_class = $k%2?"even":"odd";
			    ?>
			    <tr>
				<td class="<?=$td_class;?>"><?=$t?></td>
		    <?php
			    foreach($brands as $b)
			    {
				    $count = 0;
				    foreach($list as $l)
				    { 
					if($l['brand'] != $b || $l['topic'] != $t) continue;
					$count = $l['count'];			
				    } ?>
				    <td class="<?=$td_class;?>"><?=$count;?></td>
					<td class="<?=$td_class;?>"><?php if($count>0) printf("%3.2f",$count*100/$total[$b]); else echo '0'; ?>%</td>
				    <?php
			    }	
			?>
				</tr>
			<?php }
		?>
			<tr>
			    <th>Total</th>
		<?php
			foreach($brands as $b)
			{ ?>
			    <th><?=$total[$b];?></th>
				<th>100 %</th>
			<?php }
		?>
			</tr>
		</table>
		<p></p>
	<?php
	
	    $index++;
	    
	    unset($total);
	    unset($list);
	    unset($brands);
	}
?>