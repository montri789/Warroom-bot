<?PHP foreach($factors as $row){ ?>
<?PHP
    
    $total = $row["mood"]["total"];
    $pos_vpos = $row["mood"]["pos_vpos"];
    $neu = $row["mood"]["neu"];
    $neg_vneg = $row["mood"]["neg_vneg"];
    $subtopic = $row["mood"]["subtopic"];
    
    //print_r($subtopic); 
?>
<table class="data" border="0" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th colspan="14"><?=$row["subject_name"]?></th>
    </tr>
    <tr>
        <th>Mood Scale</th>
        <?PHP foreach($subtopic as $val){ ?>
        <th><?=$val;?></th>
        <th><?=$val;?> %</th>
        <?PHP } ?>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td class="even">Positive / Very Positive</td>
            <?PHP foreach($pos_vpos as $val){ ?>
            <td class="even"><?=$val["mood"];?></td><td><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="odd">Neutral</td>
            <?PHP foreach($neu as $val){ ?>
            <td class="odd"><?=$val["mood"];?></td><td><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="even">Negative / Very Negative</td>
            <?PHP foreach($neg_vneg as $val){ ?>
            <td class="even"><?=$val["mood"];?></td><td><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <th class="">Total moods in each topic</th>
            <?PHP foreach($total as $val){ ?>
            <th class=""><?=$val;?></th>
            <th class=""><?PHP echo ($val == 0) ? "0" : "100" ;?>%</th>
            <?PHP } ?>
        </tr>
    </tbody>
</table>
<br/>
<?PHP } ?>