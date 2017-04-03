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

      if (isset($row['tipo_evaluador'])) {
        $row['campo_resultado'] = "";

        if ($row['tipo_evaluador'] == "Autoevaluador") {
          $row['campo_resultado'] = "autoevaluador";
        }

        if ($row['tipo_evaluador'] == "Coach") {
          $row['campo_resultado'] = "coach";
        }

        if ($row['tipo_evaluador'] == "Coach 360") {
          $row['campo_resultado'] = "coach_360";
        }

        if ($row['tipo_evaluador'] == "Colaborador") {
          $row['campo_resultado'] = "colaborador";
        }
      }

  		$arr[] = $row;
  	}

  	return $arr;
  }

  function getUrl() {
    return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  }

  function getNextUrl($p = 'p') {
    $link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    $url_ = explode('?', $link);

    if (count($url_) > 1) {
      $var_str = explode('&', $url_[1]);

      foreach ($var_str as $vs) {
        $arr = explode('=', $vs);

        if ($arr[0] == $p) {
          $link = str_replace($vs, $p."=".(intval($arr[1]) + 1), $link);
        }
      }
    }

    return $link;
  }

  function redirect() {
    echo "Redirigiendo en 3 segundos a la pagina principal..<br>";
    echo "
      <script> setTimeout(() => { window.location = '../?completado=".$_GET['a']."' }, 3000)  </script>
    ";
  }
?>