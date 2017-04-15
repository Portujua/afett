<?php
    class DatabaseHandler
    {
        /* Documentacion PDO: 
        *  http://php.net/manual/es/book.pdo.php
        */

        // local, main, test
        private $connect_to = "main";

        private $db;

        public function __construct()
        {
            if ($this->connect_to == "local")
            {
                $this->username = "root";
                $this->password = "21115476";
                $this->dsn = "mysql:dbname=cargamasiva;host=localhost";
            }
            elseif ($this->connect_to == "main")
            {
                $this->username = "arreporte";
                $this->password = "-*[!5ReLVFZ6ykN1%,";
                $this->dsn = "mysql:dbname=arreporte;host=localhost";
            }
            elseif ($this->connect_to == "test")
            {
                $this->username = "eidoscon_root";
                $this->password = "-*[!5ReLVFZ6ykN1%,";
                $this->dsn = "mysql:dbname=eidoscon_arreporte_test;host=localhost";
            }

            $this->connect();
        }

        public function connect()
        {
            if (!$this->db instanceof PDO)
            {
                try
                {
                    $this->db = new PDO($this->dsn, $this->username, $this->password);       
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch (Exception $ex)
                {
                    echo $ex;
                    die();
                }
            }

            $this->db->query("SET CHARSET utf8");
        }


        /* Funciones ejemplo */

        public function ejemploInsert($nombre, $apellido)
        {
            $query = $this->db->prepare("
                INSERT INTO Persona (nombre, apellido)
                VALUES (':nombre', ':apellido')
            ");

            $query->execute(array(
                ":nombre" => $nombre,
                ":apellido" => $apellido
            ));

            // Ejemplo obtener el id de eso que acabamos de añadir
            $ultimoIdAnadido = $this->db->lastInsertId();
        }

        public function ejemploLeer()
        {
            $query = $this->db->prepare("SELECT * FROM Persona");
            $query->execute(); 
            // En este punto $query es un objeto de PDO
            // Sin embargo aun no contiene lo que pedimos
            // Para ello hacemos:
            $datos = $query->fetchAll();
            // fetchAll devuelve un arreglo con las filas de respuesta
            // No es recomendable cambiar el valor de $query
            // Por ejemplo: $query = $query->fetchAll()
            // Ya que perderiamos la posibilidad de obtener cosas como:
            // La cantidad de filas respuesta:
            $nroFilasRespuesta = $query->rowCount();
            // Asi como tambien la posibilidad de recorrerlo con un foreach
            foreach ($query as $filaRespuesta)
            {
                // algo
            }
        }





        /* Funciones nuevas aqui abajo */
        public function extract_rol($s) {
            $ex = explode(' - ', $s);
            return count($ex) > 0 ? $ex[0] : $s;
        }

        public function extract_proceso($s) {
            $ex = explode(' - ', $s);
            return count($ex) > 1 ? $ex[1] : null;
        }

        public function asignar_coach($row) {
            try {
                $query = $this->db->prepare("
                    insert into AR_Persona_Coach (persona, coach, empieza)
                    values ((select id from AR_Persona where cedula=:persona), (select id from AR_Persona where nombre_completo=:coach), now())
                ");

                $query->execute(array(
                    ":persona" => $row['cedula'],
                    ":coach" => $row['coach_nombre_completo']
                ));
            } catch (Exception $ex) {
                echo isset($_GET['debug']) ? "Coach '".$row['coach_nombre_completo']."' no existe<br>" : "";
            }
        }

        public function actualizar_persona($row)
        {
            $query = $this->db->prepare("
                update AR_Persona 
                set
                    usuario=:usuario,
                    nombre_completo=:nombre_completo,
                    trabaja_en=(select id from AR_Sede where nombre=:sede and empresa=(select id from AR_Empresa where nombre=:empresa)),
                    unidad=(select id from AR_Unidad where nombre=:unidad and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))),
                    puesto_organizativo=(select id from AR_Puesto_Organizativo where nombre=:puesto_organizativo and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))),
                    rol_integral=(select id from AR_Rol_Integral where nombre=:rol_integral and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa)) and subproceso=(select id from AR_Proceso where nombre=:subproceso)),
                    email=:email,
                    activo=:estado,
                    proceso=:proceso,
                    id_persona=:id_persona
                where cedula=:cedula
            ");

            $query->execute(array(
                ":usuario" => $row['usuario'],
                ":email" => $row['email'],
                ":estado" => $row['estado'],
                ":proceso" => $row['proceso'],
                ":id_persona" => $row['id_persona'],
                ":nombre_completo" => $row['nombre_completo'],
                ":puesto_organizativo" => $row['puesto_organizativo'],
                ":rol_integral" => $row['rol_integral'],
                ":unidad" => $row['unidad'],
                ":sede" => $row['sede'],
                ":empresa" => $row['empresa'],
                ":cedula" => $row['cedula'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":subproceso" => $this->extract_proceso($row['rol_integral']),
            ));

            $this->asignar_coach($row);
        }

        public function crear_persona($row)
        {
            $query = $this->db->prepare("
                insert into AR_Persona (usuario, email, activo, nombre_completo, puesto_organizativo, rol_integral, unidad, trabaja_en, cedula, proceso, id_persona)
                values
                    (:usuario,
                    :email,
                    :estado,
                    :nombre_completo,
                    (select id from AR_Puesto_Organizativo where nombre=:puesto_organizativo and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))),
                    (select id from AR_Rol_Integral where nombre=:rol_integral and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa)) and subproceso=(select id from AR_Proceso where nombre=:subproceso)),
                    (select id from AR_Unidad where nombre=:unidad and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))),
                    (select id from AR_Sede where nombre=:sede and empresa=(select id from AR_Empresa where nombre=:empresa)),
                    :cedula,
                    :proceso,
                    :id_persona)
            ");

            $query->execute(array(
                ":usuario" => $row['usuario'],
                ":email" => $row['email'],
                ":id_persona" => $row['id_persona'],
                ":estado" => $row['estado'],
                ":nombre_completo" => $row['nombre_completo'],
                ":puesto_organizativo" => $row['puesto_organizativo'],
                ":rol_integral" => $row['rol_integral'],
                ":unidad" => $row['unidad'],
                ":sede" => $row['sede'],
                ":empresa" => $row['empresa'],
                ":proceso" => $row['proceso'],
                ":cedula" => $row['cedula'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":subproceso" => $this->extract_proceso($row['rol_integral']),
            ));

            $this->asignar_coach($row);
        }

        public function check_existencia($campo, $valor, $tabla)
        {
            $query = $this->db->prepare("
                select *
                from $tabla
                where $campo=:valor
            ");

            $query->execute(array(
                ":valor" => $valor
            ));

            return $query->rowCount() > 0;
        }

        public function crear_empresa($row)
        {
            $query = $this->db->prepare("
                insert into AR_Empresa (nombre) values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $row['empresa']
            ));

            return $this->db->lastInsertId();
        }

        public function crear_sede($row)
        {
            if (!$this->check_existencia("nombre", $row['empresa'], "AR_Empresa")) {
                $this->crear_empresa($row);
            }

            $query = $this->db->prepare("
                insert into AR_Sede (nombre, empresa) 
                values (:nombre, (select id from AR_Empresa where nombre=:empresa))
            ");

            $query->execute(array(
                ":nombre" => $row['sede'],
                ":empresa" => $row['empresa']
            ));

            return $this->db->lastInsertId();
        }

        public function crear_rol($row)
        {
            if (!$this->check_existencia("nombre", $row['empresa'], "AR_Empresa")) {
                $this->crear_empresa($row);
            }

            $query = $this->db->prepare("
                insert into AR_Rol (nombre, empresa) 
                values (:nombre, (select id from AR_Empresa where nombre=:empresa))
            ");

            $query->execute(array(
                ":nombre" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa']
            ));

            return $this->db->lastInsertId();
        }

        public function crear_proceso($row)
        {
            $query = $this->db->prepare("
                insert into AR_Proceso (nombre) 
                values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $this->extract_proceso($row['rol_integral'])
            ));

            return $this->db->lastInsertId();
        }

        public function crear_unidad($row)
        {
            if (!$this->check_existencia("nombre", $row['empresa'], "AR_Empresa")) {
                $this->crear_empresa($row);
            }

            if (!$this->check_rol($row)) {
                $this->crear_rol($row);
            }

            $query = $this->db->prepare("
                insert into AR_Unidad (nombre, rol) 
                values (
                    :nombre, 
                    (
                        select id from AR_Rol
                        where nombre=:rol
                            and empresa=(select id from AR_Empresa where nombre=:empresa)
                    )
                )
            ");

            $query->execute(array(
                ":nombre" => $row['unidad'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa']
            ));

            return $this->db->lastInsertId();
        }

        public function crear_puesto_organizativo($row)
        {
            if (!$this->check_existencia("nombre", $row['empresa'], "AR_Empresa")) {
                $this->crear_empresa($row);
            }

            if (!$this->check_rol($row)) {
                $this->crear_rol($row);
            }

            $query = $this->db->prepare("
                insert into AR_Puesto_Organizativo (nombre, rol) 
                values (
                    :nombre, 
                    (
                        select id from AR_Rol
                        where nombre=:rol
                            and empresa=(select id from AR_Empresa where nombre=:empresa)
                    )
                )
            ");

            $query->execute(array(
                ":nombre" => $row['puesto_organizativo'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa']
            ));

            return $this->db->lastInsertId();
        }

        public function crear_rol_integral($row)
        {
            if (!$this->check_existencia("nombre", $row['empresa'], "AR_Empresa")) {
                $this->crear_empresa($row);
            }

            if (!$this->check_rol($row)) {
                $this->crear_rol($row);
            }

            if (!$this->check_proceso($row)) {
                $this->crear_proceso($row);
            }

            $query = $this->db->prepare("
                insert into AR_Rol_Integral (nombre, rol, subproceso) 
                values (
                    :nombre, 
                    (
                        select id from AR_Rol
                        where nombre=:rol
                            and empresa=(select id from AR_Empresa where nombre=:empresa)
                    ),
                    (
                        select id from AR_Proceso
                        where nombre=:subproceso
                    )
                )
            ");

            $query->execute(array(
                ":nombre" => $row['rol_integral'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa'],
                ":subproceso" => $this->extract_proceso($row['rol_integral'])
            ));

            return $this->db->lastInsertId();
        }

        public function crear_resultado($row)
        {
            try {
                $query = $this->db->prepare("
                    insert into AR_Resultado (modo_de_evaluacion, realizado_por, evaluador, rol_evaluado, rol_evaluador, resultado, peso, resultado_ponderado, ano, competencia, resultado_consolidado, id_resultado, id_valutprest, prg_riga)
                    values (
                        :modelo_evaluacion,
                        (select id from AR_Persona where cedula=:evaluador_cedula),
                        (select id from AR_Persona where cedula=:cedula),
                        (select id from AR_Rol_Integral where nombre=:rol_evaluado and rol=(select id from AR_Rol where nombre=:rol and empresa=(select AR_Sede.empresa from AR_Persona, AR_Sede where AR_Persona.trabaja_en=AR_Sede.id and cedula=:cedula)) limit 1),
                        :rol_evaluador,
                        :resultado,
                        :peso,
                        :resultado_ponderado,
                        :ano,
                        (select id from AR_Competencia where nombre=:competencia),
                        :resultado_consolidado,
                        :id_resultado,
                        :id_valutprest,
                        :prg_riga)
                ");

                $query->execute(array(
                    ":rol" => $this->extract_rol($row['rol_evaluado']),
                    ":modelo_evaluacion" => $row['modelo_evaluacion'],
                    ":cedula" => $row['cedula'],
                    ":evaluador_cedula" => $row['evaluador_cedula'],
                    ":rol_evaluado" => $row['rol_evaluado'],
                    ":rol_evaluador" => $row['rol_evaluador'],
                    ":peso" => $row['peso'],
                    ":resultado" => $row['resultado'],
                    ":resultado_ponderado" => $row['resultado_ponderado'],
                    ":ano" => $row['ano'],
                    ":competencia" => $row['competencia'],
                    ":id_resultado" => $row['id_resultado'],
                    ":id_valutprest" => $row['id_valutprest'],
                    ":prg_riga" => $row['prg_riga'],
                    ":resultado_consolidado" => isset($row['resultado_consolidado']) ? $row['resultado_consolidado'] : 0.00,
                ));

                $rid = $this->db->lastInsertId();

                return $rid;
            }
            catch (Exception $ex) {
                echo "Error añadiendo resultado:<br>" . $ex->getMessage() . "<br>";
                print_r($row);
                echo "<br>";
            }
        }

        public function crear_indicadores($row)
        {
            try {
                $query = $this->db->prepare("
                    insert into AR_Resultado_Indicador (resultado, indicador, ".$row['campo_resultado'].", id_resultado)
                    values (
                        (select id from AR_Resultado where rol_evaluador=:tipo_evaluador and competencia=(select id from AR_Competencia where nombre=:competencia) and prg_riga=:prg_riga and id_valutprest=:id_valutprest),
                        (select id from AR_Indicador where codigo=:codigo and descripcion=:descripcion and competencia=(select id from AR_Competencia where nombre=:competencia)),
                        :puntuacion,
                        :id_resultado
                    )
                ");

                $query->execute(array(
                    ":prg_riga" => $row['prg_riga'],
                    ":id_valutprest" => $row['id_valutprest'],
                    ":codigo" => $row['me_question'],
                    ":descripcion" => $row['indicador'],
                    ":competencia" => $row['competencia'],
                    ":puntuacion" => $row['puntuacion'],
                    ":id_resultado" => $row['id_resultado'],
                    ":tipo_evaluador" => $row['tipo_evaluador'],
                ));

                return $query->rowCount() > 0;
            } catch (Exception $ex) {
                if (isset($_GET['debug'])) {
                    echo "Error añadiendo indicador: ".$ex."<br>";
                    print_r($row);
                    echo "<br>";
                }
                return false;
            }
        }

        public function actualizar_resultados() {
            $query = $this->db->prepare("
                select * from AR_Resultado where id_resultado is not null
            ");

            $query->execute();

            $resultados = $query->fetchAll();

            foreach ($resultados as $r) {
                echo "------------------------------<br>";
                echo "------------------------------<br>";
                print_r($r);
                echo "<br>";

                $query = $this->db->prepare("
                    select avg(resultado.resultado) as suma, resultado.peso as peso, resultado.modo_de_evaluacion
                    from AR_Resultado as resultado
                    where resultado.competencia=:competencia and resultado.evaluador=:evaluador and ano=:ano and modo_de_evaluacion=:modo_de_evaluacion
                    group by concat(resultado.competencia, '_', resultado.evaluador, '_', resultado.ano, '_', resultado.peso)
                ");

                $query->execute(array(
                    ":competencia" => $r['competencia'],
                    ":ano" => $r['ano'],
                    ":evaluador" => $r['evaluador'],
                    ":modo_de_evaluacion" => $r['modo_de_evaluacion']
                ));

                $vals = $query->fetchAll();

                $consolidado = 0.00;

                echo "Modo de evaluacion: ".$r['modo_de_evaluacion']."<br>";

                foreach ($vals as $v) {
                    if ($v['modo_de_evaluacion'] == '180') {
                        if ($v['peso'] == '0.25' || $v['peso'] == '0.75') {
                            echo floatval($v['suma']) . " x " . floatval($v['peso']) . " = " . floatval($v['suma']) * floatval($v['peso']) . "<br>";
                            $consolidado += floatval($v['suma']) * floatval($v['peso']);
                        }
                    }

                    if ($v['modo_de_evaluacion'] == '360') {
                        if ($v['peso'] == '0.25' || $v['peso'] == '0.35' || $v['peso'] == '0.4') {
                            echo floatval($v['suma']) . " x " . floatval($v['peso']) . " = " . floatval($v['suma']) * floatval($v['peso']) . "<br>";
                            $consolidado += floatval($v['suma']) * floatval($v['peso']);
                        }
                    }
                }

                echo "Consolidado: $consolidado <br>";

                $query = $this->db->prepare("
                    update AR_Resultado 
                    set
                        resultado_consolidado=:consolidado
                    where id=:id
                ");

                $query->execute(array(
                    ":consolidado" => $consolidado,
                    ":id" => $r['id'],
                ));
            }

            $query = $this->db->prepare("
                update AR_Resultado 
                set
                    resultado_consolidado=0.0
                where resultado_consolidado is null
            ");

            $query->execute();

            echo isset($_GET['debug']) ? "<strong>Resultados actualizados con éxito</b><br/>" : "";
        }

        public function actualizar_indicadores() {
            $query = $this->db->prepare("
                select  
                    ri.id as riid,
                    r.id as rid,
                    ri.id_resultado as id_resultado, 
                    ri.indicador as indicador,
                    r.peso as peso,
                    r.modo_de_evaluacion as modo_de_evaluacion
                from AR_Resultado_Indicador as ri, AR_Resultado as r
                where 
                    ri.resultado=r.id
                    and ri.id_resultado is not null
                group by concat(ri.id_resultado, ri.indicador)
            ");

            $query->execute();

            $resultados = $query->fetchAll();

            foreach ($resultados as $r) {
                if (isset($_GET['debug'])) {
                    echo "------------------------------<br>";
                    echo "------------------------------<br>";
                    print_r($r);
                    echo "<br>";
                }

                $query = $this->db->prepare("
                    select 
                        avg(resultado.autoevaluador) as suma_autoevaluador, 
                        avg(resultado.coach) as suma_coach,
                        avg(resultado.coach_360) as suma_coach_360,
                        avg(resultado.colaborador) as suma_colaborador,
                        resultado.indicador as indicador
                    from AR_Resultado_Indicador as resultado
                    where resultado.id_resultado=:id_resultado and resultado.indicador=:indicador
                    group by concat(resultado.indicador, '_', resultado.id_resultado)
                ");

                $query->execute(array(
                    ":id_resultado" => $r['id_resultado'],
                    ":indicador" => $r['indicador'],
                ));

                $vals = $query->fetchAll();

                $consolidado = 0.00;

                if (count($vals) > 1) {
                    echo "AQUIIIIIIIIIII";
                }

                foreach ($vals as $v) {
                    if ($r['modo_de_evaluacion'] == '180') {
                        if ($r['peso'] == '0.25' || $r['peso'] == '0.75') {
                            if (isset($_GET['debug'])) {
                                echo "<table>";

                                echo "<tr><td>Autoevaluador: </td><td>" . floatval($v['suma_autoevaluador']) . " x " . floatval($r['peso']) . " = " . floatval($v['suma_autoevaluador']) * floatval($r['peso']) . "</td></tr>";
                                echo "<tr><td>Coach: </td><td>" . floatval($v['suma_coach']) . " x " . floatval($r['peso']) . " = " . floatval($v['suma_coach']) * floatval($r['peso']) . "</td></tr>";
                                echo "</table>";
                            }
                            
                            $consolidado += floatval($v['suma_autoevaluador']) * floatval($r['peso']);
                            $consolidado += floatval($v['suma_coach']) * floatval($r['peso']);
                        }
                    }

                    if ($r['modo_de_evaluacion'] == '360') {
                        if ($r['peso'] == '0.25' || $r['peso'] == '0.35' || $r['peso'] == '0.4') {
                            if (isset($_GET['debug'])) {
                                echo "<table>";

                                echo "<tr><td>Autoevaluador: </td><td>" . floatval($v['suma_autoevaluador']) . " x " . floatval($r['peso']) . " = " . floatval($v['suma_autoevaluador']) * floatval($r['peso']) . "</td></tr>";
                                echo "<tr><td>Colaborador: </td><td>" . floatval($v['suma_colaborador']) . " x " . floatval($r['peso']) . " = " . floatval($v['suma_colaborador']) * floatval($r['peso']) . "</td></tr>";
                                echo "<tr><td>Coach 360: </td><td>" . floatval($v['suma_coach_360']) . " x " . floatval($r['peso']) . " = " . floatval($v['suma_coach_360']) * floatval($r['peso']) . "</td></tr>";
                                echo "</table>";
                            }
                            
                            $consolidado += floatval($v['suma_autoevaluador']) * floatval($r['peso']);
                            $consolidado += floatval($v['suma_colaborador']) * floatval($r['peso']);
                            $consolidado += floatval($v['suma_coach_360']) * floatval($r['peso']);
                        }
                    }
                }

                if (isset($_GET['debug'])) {
                    echo "<b>Consolidado: $consolidado </b><br>";
                }

                $query = $this->db->prepare("
                    update AR_Resultado_Indicador 
                    set
                        resultado_consolidado=:consolidado
                    where id=:id
                ");

                $query->execute(array(
                    ":consolidado" => $consolidado,
                    ":id" => $r['riid'],
                ));
            }

            $query = $this->db->prepare("
                update AR_Resultado_Indicador
                set
                    resultado_consolidado=0.0
                where resultado_consolidado is null
            ");

            $query->execute();

            echo isset($_GET['debug']) ? "<strong>Indicadores actualizados con éxito</b><br/>" : "";
        }

        public function check_sede($row)
        {
            $query = $this->db->prepare("
                select *
                from AR_Sede
                where nombre=:nombre and empresa=(select id from AR_Empresa where nombre=:empresa)
            ");

            $query->execute(array(
                ":nombre" => $row['sede'],
                ":empresa" => $row['empresa']
            ));

            return $query->rowCount() > 0;
        }

        public function check_unidad($row)
        {
            $query = $this->db->prepare("
                select *
                from AR_Unidad
                where nombre=:nombre 
                    and rol=(
                        select id from AR_Rol 
                        where nombre=:rol
                            and empresa=(select id from AR_Empresa where nombre=:empresa)
                    )
            ");

            $query->execute(array(
                ":nombre" => $row['unidad'],
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa']
            ));

            return $query->rowCount() > 0;
        }

        public function check_rol($row)
        {
            $query = $this->db->prepare("
                select id from AR_Rol 
                where nombre=:rol
                    and empresa=(select id from AR_Empresa where nombre=:empresa)
            ");

            $query->execute(array(
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":empresa" => $row['empresa']
            ));

            return $query->rowCount() > 0;
        }

        public function check_proceso($row)
        {
            $query = $this->db->prepare("
                select id from AR_Proceso 
                where nombre=:subproceso
            ");

            $query->execute(array(
                ":subproceso" => $this->extract_proceso($row['rol_integral'])
            ));

            return $query->rowCount() > 0 || $this->extract_proceso($row['rol_integral']) == null;
        }

        public function check_rol_integral($row)
        {
            $query = $this->db->prepare("
                select id from AR_Rol_Integral 
                where nombre=:rol_integral
                    and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))
                    and subproceso=(select id from AR_Proceso where nombre=:subproceso)
            ");

            $query->execute(array(
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":subproceso" => $this->extract_proceso($row['rol_integral']),
                ":rol_integral" => $row['rol_integral'],
                ":empresa" => $row['empresa']
            ));

            return $query->rowCount() > 0;
        }

        public function check_puesto_organizativo($row)
        {
            $query = $this->db->prepare("
                select id from AR_Puesto_Organizativo 
                where nombre=:puesto_organizativo
                    and rol=(select id from AR_Rol where nombre=:rol and empresa=(select id from AR_Empresa where nombre=:empresa))
            ");

            $query->execute(array(
                ":rol" => $this->extract_rol($row['rol_integral']),
                ":puesto_organizativo" => $row['puesto_organizativo'],
                ":empresa" => $row['empresa']
            ));

            return $query->rowCount() > 0;
        }

        public function check_resultado($row)
        {
            try {
                $query = $this->db->prepare("
                    select id from AR_Resultado
                    where 
                        modo_de_evaluacion=:modelo_evaluacion
                        and realizado_por=(select id from AR_Persona where cedula=:evaluador_cedula)
                        and evaluador=(select id from AR_Persona where cedula=:cedula)
                        and rol_evaluado=(select id from AR_Rol_Integral where nombre=:rol_evaluado and rol=(select id from AR_Rol where nombre=:rol and empresa=(select AR_Sede.empresa from AR_Persona, AR_Sede where AR_Persona.trabaja_en=AR_Sede.id and cedula=:cedula)) limit 1)
                        and rol_evaluador=:rol_evaluador
                        and peso=:peso
                        and resultado=:resultado
                        and resultado_ponderado=:resultado_ponderado
                        and ano=:ano
                        and competencia=(select id from AR_Competencia where nombre=:competencia)
                ");

                $query->execute(array(
                    ":rol" => $this->extract_rol($row['rol_evaluado']),
                    ":modelo_evaluacion" => $row['modelo_evaluacion'],
                    ":cedula" => $row['cedula'],
                    ":evaluador_cedula" => $row['evaluador_cedula'],
                    ":rol_evaluado" => $row['rol_evaluado'],
                    ":rol_evaluador" => $row['rol_evaluador'],
                    ":peso" => $row['peso'],
                    ":resultado" => $row['resultado'],
                    ":resultado_ponderado" => $row['resultado_ponderado'],
                    ":ano" => $row['ano'],
                    ":competencia" => $row['competencia'],
                ));

                return $query->rowCount() > 0;
            } catch (Exception $ex) {
                echo "Error chequeando resultado:<br>";
                print_r($row);
                echo "<br>";
                return false;
            }
        }

        public function check_indicadores($row)
        {
            try {
                $query = $this->db->prepare("
                    select id from AR_Resultado_Indicador
                    where 
                        resultado=(select id from AR_Resultado where rol_evaluador=:tipo_evaluador and competencia=(select id from AR_Competencia where nombre=:competencia) and prg_riga=:prg_riga and id_valutprest=:id_valutprest)
                        and indicador=(select id from AR_Indicador where codigo=:codigo and descripcion=:descripcion and competencia=(select id from AR_Competencia where nombre=:competencia) limit 1)
                        and ".$row['campo_resultado']."=:puntuacion
                ");

                $query->execute(array(
                    ":prg_riga" => $row['prg_riga'],
                    ":id_valutprest" => $row['id_valutprest'],
                    ":codigo" => $row['me_question'],
                    ":descripcion" => $row['indicador'],
                    ":competencia" => $row['competencia'],
                    ":puntuacion" => $row['puntuacion'],
                    ":tipo_evaluador" => $row['tipo_evaluador'],
                ));

                return $query->rowCount() > 0;
            } catch (Exception $ex) {
                if (isset($_GET['debug'])) {
                    echo "Error chequeando resultado indicador:<br>";
                    print_r($row);
                    echo "<br>";
                }
                return false;
            }
        }

        public function check_indicador($row)
        {
            try {
                $query = $this->db->prepare("
                    select id from AR_Indicador 
                    where 
                        codigo=:codigo 
                        and descripcion=:descripcion 
                        and competencia=(select id from AR_Competencia where nombre=:competencia)
                ");

                $query->execute(array(
                    ":codigo" => $row['me_question'],
                    ":descripcion" => $row['indicador'],
                    ":competencia" => $row['competencia'],
                ));

                return $query->rowCount() > 0;
            } catch (Exception $ex) {
                if (isset($_GET['debug'])) {
                    echo "Error chequeando indicador:<br>";
                    print_r($row);
                    echo "<br>";
                }
                return false;
            }
        }
    }
?>