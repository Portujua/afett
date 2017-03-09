<?php
	include_once('utils.php');
	include_once('databasehandler.php');

	$debug = isset($_GET['debug']);

	$dbh = new DatabaseHandler();

	if (!isset($_GET['a'])) {
		echo "Debe especificar qué se va a cargar.";
		die();
	}

	$fc = readFileContent("../export/{$_GET['a']}");
	$arr = convertToArray($fc);

#mapPersonas = ['id_persona', 'cedula', 'nombres', 'primer_apellido', 'segundo_apellido', 'email', 'estado', 'usuario', 'rol_integral', 'puesto_organizativo', 'unidad', 'proceso', 'empresa', 'sede', 'coach_cedula', 'coach_nombres', 'coach_primer_apellido', 'coach_segundo_apellido']
	$dbFields = array(
		"cedula" => ["campo" => "cedula", "tabla" => "AR_Persona", "pk" => true, "picklist" => false],
		"usuario" => ["campo" => "usuario", "tabla" => "AR_Persona", "pk" => false, "picklist" => false],
		"nombre_completo" => ["campo" => "nombre_completo", "tabla" => "AR_Persona", "pk" => false, "picklist" => false],
		"empresa" => ["campo" => "nombre", "tabla" => "AR_Empresa", "pk" => false, "picklist" => true, "createFn" => "crear_empresa"],
		"sede" => ["pk" => false, "picklist" => true, "createFn" => "crear_sede", "checkFn" => "check_sede"],
		"unidad" => ["pk" => false, "picklist" => true, "createFn" => "crear_unidad", "checkFn" => "check_unidad"],
	);

	$pks = [];

	foreach ($arr as $r) {
		if (count($r) == 0) {
			continue;
		}

		$pk = "";
		$pkField = "";
		$pkExists = null;
		// get the pk
		foreach ($r as $k => $v) {
			if (array_key_exists($k, $dbFields)) {
				if ($dbFields[$k]["pk"]) {
					$pk = $v;
					$pkField = $k;
					$pkExists = $dbh->check_existencia($dbFields[$k]["campo"], $v, $dbFields[$k]["tabla"]);
				}
			}
		}

		if ($pk == "") {
			echo "Clave primaria no encontrada.<br><br>Información de error:<br>";
			echo "Array length: " . count($r) . "<br>Array: ";
			print_r($r);
			echo "<br>";
			continue;
		}

		if (!in_array($pk, $pks)) {
			$pks[] = $pk;

			foreach ($r as $k => $v) {
				if (array_key_exists($k, $dbFields)) {
					if ($dbFields[$k]["picklist"]) {
						$c_ = isset($dbFields[$k]["checkFn"]) ? $dbh->$dbFields[$k]["checkFn"]($r) : $dbh->check_existencia($dbFields[$k]["campo"], $v, $dbFields[$k]["tabla"]);

						if ($c_) {
							echo $debug ? "'$k' con valor '$v' si existe.<br>" : "";
						}
						else {
							echo $debug ? "'$k' con valor '$v' debe ser creado.<br>" : "";

							if (isset($dbFields[$k]["createFn"])) {
								$dbh->$dbFields[$k]["createFn"]($r);
								echo $debug ? "'$k' con valor '$v' creado con éxito.<br>" : "";
							}
						}
					}
				}
			}

			// check if needs to be created or updated
			if ($pkExists) {
				echo $debug ? "<b>'$pkField' con valor '$pk' debe ser actualizado</b><br>" : "";
				$dbh->actualizar_persona($r);
				echo $debug ? "<b>'$pkField' con valor '$pk' actualizado con éxito</b><br>" : "";
			}
			else {
				echo $debug ? "<b>'$pkField' con valor '$pk' debe ser creado</b><br>" : "";
			}
		}

		echo $debug ? "<br>" : "";
	}
?>