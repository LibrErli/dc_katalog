<?php
include_once "server.inc.php";

$sql = "SELECT record_id FROM t_record ORDER BY record_id;";
$erg_id = $db->query($sql);


#if(isset($_GET['id'])){
	$dc_ns = 'http://purl.org/dc/elements/1.1/';
		
	$xml = new DOMDocument('1.0','UTF-8');
	$rootNode = $xml->appendChild($xml->createElement('records'));
	$rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dc', $dc_ns);

	while($row_id = $erg_id->fetch(PDO::FETCH_ASSOC)){
		$id = $row_id['record_id'];
		$record = $rootNode->appendChild($xml->createElement('record'));

		#Schreiben der Verknuepfung zu den Dateien
		$sql = "SELECT * FROM t_files WHERE record_id = ".$id.";";
		$erg = $db->query($sql);
		while ($row=$erg->fetch(PDO::FETCH_ASSOC)){
			$element = $record->appendChild($xml->createElement('file'));
			$element->setAttribute('ressourceURI',$row['file']);	
		}

		$sql = "SELECT * FROM t_metadata WHERE record_id = ".$id.";";
		$erg = $db->query($sql);
			
		#Schreiben der metadaten-informationen	
		while($row=$erg->fetch(PDO::FETCH_ASSOC)){
			#echo $row['dc_element'];
			$element = $record->appendChild($xml->createElement('dc:'.$row['dc_element'],htmlspecialchars($row['value'])));
			if(!empty($row['attribute_value'])){ $element->setAttribute($row['attribute_type'],'http://d-nb.info/gnd/'.$row['attribute_value']); }		
		}
	}
	header('Content-type: text/xml');
	#var_dump($xml);
	echo $xml->saveXML();
	
#}
?>