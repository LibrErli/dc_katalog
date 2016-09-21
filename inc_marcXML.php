<?php
#header('Content-Type: text/html; charset=utf-8');
class marcXMLParser {

	const DNB_VALUE = 'Nationalbib';

	public $marcXML;
	public $db;
	function __construct($file, $db=''){

		#echo $file;
		$url_arr = get_headers($file);
		$url_str = $url_arr[0];
		if(strpos($url_str,"200")){
			$str = file_get_contents($file);
			$str = preg_replace('/&#152;/','',$str);
			$str = preg_replace('/&#156;/','',$str);
			$this->marcXML = simplexml_load_string($str);	
			#echo var_dump($this->marcXML);
			$this->marcXML->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
			$this->marcXML->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			$this->db = $db;

		}
		else {
			echo "Keine Informationen für diese DNB-ID ".$dnb_id;		
			exit();
		}
		
	}

	public static function parseXML($file) {
			$str = file_get_contents($file);
			$marcXML = simplexml_load_string($str);	
			#echo var_dump($this->marcXML);
			$marcXML->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
			$marcXML->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			return $marcXML;
	}

	function NoR(){
		return $this->marcXML->numberOfRecords;
	}
	
	public static function NumOfRows($xml){
		return $xml->numberOfRecords;
	}
	
	function NoR_inRespond(){
		return count($this->marcXML->records->record);
		
	}
	
	function r4r(){
		$ret = array();
		$i = 0;
		foreach($this->marcXML->records->record as $record){
			$record->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			$ret[$i]['dnb_id'] = $this->dnb_id($record);
			$ret[$i]['titel'] = $this->titel($record);
			$ret[$i]['contributor'] = $this->contributor($record);
			$ret[$i]['hs_schrift'] = $this->hs_schrift($record);
			$ret[$i]['verlag'] = $this->verlag($record);
			$ret[$i]['seiten'] = $this->seiten($record);
			$ret[$i]['isbn'] = $this->isbn($record);
			$ret[$i]['preis'] = $this->preis($record);
			$ret[$i]['swf'] = $this->swf($record);
			$dnb_sg = $this->parse_datafield_by_sub($record,'084','a',"",false,true);
			$ret[$i]['dnb_sg'] = $this->get_dnbsg_label($dnb_sg);
			$ret[$i]['toc'] = $this->toc($record);
			$ret[$i]['inhaltstext'] = $this->inhaltstext($record);
			#$ret[$i]['erscheinungstermin'] = $this->parse_datafield_by_sub($record,'263','a',"",false,false);
			$ret[$i]['erscheinungstermin'] = $this->erscheinungstermin($record);
			$i++;
		}
		return $ret;
	}
	
	function test(){
		$ret = array();
		$i = 0;
		foreach($this->marcXML->records->record as $record){
			$record->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			echo $this->toc($record)."<br>";
		}
	}
	
	
	function parse_for_dnbinfo($kat,$sub, $del = " ", $del_end = false, $as_array = false) {
		$str = $this->marcXML->xpath(".//slim:datafield[contains(@tag, '".$kat."')]/slim:subfield[contains(@code, '".$sub."')]");
		$ret = "";
		if ( array_key_exists(0,$str) ) {  
			if($as_array==false){
			for($i=0;$i<count($str);$i++) {
				if($i < count($str) ) { $ret .= $str[$i].$del; } else { $ret .= $str[$i]; }
			}
			if($del_end == true) { $ret.=$del; }
			#echo ($ret)."<br/>";
			return $ret;
			}
			else { return $str; }
		}
	}
	
	
	function parse_datafield_by_sub($xml,$kat,$sub, $del = " ", $del_end = false, $as_array = false) {
		$str = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '".$kat."')]/slim:subfield[contains(@code, '".$sub."')]");
		$ret = "";
		if ( array_key_exists(0,$str) ) {  
			if($as_array==false){
			for($i=0;$i<count($str);$i++) {
				if($i < count($str) ) { $ret .= $str[$i].$del; } else { $ret .= $str[$i]; }
			}
			if($del_end == true) { $ret.=$del; }
			return $ret;
			}
			else { return $str; }
		}
	}
	
	function get_dnbsg_label($dnb_sg){
		$sql = "SELECT ddc_dnbsg_text FROM t_dnbsg WHERE ddc_dnbsg = :ddc_dnbsg;";
		$erg_dnbsg = $this->db->prepare($sql);
		$dnb_sg_str = "";
		for($j=0;$j<count($dnb_sg);$j++){
			#echo $dnb_sg[$j]."<br>";
			$daten = array('ddc_dnbsg' => $dnb_sg[$j]);
			$erg = $erg_dnbsg->execute($daten);
			$row = $erg_dnbsg->fetch(PDO::FETCH_OBJ);
			if(isset($row->ddc_dnbsg_text)) {
			$dnbsg_label = preg_split("/[0-9]{3}/",$row->ddc_dnbsg_text);
			$dnb_sg_str .= $dnb_sg[$j]." ".$dnbsg_label[0]."; ";
			}
		}		
		return $dnb_sg_str;
	}
	
	function dnb_id($xml){
		$dnb_id = $xml->xpath(".//slim:record/slim:controlfield[contains(@tag, '001')]");
		return $dnb_id[0];
	}
	
	function isbn($xml){
		$isbn = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '020')]/slim:subfield[contains(@code, '9')]");
		if(array_key_exists(0,$isbn)) { return $isbn[0]; } 
	}
	
	function titel($xml){
		$titel = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'a')]");
		$titelzusatz = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'b')]");
		$ret = $titel[0];
		if(array_key_exists(0,$titelzusatz)){ $ret.=" : ".$titelzusatz[0]; }
		$mbw = $this->mbw($xml);
		if(!empty($mbw)){ $ret .= " : ".$mbw; }
		return $ret;
	}
	function titelzusatz($xml){
		$titel = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'b')]");
		if(array_key_exists(0,$titel)) { return $titel[0];  } 
	}
	
	function mbw($xml){
		$nr = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'n')]");
		$titel = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'p')]");
		$ret = "";
		if(array_key_exists(0,$nr)) { $ret = $nr[0]." "; } 
		if(array_key_exists(0,$titel)) { $ret .= $titel[0];  } 
		return $ret;
	}
	
	function contributor($xml){
		$autor = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '245')]/slim:subfield[contains(@code, 'c')]");
		if(array_key_exists(0,$autor)) { 
			return $autor[0]; 
			#return "n.N.";
		} 
		#else { return "n.n."; }
	}
	
	function preis($xml){
		$hs = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '020')]/slim:subfield[contains(@code, 'c')]");
		if ( array_key_exists(0,$hs) ) {  return $hs[0]; }
	}	
	
	function verlag($xml){
		$vrlg = $xml->xpath	(".//slim:record/slim:datafield[contains(@tag, '264')]/slim:subfield");
		#echo var_dump($vrlg);
		$ret = "";
		for($i=0;$i<count($vrlg);$i++){
			#echo count($vrlg[$i]['code']
			if($vrlg[$i]['code']=='a') { $ret .= $vrlg[$i]." : "; }
			elseif($vrlg[$i]['code']=='b') { $ret .= $vrlg[$i].", "; }
			else { $ret .= $vrlg[$i]; }
			
		}
		return $ret;
	}
	
	function hs_schrift($xml){
		#$hs = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '502')]/slim:subfield[contains(@code, 'a')]");
		#if ( array_key_exists(0,$hs) ) {  return $hs[0]; }
		$hs = $xml->xpath(".//slim:record/slim:datafield[contains(@tag, '502')]/slim:subfield");
		$ret = "";
		for($i=0;$i<count($hs);$i++){
			$ret .= $hs[$i].", ";
		}
		return $ret;
	}
	
	function seiten($xml) {
		$pp = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '300')]/slim:subfield[contains(@code, 'a')]");
		if ( array_key_exists(0,$pp) ) {  return $pp[0]; }
	}
	function erscheinungstermin($xml) {
		$erscheinungstermin = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '263')]/slim:subfield[contains(@code, 'a')]");
		if ( array_key_exists(0,$erscheinungstermin) ) {  if(preg_match("/[0-9]{6}/",$erscheinungstermin[0],$date_res)){ $ret =  "Erscheinungstermin: ".substr($date_res[0],4,2)."/".substr($date_res[0],0,4); return $ret; } }
	}
	
	function swf($xml){
		$swf = array();
		foreach($xml->xpath(".//slim:record/slim:datafield[contains(@tag, '689')]/slim:subfield[contains(@code, 'a')]") as $sw){
			array_push($swf,$sw);
		} 
		$ret = "";
		for($i=0;$i<count($swf);$i++){
			$ret .= $swf[$i];
			if($i<count($swf)){ $ret.= " ; "; }
		}
		return $ret;
	}
	
	function toc($xml) {
		$toc = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '856')]/slim:subfield[.='Inhaltsverzeichnis']/parent::*");
		if(array_key_exists(0,$toc)){
			#echo var_dump($toc[0]);
			$res = $toc[0];
			$res->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			$url = $res->xpath(".//slim:subfield[contains(@code, 'u')]");
			return $url[0];
		}
	}
	
	function inhaltstext($xml) {
		$toc = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '856')]/slim:subfield[.='Inhaltstext']/parent::*");
		if(array_key_exists(0,$toc)){
			#echo var_dump($toc[0]);
			$res = $toc[0];
			$res->registerXPathNamespace('slim', 'http://www.loc.gov/MARC21/slim');
			$url = $res->xpath(".//slim:subfield[contains(@code, 'u')]");
			return $url[0];
		}
	}
	
	public static function dnb_urn($xml) {
		$pp = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '024')]/slim:subfield[contains(@code, 'a')]");
		if ( array_key_exists(0,$pp) ) {  return $pp[0]; }
	}
	
	public static function dnb_datum($xml) {
		$pp = 	$xml->xpath(".//slim:record/slim:datafield[contains(@tag, '773')]/slim:subfield[contains(@code, 'g')]");
		for($i=0;$i<count($pp);$i++){
			if(preg_match('/[0-9]{1,2}.[0-9]{1,2}.[0-9]{2,4}/',$pp[$i])) { 
				$str = explode('.',$pp[$i]);
				return $str[2]."-".$str[1]."-".$str[0];
			}
		}
	}

	
}

?>