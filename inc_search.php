<?php

include_once "server.inc.php";



?>

<form name='rpl_search' method='post' action='<?php echo $_SERVER['PHP_SELF']."?search"; ?>'>
<table>
<tr><td><select name='index'>
<option value='all'>Alle Felder durchsuchen</option>
<?php
$sql = "SELECT * FROM ut_dcschema";
$erg = $db->query($sql);

while($row = $erg->fetch(PDO::FETCH_ASSOC)){
	echo "<option value='".$row['dc_schema']."'";
	if(isset($_POST['all']) and  $_POST['all']==$row['dc_schema']) { echo "selected"; } echo ">dc:".$row['dc_schema']."</option>";
}

?>
</select></td>
<td><input type='text' name='begriff'  style='width:350px;' value="<?php if(isset($_POST['begriff'])){ echo $_POST['begriff']; } ?>" /></td>
</tr>
<tr><td></td><td><input type="submit" name='submitSearch' value='Suchen!' /></td></tr>


</table>
</form>


<?php 
if(isset($_POST['submitSearch'])){
	

	if($_POST['index']=='all'){
		
		$sql = "SELECT DISTINCT * FROM t_metadata WHERE element_id = '".$_POST['begriff']."' OR record_id = '".$_POST['begriff']."' OR value LIKE '%".$_POST['begriff']."%' OR attribute_value = '".$_POST['begriff']."' GROUP BY record_id;";
		
		
	}
	else {
		$sql = "SELECT DISTINCT * FROM t_metadata WHERE dc_element = '".$_POST['index']."' AND (value LIKE '%".$_POST['begriff']."%' OR attribute_value = '".$_POST['begriff']."') GROUP BY record_id;";
	}
	#echo $sql;
	$erg = $db->query($sql);
	
echo "<table>";	
$record_id = 0;
$tr_class = 0;
$tr_counter = 0;
while($row = $erg->fetch(PDO::FETCH_ASSOC)){

	$sql_det = "SELECT  * FROM `t_metadata` JOIN ut_dcschema ON ut_dcschema.dc_schema = t_metadata.dc_element JOIN t_record ON t_record.record_id = t_metadata.record_id JOIN t_cataloguers_history ON t_record.record_id = t_cataloguers_history.record_id WHERE t_metadata.record_id = ".$row['record_id']." ORDER BY t_metadata.record_id, sort_id ASC";
	$erg_det = $db->query($sql_det);
	while($row_det = $erg_det->fetch(PDO::FETCH_ASSOC)){

	if($record_id<>$row_det['record_id'])
	{
		$tr_counter = 0;
		$record_id = $row_det['record_id']; 
		if($tr_class==0){$tr_class=1;} else { $tr_class=0; }
		$sql = "SELECT * FROM t_files WHERE record_id = ".$record_id.";";
		$erg_files = $db->query($sql);
		echo "<tr class=' mod".$tr_class."'>";
		echo "<td></td><td><b>Datensatz #".$record_id."</b>";
		while($row_files=$erg_files->fetch(PDO::FETCH_ASSOC)){			
				$datei =str_replace("rpl_fachrepository/data/","",$row_files['file']);
				echo "<br/><a href='../".$row_files['file']."'>".$datei."</a>";
		}
		echo "</td><td></td>
		<td><a href=\"?cat=11&edit_id=".$row_det['record_id']."\"><img src='img/edit.png' alt='Bearbeiten' title='Bearbeiten' /></a></td>
		</tr>";
	}
	else {
		$tr_counter++;
	}
	

	echo "<tr class=' mod".$tr_class."'>";
	echo "<td>dc:".$row_det['dc_element']."</td>";
	echo "<td>".($row_det['value']);
		if(!empty($row_det['attribute_value'])){ echo " <a href='http://d-nb.info/gnd/".$row_det['attribute_value']."' target='_blank'>".$row_det['attribute_value']."</a>"; }
	echo "</td>";
	echo "<td>".$row_det['record_id']."</td>";
	echo "<td>"; if($tr_counter==0){ echo ""; } echo "</td>";
	echo "</tr>";
	}
}
echo "</table>";	
}


?>