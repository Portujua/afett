<?php
    class DatabaseHandler
    {
        /* Documentacion PDO: 
        *  http://php.net/manual/es/book.pdo.php
        */

        // local, main, test
        private $connect_to = "local";

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
                $this->username = "eidoscon_root";
                $this->password = "-*[!5ReLVFZ6ykN1%,";
                $this->dsn = "mysql:dbname=eidoscon_arreporte;host=localhost";
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
                    proceso=:proceso
                where cedula=:cedula
            ");

            $query->execute(array(
                ":usuario" => $row['usuario'],
                ":email" => $row['email'],
                ":estado" => $row['estado'],
                ":proceso" => $row['proceso'],
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
        }

        public function crear_persona($row)
        {
            $query = $this->db->prepare("
                insert into AR_Persona (usuario, email, activo, nombre_completo, puesto_organizativo, rol_integral, unidad, trabaja_en, cedula, proceso)
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
                    :proceso)
            ");

            $query->execute(array(
                ":usuario" => $row['usuario'],
                ":email" => $row['email'],
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
    }
?>