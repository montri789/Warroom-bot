<?PHP foreach($overall as $row){ ?>
<table class="data" border="0" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th>Mood Scale</th>
        <?PHP foreach($row["subject"] as $val){ ?>
        <th><?=$val;?></th><th><?=$val;?>%</th>
        <?PHP } ?>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td class="even">Very Positive</td>
            <?PHP foreach($row["vpos"] as $val){ ?>
            <td class="even"><?=$val["mood"];?></td><td class="even"><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="odd">Positive</td>
            <?PHP foreach($row["pos"] as $val){ ?>
            <td class="odd"><?=$val["mood"];?></td><td class="odd"><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="even">Neutral</td>
            <?PHP foreach($row["neu"] as $val){ ?>
            <td class="even"><?=$val["mood"];?></td><td class="even"><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="odd">Negative</td>
            <?PHP foreach($row["neg"] as $val){ ?>
            <td class="odd"><?=$val["mood"];?></td><td class="odd"><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
        <tr>
            <td class="even">Very Negative</td>
            <?PHP foreach($row["vneg"] as $val){ ?>
            <td class="even"><?=$val["mood"];?></td><td class="even"><?PHP printf("%10.2f",$val["per"]); ?>%</td>
            <?PHP } ?>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th>Total moods</th>
            <?PHP foreach($row["total"] as $val){ ?>
            <th><?=$val;?></th>
            <th><?PHP echo ($val == 0) ? "0" : "100" ;?>%</th>
            <?PHP } ?>
        </tr>
    </tfoot>
</table>
<br/>
<?PHP } ?>