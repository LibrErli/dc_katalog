<?php
/*Dieses Skript bearbeitet alle im Repositorium beinhalteten GND Eintraege und schreibt Deskriptoren und Non-Deskriptoren neu. */

include_once "server.inc.php";
include_once "../speis_kostladen/inc_marcXML.php";

$sql = "SELECT element_id, record_id, dc_element, attribute_value FROM t_metadata WHERE attribute_value <> '';";
$erg = $db->query($sql);


while($row = $erg->fetch(PDO::FETCH_ASSOC)){

	$sql_gnddata = "DELETE FROM t_gnddata WHERE gnd_id = '".trim($row['attribute_value'])."';";
	$db->query($sql_gnddata);
	
	$gnd = new marcXMLParser('http://d-nb.info/'.trim($row['attribute_value']).'/about/marcxml',$db);
	
	if($row['dc_element']=='subject'){
		$kat_des = '150';
		$kat_nondes = '450';
	}
	elseif ($row['dc_element']=='coverage'){
		$kat_des = '151';
		$kat_nondes = '451';
	}
	elseif ($row['dc_element']=='contributor'){
		$kat_des = '110';
		$kat_nondes = '410';
	}
	if(isset($kat_des) and isset($kat_nondes)){
		$deskriptor = $gnd->parse_for_dnbinfo($kat_des,'a');
		#$non_deskriptor = $gnd->parse_for_dnbinfo($kat_nondes,'a','','',true);
	}
	
	
	echo($deskriptor)."<br/>";
	$daten = array("deskriptor" => $deskriptor, "gnd_id" => trim($row['attribute_value']) );
	$sql = "UPDATE t_metadata SET value = :deskriptor, attribute_value = :gnd_id WHERE element_id = ".$row['element_id'].";";
	$erg_update = $db->prepare($sql);
	$erg_update->execute($daten);
	
	#var_dump($non_deskriptor)."<br/>";

	
}



?>