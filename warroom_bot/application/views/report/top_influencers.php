<table class="data" border="0" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th>Influencers</th>
        <?PHP foreach($subject as $val){ ?>
        <th><?=$val;?></th><th><?=$val;?>%</th>
        <?PHP } ?>
    </tr>
    </thead>
    <tbody>
        <?PHP $i=1; foreach($tops as $row){ $td_class = $i%2?"even":"odd"; ?>
        <tr>
            <td class="<?=$td_class?>"><?=$row["username"]; ?></td>
            <?PHP $j = 0; foreach($row["col"] as $col){ ?>
            <td class="<?=$td_class?>">
            <?PHP echo (empty($col)) ? 0 : $col ;?>
            </td>
            <td class="<?=$td_class?>">
            <?PHP echo (empty($total_msg[$j]) || $total_msg[$j] == 0) ? 0 :  sprintf("%10.2f",(($col*100)/$total_msg[$j]));?>% 
            </td>
            <?PHP $j++;  } ?>
        </tr>
        <?PHP $i++; } ?>
        <tr>
            <th>Total messages</th>
            <?PHP foreach($total_msg as $val){ ?>
            <th><?=$val;?></th><th>100 %</th>
            <?PHP } ?>
        </tr>
    </tbody>
</table>