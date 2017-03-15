<?php
	function readFileContent($filename, $convertUTF = true) {
    $fp = fopen($filename, "r");
    $content = fread($fp, filesize($filename));
    fclose($fp);

    return $convertUTF ? utf8_encode($content) : $content;
  }

  function convertToArray($data, $delimiter = '{}{}{}') {
  	$data = str_replace('None', 'null', $data);
  	$data = str_replace('\'', '"', $data);
  	
  	$arr = array();

  	foreach (explode($delimiter, $data) as $l) {
  		if (strlen($l) == 0) {
  			continue;
  		}

  		$row = json_decode($l, true);

  		// Fix personas
  		if (isset($row['nombres'])) {
  			$row['nombre_completo'] = $row['nombres'] . (strlen($row['primer_apellido']) > 0 ? " ".$row['primer_apellido'] : "") . (strlen($row['segundo_apellido']) > 0 ? " ".$row['segundo_apellido'] : "");

  			$row['coach_nombre_completo'] = $row['coach_nombres'] . (strlen($row['coach_primer_apellido']) > 0 ? " ".$row['coach_primer_apellido'] : "") . (strlen($row['coach_segundo_apellido']) > 0 ? " ".$row['coach_segundo_apellido'] : "");
  		}

      // Fix resultados
      if (isset($row['resultado_pondersdo'])) {
        $row['resultado_ponderado'] = $row['resultado_pondersdo'];
      }
      
      if (isset($row['id_valutprest']) && isset($row['prg_riga'])) {
        $row['id_resultado'] = $row['id_valutprest'].','.$row['prg_riga'];
      }

      if (isset($row['evaluador_nombres'])) {
        $row['evaluador_nombre_completo'] = $row['evaluador_nombres'] . (strlen($row['evaluador_primer_apellido']) > 0 ? " ".$row['evaluador_primer_apellido'] : "") . (strlen($row['evaluador_segundo_apellido']) > 0 ? " ".$row['evaluador_segundo_apellido'] : "");
      }

  		$arr[] = $row;
  	}

  	return $arr;
  }
?>