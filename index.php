<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style.css" rel="stylesheet"/>
<title>MetadatenBank - Raumplanungs-Fachrepositorium</title>
<script src="jquery/jquery-1.12.3.min.js"></script>
<script src="jquery/fileman/js/custom.js"></script>
<script src='jquery/jquery_ui/ui/jquery-ui.js'></script>
</head>

<body>
<h1>MetadatenBank - Raumplanungs-Fachrepositorium</h1>
<ul id='navigation'>
	<li style='width:160px;'><a href='?'>Neuer Datensatz</a></li>
    <li style='width:160px;'><a href='?search'>Suchen</a></li>
</ul>
<p style='clear:both;'></p>
<?php

if(isset($_GET['search'])){
	include_once "inc_search.php";
	
}


include_once "server.inc.php";
include_once "inc_marcXML.php";
include_once "inc_phpfunctions.php"; 

if(isset($_POST['submitForm'])){

if(!isset($_POST['edit_record_id'])){
	$sql = "INSERT INTO t_record (creation_date) VALUES ('".date('Y-m-d H:i',time())."');";
	#echo $sql;
	$db->query($sql);
	$record_id = $db->lastInsertId();
	
	$sql = "INSERT INTO t_cataloguers_history  (`record_id`, `paraphe`, `datum`) VALUES (".$record_id.", '".$_POST['paraphe']."', '".date('Y-m-d H:i',time())."');";
	$db->query($sql);
	
}
else {
	$record_id = $_POST['edit_record_id'];
			if(isset($_POST['edit_record_id'])){
				
				$sql = "DELETE FROM t_metadata WHERE record_id = ".$record_id.";";
				$db->query($sql);
				$sql = "DELETE FROM t_files WHERE record_id = ".$record_id.";";
				$db->query($sql);
				
			}
}
#echo $record_id;

	$sql_col ="";
	$sql_val ="";
	$stopp = count ($_POST['dc_elements']);
	for($i=0;$i<(count($_POST['dc_elements']));$i++){
		if(!empty($_POST['value'][$i]) or !empty($_POST['attribute_value'][$i])){
		
			#echo  $_POST['dc_elements'][$i].": ".$_POST['value'][$i]." (".$_POST['attribute_type'][$i].": ".$_POST['attribute_value'][$i].")</br>";
			
	
			#Metadaten-Insert
			if(!empty($_POST['attribute_value'][$i])){
				$daten = array('dc_element' => $_POST['dc_elements'][$i], 'value' => trim($_POST['value'][$i]),'attribute_type' => $_POST['attribute_type'][$i],'attribute_value' => trim($_POST['attribute_value'][$i]) );
				$sql = "INSERT INTO t_metadata (record_id, dc_element, value, attribute_type, attribute_value) VALUES (".$record_id.", :dc_element, :value, :attribute_type, :attribute_value);";
			} else {
					$daten = array('dc_element' => $_POST['dc_elements'][$i], 'value' => trim($_POST['value'][$i]) );
				$sql = "INSERT INTO t_metadata (record_id, dc_element, value) VALUES (".$record_id.", :dc_element, :value);"; 
			}
			
			$erg = $db->prepare($sql);
			$erg->execute($daten);
			

		}
		
	}
	
	#file-Data-insert
	for($i=0;$i<count($_POST['file_name']);$i++){
		if(!empty($_POST['file_name'][$i]) and $_POST['file_name'][$i]!='Hier klicken, um Originaldatei hochzuladen und auszuwählen!'){
			#echo $_POST['file_name'][$i];
			$sql = "INSERT INTO t_files (file, record_id, upload_time) VALUES ('".$_POST['file_name'][$i]."',".$record_id.",'".date('Y-m-d H:i',time())."');";
			$db->query($sql);
			
		}
	}
	

	
}

?>
<script>
function openCustomRoxy2(){
  $('#roxyCustomPanel2').dialog({modal:true, width:875,height:600});
}
function closeCustomRoxy2(){
  $('#roxyCustomPanel2').dialog('close');
}
</script>
<form name='erfassungs_formular' method="post" action="?cat=11<?php if(isset($_GET['edit_id'])) { echo "&edit_id=".$_GET['edit_id']; }?> ">
<table>
    <tr style='font-weight:bold;'>
    <td>DC-Element</td><td>Value</td><td>Attribute_Type</td><td>Attribute_Value</td><td></td>
    </tr>
	<?php
	

	if(isset($_GET['edit_id'])){
	
		$sql = "SELECT * FROM `t_metadata` JOIN ut_dcschema ON ut_dcschema.dc_schema = t_metadata.dc_element JOIN t_record ON t_record.record_id = t_metadata.record_id JOIN t_cataloguers_history ON t_record.record_id = t_cataloguers_history.record_id WHERE t_metadata.record_id = ".$_GET['edit_id']." ORDER BY t_metadata.record_id, sort_id ASC;";
		#echo $sql;
		$erg = $db->query($sql);
		$j=0;
		$dc_element = array();
		$dc_value = array();
		$attribute_value = array();
		while($row = $erg->fetch(PDO::FETCH_ASSOC)){

			array_push($dc_element,$row['dc_element']);
			array_push($dc_value,$row['value']);
			array_push($attribute_value,$row['attribute_value']);
			$j++;
		}
		
		$sql = "SELECT * FROM t_files WHERE record_id = ".$_GET['edit_id'].";";
		$erg_files = $db->query($sql);
		
	}
	else {$j=12; } #$j -> Zaehler fuer die auszugebenden Zielen an Kategorien. Standard fuer leere (neue) Formulare ist 12, bei Bearbeitungen von bestehnden Eintraegen wird der Wert oben anhand der vorliegenden Kategorien befuellt.
	
	#var_dump($dc_element);
	#var_dump($dc_value);
	
	for($i=0;$i<$j;$i++){
	
	echo "
    <tr>
    	<td>
        <select name='dc_elements[]' id='dc_elements".$i."'>
            <option value='contributor' "; if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','contributor'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'contributor');  } elseif($i==3 or $i==4) { echo " selected "; } echo ">Contributor (Herausgeber, weitere beteiligte Personen)</option>
            <option value='coverage'  ";  if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','coverage'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'coverage');  } elseif($i==10) { echo " selected "; } echo ">Coverage (Räumliche [und zeitliche] Abdeckung)</option>
            <option value='creator'  ";  if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','creator'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'creator');  }elseif($i==1 or $i==2) { echo " selected "; } echo ">Creator (Geistiger SchöpferIn)</option>
            <option value='date'  ";  if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','date'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'date');  }elseif($i==1 or $i==2) { echo " selected "; } elseif($i==6) { echo " selected "; } echo ">Date</option>
            <option value='description'>Description (Abstract, Inhaltsverzeichnis)</option> 
            <option value='format'  ";  if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','format'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'format');  }elseif($i==1 or $i==2) { echo " selected "; }  elseif($i==8) { echo " selected "; } echo ">Format (Dateiformat)</option> 
            <option value='identifier'>Identifier (AC#, ISBN)</option> 
            <option value='language'  "; if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','language'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'language');  }elseif($i==1 or $i==2) { echo " selected "; } elseif($i==0) { echo " selected "; } echo ">Language</option><!-- default: Deutsch -->
            <option value='publisher'>Publisher (Verleger)</option>
            <option value='relation'>Relation (Verknüpfung zu anderen Ressource)</option>
            <option value='rights'>Rights (Urheberrechte: local access / open access etc)</option>
            <option value='source'  "; if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','source'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'source');  }elseif($i==1 or $i==2) { echo " selected "; } elseif($i==7) { echo " selected "; } echo ">Source (Quelle des Dokuments - zB URL)</option>
            <option value='subject'  "; if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','subject'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'subject');  }elseif($i==1 or $i==2) { echo " selected "; } elseif($i==11 or $i==12) { echo " selected "; } echo ">Subject (Inhaltliche Erschließung - Sachschlagwörter)</option>
            <option value='title' "; if(isset($_POST['dc_elements'])) {  echo get_selected_value($i,$_POST['dc_elements'],'','title'); } elseif(isset($dc_element)) { echo get_selected_value($i,'',$dc_element,'title');  }elseif($i==1 or $i==2) { echo " selected "; } elseif($i==5) { echo " selected "; } echo ">Title </option>
            <option value='type'>Type (&quot;Formschlagwort&quot;)</option>
        </select>
        </td>
        <td><input type='text' name='value[]' style='width:500px;' value='";  if(isset($_POST['value'])) {  echo get_field_value($i,$_POST['value'],''); } elseif(isset($dc_value)) { echo get_field_value($i,'',$dc_value);  }echo "'/></td>
		<td><select name='attribute_type[]' id='attribute".$i."'>
			<option value='ressourceURI'>ressourceURI</option></select></td>
		<td><input type='text' name='attribute_value[]' style='width:200px;' value='"; if(isset($_POST['value'])) {  echo get_field_value($i,$_POST['attribute_value'],''); } elseif(isset($dc_value)) { echo get_field_value($i,'',$attribute_value);  }  echo "'/></td>
        <td><img src='img/plus.png' title='Add further Element' alt='Add further Element' class='new_cat'/></td>
    </tr> ";
	}
	?>
  

<?php 
if (isset($_GET['edit_id'])){
		while($row_files = $erg_files->fetch(PDO::FETCH_ASSOC)){
		echo "
		<tr>
			<td>Originaldatei:</td>
			<td><input type='text' id='txtSelectedFile'  name='file_name[]' style='cursor:pointer; width:500px;' value='".$row_files['file']."' onclick='openCustomRoxy2()' title='Hier klicken, um Originaldatei hochzuladen und auszuw&auml;hlen!'>
	<div id='roxyCustomPanel2' style='display: none;'>
	  <iframe src='jquery/fileman/index.html?integration=custom&type=files&txtFieldId=txtSelectedFile' style='width:100%;height:100%' frameborder='0'>
	  </iframe>
	</div></td>
			<td></td>
			<td></td>
			<td><img src='img/plus.png' title='Add further Element' alt='Add further Element' class='new_cat'/>  <img src='img/delete.png' title='Delete Element' alt='Delete Element' class='delete_cat'/></td>
		</tr>";
			
		}
    
    
}

?>
		<tr>
			<td>Originaldatei:</td>
			<td><input type='text' id='txtSelectedFile'  name='file_name[]' style='cursor:pointer; width:500px;' value='Hier klicken, um Originaldatei hochzuladen und auszuw&auml;hlen!' onclick='openCustomRoxy2()' title='Hier klicken, um Originaldatei hochzuladen und auszuw&auml;hlen!'>
	<div id='roxyCustomPanel2' style='display: none;'>
	  <iframe src='jquery/fileman/index.html?integration=custom&type=files&txtFieldId=txtSelectedFile' style='width:100%;height:100%' frameborder='0'>
	  </iframe>
	</div></td>
			<td></td>
			<td></td>
			<td><img src='img/plus.png' title='Add further Element' alt='Add further Element' class='new_cat'/></td>
		</tr>
    <tr>
    	<td>Bearbeiter:</td>
    	<td><input type="text" name='paraphe' value=''  />    </td><td></td>
    </tr>
    </tr>
    <tr>
    <td><input type="submit" name='submitForm' value='Eintragen' />
    <?php if(isset($_GET['edit_id'])){ echo "<input type='hidden' name='edit_record_id' value='".$_GET['edit_id']."'>"; } ?></td><td></td><td></td>
    </tr>
    </tr>
</table>
</form>

<table>
<?php 
$sql = "SELECT * FROM `t_metadata` JOIN ut_dcschema ON ut_dcschema.dc_schema = t_metadata.dc_element JOIN t_record ON t_record.record_id = t_metadata.record_id JOIN t_cataloguers_history ON t_record.record_id = t_cataloguers_history.record_id ORDER BY t_metadata.record_id, sort_id ASC";
$erg = $db->query($sql);

$record_id = 0;
$tr_class = 0;
$tr_counter = 0;
while($row = $erg->fetch(PDO::FETCH_ASSOC)){

	if($record_id<>$row['record_id'])
	{
		$tr_counter = 0;
		$record_id = $row['record_id']; 
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
		<td><a href=\"?cat=11&edit_id=".$row['record_id']."\"><img src='img/edit.png' alt='Bearbeiten' title='Bearbeiten' /></a></td>
		</tr>";
	}
	else {
		$tr_counter++;
	}
	

	echo "<tr class=' mod".$tr_class."'>";
	echo "<td>dc:".$row['dc_element']."</td>";
	echo "<td>".($row['value']);
		if(!empty($row['attribute_value'])){ echo " <a href='http://d-nb.info/gnd/".$row['attribute_value']."' target='_blank'>".$row['attribute_value']."</a>"; }
	echo "</td>";
	echo "<td>".$row['record_id']."</td>";
	echo "<td>"; if($tr_counter==0){ echo ""; } echo "</td>";
	echo "</tr>";
	
}
?>
</table>
<script>

$("body").on('click', 'img.new_cat', function(){

	tr_html = $(this).parents('tr').html()
	row = $(this).parents('tr').index();
	console.log('click');
	$(this).parents('tr').after("<tr>"+tr_html+"</tr>");

});

$("body").on('click', 'img.delete_cat', function(){

	$(this).parents('tr').remove();

});

</script>
</body>
</html>