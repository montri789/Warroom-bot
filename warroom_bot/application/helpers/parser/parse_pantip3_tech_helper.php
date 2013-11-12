<?php
	function parse_pantip3_tech($fetch,$page,$debug=false,$domain)
	{
		$log_unit_name = 'parse_pantip3_cafe';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$html = str_get_html($fetch);
		
		
		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$elements = $html->find('table[border=1]');
		if($elements == null){
			$result['parse_ok'] = false;
			echo 'elements error.';
			return $result;
		}
		
		
		$count=count($elements);
		
		if($debug) echo ' parse_pantip_cafe : found elements : '.$count.'<br />';
		else
		{
			echo '(ppc=';
			echo (int)$count-(int)$parsed_posts_count;
			echo ')';
		}

	
		log_message('info',' parse_pantip_tech : found elements : '.$count);


		$i=0; $j=0;
		foreach($elements as $e)
		{  
			
			if($debug) $parsed_posts_count = 0;
			if($i < $parsed_posts_count)
			{
				$i++;
				continue;
			}

			

			if($e->find('caption') != null) continue;
			$is_script = $e->first_child()->first_child()->children(1);
			if($is_script != null && $is_script->tag == 'script') continue;
			if($e->parent()->tag == 'div') continue;
			
			//echo trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->outertext));
			
			$data = array();
			
			//$post = new Post_model2();
			//$post->init();
			//$post->page_id = $page->id;
			 $data["page_id"] = $page->id;
     
	 

			if($i==0) 
			{
				$data["type"] = "post";
				$e = $e->first_child()->first_child();
				$data["title"] = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->children(1)->plaintext));
				$data["body"] = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->children(2)->first_child()->plaintext));

				$meta_list = $html->find('font[color=#ffff00]');
				$meta = $meta_list[count($meta_list)-1];
				$txt = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->next_sibling()->plaintext));
				$needle2 = " -[ ";
				$matches = explode($needle2,$txt);
				$author_name = $matches[0];
				$date = explode(" ",trim($matches[1]));
				$tt = $date[4];      
			}
			else
			{  
				$data["type"] = "comment";
				//$e = $e->first_child()->first_child()->first_child();
				//while($e->children($j)->tag != "font") $j++;
				$title = $e->find('font',0);
				$data["title"] = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$title->plaintext));

				//$j++;
				//while($e->children($j)->tag != "font") $j++;
				$body = $e->find('font',1);
				$data["body"] = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$body->plaintext));

				$e_list = $e->find('font[color]');
				for($i=count($e_list)-1;$i>=0;$i--)
				{
					if($e_list[$i]->getAttribute('color') == "#F0F8FF")
					{
						$meta = $e_list[$i];
						break;
					}
				}
//				$meta = $e->find('font[color]',count($e->find('font[color]'))-1);
			
				$str = @iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->plaintext);
				$needle1 = "จากคุณ : ";
				$needle2 = " - [ ";
				$txt = substr($str,strlen($needle1));
				$matches = explode($needle2,$txt);
				$author_name = $matches[0];
				
				   
				// if วันสิ้นปี, วันปีใหม่, replace back to normal
				$special_date = array('วันสิ้นปี' => '31 ธ.ค.','วันปีใหม่' => '1 ม.ค.', 'วันนักประดิษฐ์' => '2 ก.พ.', 'วันวาเลนไทน์' => '14 ก.พ.','วันเข้าพรรษา'=>'27 ก.ค.','วันแม่แห่งชาติ'=>'12 ส.ค.','วันเกิด PANTIP.COM'=>'7 ต.ค.','วันปิยมหาราช'=>'23 ต.ค.','วันลอยกระทง'=>'10 พ.ย.','วันพ่อแห่งชาติ'=>'5 ธ.ค.','วันจักรี'=>'6 เม.ย.','วันฉัตรมงคล'=>'5 พ.ค.','วันมาฆบูชา'=>'7 มี.ค.','วันสตรีสากล'=>'8 มี.ค.','วันการสื่อสารแห่งชาติ'=>'4 ส.ค.');
				mb_internal_encoding("UTF-8");
				
				$thYear = date("Y")+543-2500;
				foreach($special_date as $k=>$v)
				{
					$post_date = preg_replace('/'.$k.'/',$v.' '.$thYear,trim($matches[1]));
				}
			
				$date = explode(" ",trim($post_date));
				$post_date = $date[0]."-".thMonth_decoder($date[1])."-".thYear_decoder($date[2]);
				$tt = $date[3];
			}



			//$post->sentiment = $this->sentiment->check_sentiment($post->body);
			$yy = thYear_decoder($date[2]);
			$mm = thMonth_decoder($date[1]);
			$dd = $date[0];
			
			$post_date = $yy."-".$mm."-".$dd." ".$tt;

			$data["author"] = $author_name;

			$data["post_date"] = $post_date;
			$data["parse_date"] = mdate('%Y-%m-%d %H:%i',time());
			$data["website_id"] = $domain["id"];
			$data["website_name"] = $domain["name"];
			$data["url"] = substr($domain["root_url"],0,-1)."".$page->url;

			if($debug)
			{
				echo "<hr>";
			}
			else
			{
				echo '.';
				if(validate($post_date))
					{
						$result['posts'] []= $data;
						$result['parse_ok'] = true;
					}
					else{
						$result['parse_ok'] = false;
						echo "validate failed . . .";
					}
				unset($data);
			}
			unset($data);
			$i++;
			$j=0;
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>