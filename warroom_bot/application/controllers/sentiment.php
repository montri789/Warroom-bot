<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sentiment extends CI_Controller {
	
	function check_sentiment($str)
	{
		$raw = rand(0,100);
		if($raw < 10) return 1;
		else if($raw < 25) return 2;
		else if($raw < 70) return 3;
		else if($raw < 80) return 4;
		else return 5;
	}
	
	function run()
	{
		// $option = array('sentiment' => 0);
		// $query = $this->db->get_where('post',$option);
		$query = $htis->db->get('post');
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$post = new Post_model();
				$post->init($row->id);
				// $post->sentiment = $this->check_sentiment($post->body);		// Random
				
				log_message('info','Sentiment : post('.$post->id.') : '.$post->sentiment);
				$post->update();
			}
		}
	}
	
	function eval_sentiment($str,$subject_id,$debug=0)
	{
		mb_internal_encoding('utf-8');
		$thres_len = 20;
		
		// get subject keywords
		$query = $this->db->get_where('subject',array('id'=>$subject_id));
		$subject_inc = $query->row()->inclusive;
		$subject_exc = $query->row()->exclusive;
		$subject_inc = explode(",",$subject_inc);
		$subject_exc = explode(",",$subject_exc);
		
		
		$total_sentiment_phrase = 0;
		$total_net_score = 0;
		
		$total_len = mb_strlen($str,'utf-8'); // Str length
		$str = preg_split('#[\s\n\r]+#', $str); // split by space
		//print_r($str);
		
		foreach($str as $s)
		{
			// if this phrase contain subject words, increase amp_score start weight to 2 otherwise is 0.5
			$amp_score = 0.5;
			
			foreach($subject_inc as $subj)
			{
				$subj_pos = mb_stripos(trim($s),trim($subj));
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
			
			foreach($this->words->emo as $w)
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
				foreach($this->words->neg as $n)
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
				foreach($this->words->amp as $a)
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
				foreach($this->words->ign as $i)
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
		$net_weight = $total_sentiment_phrase/$total_len;
		$sentiment = $total_net_score*$net_weight;

		if($debug)
		{
			echo "<hr />";
			echo "Total Sentiment Phrase:".$total_sentiment_phrase."<br />";
			echo "Total Len:".$total_len."<br />";
			echo "Weight:".$net_weight."<br />";
		}
		
		return $sentiment;
	}

	function test($subject_id,$from=0,$to=null)
	{
		$query = $this->db->get_where('matchs',array("subject_id"=>$subject_id));
		
		echo "<div style='width:960px;'><table border=1><thead><tr><td>Post ID</td><td>Sentiment</td><td>Body</td></thead><tbody>";
		foreach($query->result() as $row)
		{
			$post = new Post_model();
			$post->init($row->post_id);
			
			echo "<tr><td>".$post->id."</td><td>".round($this->eval_sentiment($post->body),2)."</td><td>".$post->body."</td></tr>";
			unset($post);
		}
		echo "</table></div>";
	}
	
	function test_post($post_id = null,$subject_id=null,$debug=false)
	{
		if($post_id == null)
		{
			echo "invalid post";
			exit;
		}
		
		$post = new Post_model();
		$post->init($post_id);
		
		echo "Post:".$post_id.":".$post->title."<br />";
		echo "Body:".$post->body."<br />";
		echo "sentiment:".$this->eval_sentiment($post->body,$subject_id,$debug);
	}
	
	function test_words()
	{
		var_dump($this->words->emo);
	}
}