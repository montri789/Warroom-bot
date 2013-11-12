<?php
function get_hilight($subject_id = null, $string = null)
{
	if($subject_id == null) return false;
	if($string == null) return false;

	// get query_string from $subject_id
	$CI =& get_instance();
	$query_string = $CI->custom_model->get_value('subject','query',$subject_id);
	
	// get keywords from query_string
	$keywords = get_keywords($query_string);
	
	// do pattern and replacement
	$pattern = array();
	$replacement = array();
	foreach($keywords as $word)
	{
		$pattern[] = '/'.trim($word).'/';
		$replacement[] = '<b>'.trim($word).'</b>';
	}
	
	// return
	return preg_replace($pattern,$replacement,$string);
}

function get_keywords($search_string = null)
{
	$keywords = array();
	if($search_string == null) return $keywords;
	
	// fix "word\-word" to "word#word"
	$search_string = str_replace('\\-','#',$search_string);
	
	// strip exclude words
	$pattern = "/\-\"*[\s\S]+\"*/";
	$search_string = preg_replace($pattern,'',$search_string);

	// back "word#word" to "word-word"
	$search_string = str_replace('#','-',$search_string);

	// extract keywords
	$pattern = "/[\|\(\)\&\"]+/";
	$raw_keywords = preg_split($pattern,$search_string,null,PREG_SPLIT_NO_EMPTY);
	
	foreach($raw_keywords as $k=>$r)
	{
		if(strlen(trim($r)) === 0) continue;
		else $keywords[] = $r;
	}
	
//	echo 'striped:'.$search_string;
//	echo "<br />";
//	print_r($keywords);
	
	return($keywords);
}

function get_mood($str=null,$keywords)
{
	$CI =& get_instance();
	$CI->load->library(array('words'));

	$debug = false;
	mb_internal_encoding('utf-8');
	$thres_len = 20;
	
	$total_sentiment_phrase = 0;
	$total_net_score = 0;
	
	$total_len = mb_strlen($str,'utf-8'); // Str length
	$str = preg_split('#[\s\n\r]+#', $str); // split by space
	//print_r($str);
	
	foreach($str as $s)
	{
		// if this phrase contains subject words, increase amp_score start weight to 2 otherwise is 0.5
		$amp_score = 0.5;
		
		foreach($keywords as $k)
		{
			$subj_pos = mb_stripos(trim($s),trim($k));
			if(is_int($subj_pos))
			{
				$amp_score++;
				if($debug)
				{
					echo "found at ".$subj_pos." subject:".$subj;
				}
				break;
			}
		}
		
		
		foreach($CI->words->emo as $w)
		{
			$s_len = mb_strlen($s);
			
			$score = 0;
			$amp_len = 0;
			$neg_len = 0;
			
			// search for emo words
			$emo_pos = mb_stripos($s,$w->word);
			if(is_int($emo_pos))
			{
				$total_sentiment_phrase += $s_len;
				$score = $w->value;
				$emo_len = mb_strlen($w->word);
				
				if($debug)
				{
					echo "<hr/>context(".$s_len."):".$s."<br/>";
					echo "found[".$emo_pos."]:".$w->word."(".$score.")";
					echo "<br />";
				}
			}
			else continue; // if not found emo word skip whole chunk

			// look backward for neg
			foreach($CI->words->neg as $n)
			{
				$neg_pos = mb_strrpos($s,$n->word);
				if(is_int($neg_pos))
				{
					$gap_pos = $emo_pos-$neg_pos;
					if($gap_pos > 0 && $gap_pos < $thres_len)
					{
						$score *= $n->value;
						$neg_len = mb_strlen($n->word);
						
						if($debug)
						{
							echo "found[".$gap_pos."]:".$n->word."(".$score.")";
							echo "<br />";
						}
					}
				}
			}

			// look backward for amp

			// look forward for amp
			foreach($CI->words->amp as $a)
			{
				$amp_pos = mb_stripos($s,$a->word,$emo_pos);
				if(is_int($amp_pos))
				{
					$amp_score += $a->value;
					$amp_len += mb_strlen($a->word);
					
					if($debug)
					{
						echo "found[".$amp_pos."]:".$a->word."(".$amp_score.")";
						echo "<br />";
					}
				}
			}
			if($amp_score != 0) $score *= $amp_score;


			// look forward for ign
			foreach($CI->words->ign as $i)
			{
				$ign_pos = mb_stripos($s,$i->word,$emo_pos);
				if(is_int($ign_pos))
				{
					$score = 0;

					if($debug)
					{
						echo "found[".$ign_pos."]:".$i->word;
						echo "<br />";
					}
					
					continue;
					
				}
			}
			
			if($debug)
			{
				// total emo value
				echo "Score:".$score;
				echo "<br />";
			}
			
			// check weight
			$sentiment_len = $emo_len + $neg_len + $amp_len;
			$weight = $sentiment_len/$s_len;
			if($debug)
			{
				echo "Weight:".$weight;
				echo "<br />";
			}
			
			// cal net emo value
			$net_score = $score*$weight*100;
			$total_net_score += $net_score;
			if($debug)
			{
				echo "Net:".$net_score;
				echo "<br />";
			}
			
			$score = 0;
		}
	}
	
	// sum all 
	if($total_len != 0) $net_weight = $total_sentiment_phrase/$total_len;
	else $net_weight = 0;
	$sentiment = $total_net_score*$net_weight;
	
	// only from -100 to 100
	if($sentiment < -100) $sentiment = -100;
	if($sentiment > 100) $sentiment = 100;
	
	if($debug)
	{
		echo "<hr />";
		echo "Total Sentiment Phrase:".$total_sentiment_phrase."<br />";
		echo "Total Len:".$total_len."<br />";
		echo "Weight:".$net_weight."<br />";
	}
	
	unset($str);
	return $sentiment;
}

?>