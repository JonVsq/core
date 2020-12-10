<?php
require_once('Conexion.php');
class Nucleo
{
    //VARIABLE UTILIZADA PARA ESTABLECER LA CONEXION A LA BASE DE DATOS
    private $conexion;
    //VARIABLE UTILIZADA PARA ALMACENAR EL QUERY PERSONALIZADO POR EL DESARROLLADOR
    private $queryPersonalizado;
    //VARIABLE UTILIZADA PARA ESTABLECER LA TABLA EN LA BASE DE DATOS
    //DONDE SE EJECUTARA ALGUNA OPCION
    private $tablaBase;
    //VARIABLE UTILIZADA PARA DETERMINAR SI UNA CONSULTA DEBE SER ORDENADA
    private $ordenar;
    //VARIABLE UTILIZADA PARA ALMACENAR EL TOTAL DE CAMPOS EN UNA TABLA DE LA BASE DE DATOS
    private $totalCampos;
    //VARIABLE UTILIZADA PARA DETERMINAR SI EL DESARROLLADOR
    //DESEA OBTENER EL ID DEL REGISTRO ALMACENADO EN LA BASE DE DATOS
    //SOLO ES POSIBLE EN TABLAS CON PRIMARY KEY SERIAL O AUTOINCREMENTABLE
    private $regresarId = false;
    //VARIABLE UTILIZADA PARA ALMACENAR TEMPORALMENTE EL ID DEL REGISTRO ALMACENADO
    private $id;
    //VARIABLES PARA EL ENTORNO DE PAGINACION
    //TOTAL DE REGISTROS A MOSTRAR POR PAGINA
    private $porPagina = 10;
    //MAXIMO DE ENLACES PARA PAGINAS A GENERAR
    private $maximoEnlace = 4;

    //ALMACENA LA QUERY NECESARIA PARA CALCULAR EL TOTAL DE REGISTROS DE LA PAGINACION
    private $queryTotalRegistroPag;
    //ALMACENA LA QUERY NECESARIA PARA EXTRAER LOS REGISTROS DE LA PAGINACION
    private $queryExtractRegistroPag;
    //UTILIZADA PARA IDENTIFICAR EL NUMERO DE PAGINA QUE EL USUARIO SOLICITA
    private $numPagina;
    //UTILIZADA PARA ALMACENAR EL TOTAL DE PAGINAS QUE SE OBTENDRAN
    private $total_paginas;



    public function __construct()
    {
        $obj = new Conexion();
        $this->conexion = $obj->conectar();
    }

    //SECCION DE INSERCION
    public function insertarRegistro(array $campos)
    {
        return $this->nuevoRegistro($campos);
    }
    //SECCION DE MODIFICACION
    public function modificarRegistro(array $campos)
    {
        return $this->actualizar($campos);
    }
    //SECCION DE LISTAR 
    public function getDatos()
    {
        return $this->obtenerData();
    }
    public function getDatosParametros($campos)
    {
        return $this->obtenerDataParametros($campos);
    }
    //SECCION DE ELIMINACION
    public function eliminarTodo()
    {
        return $this->eliminaDatosTabla();
    }
    public function eliminarRegistro(array $campos)
    {
        return $this->eliminaPorQuery($campos);
    }
    //SECCION DE PAGINACION
    public function getDatosHtml(array $campos, array $botones, $identificador)
    {
        return $this->generarTablaHtml($campos, $botones, $identificador);
    }

    private function nuevoRegistro($campos)
    {
        try {
            $consulta = "";
            if (!empty($this->tablaBase)) {
                $this->totalCampos = count($campos);
                if (!empty($this->queryPersonalizado)) {
                    $values = "";
                    for ($i = 0; $i < $this->totalCampos; $i++) {
                        $values = $values . "?, ";
                    }
                    $values = substr($values, 0, -2);
                    $consulta = "INSERT INTO $this->tablaBase (" . $this->queryPersonalizado . ") VALUES(" . $values . ");";
                } else {
                    if (GESTOR == 1) {
                        $consulta = $this->getInToPSQL();
                    } else if (GESTOR == 2) {
                        $consulta = $this->getInToMariaDB();
                    }
                }
                if (!empty($consulta)) {
                    $resultado = $this->conexion->prepare($consulta);
                    $resultado->execute($campos);
                    if ($resultado->rowCount() > 0) {
                        $this->id = $this->conexion->lastInsertId();
                        $resultado->closeCursor();
                        $respuesta = $this->regresarId ? $this->id : true;
                        $this->limpiarEntorno();
                        return $respuesta;
                    }
                    $resultado->closeCursor();
                    $this->limpiarEntorno();
                    return false;
                }
            }
            return null;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    private function actualizar($campos)
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "";
                $this->totalCampos = count($campos);
                $resultado = null;
                if (empty($this->queryPersonalizado)) {
                    if (GESTOR == 1) {
                        $consulta = $this->getUpDatePSQL($campos);
                    } else if (GESTOR == 2) {
                        $consulta = $this->getUpDateMariaDB($campos);
                    }

                    $resultado = $this->conexion->prepare($consulta);
                    for ($i = 1; $i < count($campos); $i++) {
                        $resultado->bindValue($i, $campos[$i]);
                    }
                } else {
                    $consulta = "UPDATE $this->tablaBase SET " . $this->queryPersonalizado;
                    $resultado = $this->conexion->prepare($consulta);
                    for ($i = 0; $i < count($campos); $i++) {
                        $resultado->bindValue($i + 1, $campos[$i]);
                    }
                }
                if ($resultado->execute()) {
                    $resultado->closeCursor();
                    $this->limpiarEntorno();
                    return true;
                }
                $this->limpiarEntorno();
                $resultado->closeCursor();
            }
            return false;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //OBTIENE EL SQL INSERT IN TO SI LA TABLA PERTENECE A POSTGRESQL
    private function getInToPSQL()
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "SELECT column_name FROM information_schema.columns WHERE 
                table_schema = 'public' AND table_name = '$this->tablaBase'";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute()) {
                    $salida = array();
                    $parametros = "";
                    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    $totalColumnas = count($datos);
                    if ($totalColumnas >= $this->totalCampos) {
                        for ($i = ($totalColumnas - ($this->totalCampos)); $i < $totalColumnas; $i++) {
                            $salida[] = $datos[$i]['column_name'];
                            $parametros = $parametros . "? ,";
                        }
                        $resultado->closeCursor();
                        $parametros = substr($parametros, 0, -1);
                        return "INSERT INTO $this->tablaBase (" . implode(",", $salida) . ") VALUES (" . $parametros . ");";
                    }
                }
                $resultado->closeCursor();
            }
            return "";
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }

    //OBTIENE EL SQL INSERT IN TO SI LA TABLA PERTENECE A MARIADB
    private function getInToMariaDB()
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "show columns from $this->tablaBase";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute()) {
                    $salida = array();
                    $parametros = "";
                    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    $totalColumnas = count($datos);
                    if ($totalColumnas >= $this->totalCampos) {
                        for ($i = ($totalColumnas - ($this->totalCampos)); $i < $totalColumnas; $i++) {
                            $salida[] = $datos[$i]['Field'];
                            $parametros = $parametros . "? ,";
                        }
                        $resultado->closeCursor();
                        $parametros = substr($parametros, 0, -1);
                        return "INSERT INTO $this->tablaBase (" . implode(",", $salida) . ") VALUES (" . $parametros . ");";
                    }
                    $resultado->closeCursor();
                }
            }
            return "";
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }

    //OBTIENE TODOS LOS REGISTROS DE LA TABLA INDICADA
    private function obtenerData()
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "";
                if (!empty($this->queryPersonalizado)) {
                    $consulta = $this->queryPersonalizado;
                } else {
                    if (!empty($this->ordenar)) {
                        $consulta = "SELECT * FROM $this->tablaBase order by $this->ordenar;";
                    } else {
                        $consulta = "SELECT * FROM $this->tablaBase";
                    }
                }
                $resultado = $this->conexion->prepare($consulta);
                $resultado->execute();
                $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                $resultado->closeCursor();
                $this->limpiarEntorno();
                if ($datos) {
                    return $datos;
                }
            }
            return null;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //ELIMINA TODOS LOS REGISTROS DE UNA TABLA
    private function eliminaDatosTabla()
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "DELETE  FROM  $this->tablaBase;";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute() && $resultado->rowCount() > 0) {
                    $resultado->closeCursor();
                    $this->limpiarEntorno();
                    return true;
                }
                $this->limpiarEntorno();
                $resultado->closeCursor();
            }
            return false;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //ELIMINA UN REGISTRO
    private function eliminaPorQuery($campos)
    {
        try {
            if (!empty($this->tablaBase) && !empty($this->queryPersonalizado) && !empty($campos)) {
                $consulta = "DELETE FROM  $this->tablaBase " . $this->queryPersonalizado;
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute($campos) && $resultado->rowCount() > 0) {
                    $resultado->closeCursor();
                    $this->limpiarEntorno();
                    return true;
                }
                $this->limpiarEntorno();
                $resultado->closeCursor();
            }
            return false;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }

    //OBTIENE EL SQL UPTADE SI LA TABLA PERTENECE A POSTGRESQL
    private function getUpDatePSQL($campos)
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "SELECT column_name FROM information_schema.columns WHERE 
                table_schema = 'public' AND table_name = '$this->tablaBase'";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute()) {
                    $parametros = "";
                    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    $totalColumnas = count($datos);
                    if ($totalColumnas == $this->totalCampos) {
                        for ($i = 1; $i < $totalColumnas; $i++) {
                            $parametros = $parametros . $datos[$i]['column_name'] . " = ?, ";
                        }
                        $resultado->closeCursor();
                        $parametros = substr($parametros, 0, -2);
                        return "UPDATE $this->tablaBase SET " . $parametros . " WHERE {$datos[0]['column_name']} = " . $campos[0] . "; ";
                    }
                }
                $resultado->closeCursor();
            }
            return "";
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //OBTIENE EL SQL UPDATE SI LA TABLA PERTENECE A MARIADB
    private function getUpDateMariaDB($campos)
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "show columns from $this->tablaBase";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute()) {
                    $parametros = "";
                    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    $totalColumnas = count($datos);
                    if ($totalColumnas == $this->totalCampos) {
                        for ($i = 1; $i < $totalColumnas; $i++) {
                            $parametros = $parametros . $datos[$i]['Field'] . "= ?, ";
                        }
                        $resultado->closeCursor();
                        $parametros = substr($parametros, 0, -2);
                        return "UPDATE $this->tablaBase SET " . $parametros . " WHERE {$datos[0]['Field']} = " . $campos[0] . ";";
                    }
                    $resultado->closeCursor();
                }
            }
            return "";
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //OBTIENE TODOS LOS REGISTROS DE LA TABLA INDICADA CON PARAMETROS
    private function obtenerDataParametros($campos)
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "";
                if (!empty($this->queryPersonalizado)) {
                    $consulta = $this->queryPersonalizado;
                    $resultado = $this->conexion->prepare($consulta);
                    $resultado->execute($campos);
                    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    $resultado->closeCursor();
                    $this->limpiarEntorno();
                    if ($datos) {
                        return $datos;
                    }
                }
            }
            return null;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }


    //SECCION DE LA PAGINACION
    //LISTAR DATOS
    public function generarTablaHtml(array $campos, $botones, $identificador)
    {
        try {
            $empezarDesde = $this->iniciarDesde($this->numPagina);
            if (GESTOR == 1) {
                $this->queryExtractRegistroPag = $this->queryExtractRegistroPag  . " LIMIT " .  $this->porPagina . " OFFSET "  . $empezarDesde;
            } else {
                $this->queryExtractRegistroPag = $this->queryExtractRegistroPag  . " LIMIT " . $empezarDesde  . ", "  . $this->porPagina;
            }
            $totalRegistros = $this->numeroRegistros();
            $this->total_paginas = $this->numeroPaginas($totalRegistros);
            $datos = $this->consultarDatos();
            $tabla = "";
            //OBTIENE EL HTML A MOSTRAR
            foreach ($datos as $objeto) {
                $fila = "<tr class='text-center' >";
                foreach ($campos as $valor) {
                    $fila = $fila . "<td>{$objeto[$valor]}</td>";
                    $fila = $fila . "<td>";
                    foreach ($botones as $boton => $tipo)
                        $fila = $fila . "<button obj$boton='{$objeto[$identificador]}'  type='button'  class='$boton btn btn-primary btn-sm'>
                                        <i class='fas fa-$tipo'></i></button>";
                }
                $fila = $fila . "</td>";
                $fila = $fila . "</tr>\n";
                $tabla = $tabla . $fila;
            }
            $paginador = $this->enlaces();

            //ARRAY QUE SE ENVIARA A JS
            $data = array();
            $data["totalRegistros"] = $totalRegistros;
            $data["tabla"] = ($totalRegistros > 0) ? $tabla : "<td class='text-center text-info'>No hay registros para mostrar</td><td></td><td></td>";
            $data["paginador"] = $paginador;
            $data["totalPagina"] = $this->total_paginas;
            $data["paginaActual"] = ($totalRegistros > 0) ? $this->numPagina : 0;
            $data["desde"] = ($totalRegistros > 0) ? $empezarDesde + 1 : 0;
            if (($empezarDesde + $this->porPagina) > $totalRegistros) {
                $data["hasta"] = $totalRegistros;
            } else {
                $data["hasta"] = $empezarDesde + $this->porPagina;
            }
            return $data;
        } catch (PDOException $e) {
            die('Ocurrio un error: ' . $e->getMessage());
        }
    }
    //CALCULA DESDE DONDE SE EMPEZARA A TRAER LOS REGISTROS DE LA BASE DE DATOS
    private function iniciarDesde()
    {
        return ($this->numPagina - 1) * $this->porPagina;
    }
    //CALCULA EL NUMERO DE PAGINAS QUE SE VAN A MOSTRAR
    private function numeroPaginas($totalRegistros)
    {
        return ceil($totalRegistros / $this->porPagina);
    }

    //OBTIENE EL NUMERO DE REGISTROS EN LA BASE DE DATOS SEGUN QUERY
    private  function numeroRegistros()
    {
        try {
            $totalRegistros = 0;
            $resultado = $this->conexion->prepare($this->queryTotalRegistroPag);
            $resultado->execute();
            $totalRegistros = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $resultado->closeCursor();
            $totalRegistros = $totalRegistros[0]["total"];
            settype($totalRegistros, 'int');
            return $totalRegistros;
        } catch (PDOException $e) {
            die("Ocurrio un error en la consulta: " . $e->getMessage());
        }
    }
    //CONSULTA LOS DATOS
    private function consultarDatos()
    {
        try {
            $resultado = $this->conexion->prepare($this->queryExtractRegistroPag);
            $resultado->execute();
            $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $resultado->closeCursor();
            return $datos;
        } catch (PDOException $e) {
            die("Ocurrio un error en la consulta: " . $e->getMessage());
        }
    }
    //GENERA LOS ENLACES DE LA PAGINACION
    private function enlaces()
    {
        $paginador = "";
        $indice = $this->numPagina;
        $siguiente = 0;
        if ($this->total_paginas > $this->maximoEnlace) {
            if ($this->numPagina == 1) {
                $paginador = $paginador .  "<li  class='page-item disabled'>
                                         <a class='page-link' href='#'>Anterior</a></li>";
            } else {
                $paginador = $paginador .  "<li pag = '" . ($this->numPagina - 1) . "' class='siguiente page-item'>
                                         <a class='page-link' href='#'>Anterior</a></li>";
            }
            if ($this->numPagina == 1) {
                $paginador = $paginador . "<li   class='page-item active'><a class='page-link disable' href='#'>1</a></li>";
            } else {
                $paginador = $paginador . "<li  pag = '1' class='pagina page-item'><a class='page-link' href='#'>1</a></li>";
            }
            if ((($this->total_paginas) - $this->numPagina < $this->maximoEnlace) && $this->numPagina >= $this->maximoEnlace) {
                $inicio = (($this->total_paginas) - $this->maximoEnlace);
                while ($this->maximoEnlace > 0) {
                    if ($inicio > $this->total_paginas) {
                        break;
                    } else {
                        if ($inicio != 1 && $inicio != $this->total_paginas) {
                            if ($inicio == $this->numPagina) {
                                $paginador = $paginador . "<li   class='page-item active'><a class='page-link disable' href='#'>{$inicio}</a></li>";
                            } else {
                                $paginador = $paginador . "<li  pag = '{$inicio}' class='pagina page-item'><a class='page-link' href='#'>{$inicio}</a></li>";
                            }
                            $this->maximoEnlace--;
                            $indice++;
                        }
                    }
                    $inicio++;
                }
            } else {
                for ($i = $this->numPagina; $i <= $this->total_paginas; $i++) {
                    if ($this->maximoEnlace == 0) {
                        break;
                    }
                    if ($i != 1 && $i != $this->total_paginas) {
                        if ($i == $this->numPagina) {
                            $paginador = $paginador . "<li   class='page-item active'><a class='page-link disable' href='#'>{$i}</a></li>";
                        } else {
                            $paginador = $paginador . "<li  pag = '{$i}' class='pagina page-item'><a class='page-link' href='#'>{$i}</a></li>";
                        }
                        $this->maximoEnlace--;
                    }
                    $indice++;
                }
            }
            if ($this->numPagina == $this->total_paginas) {
                $paginador = $paginador . "<li   class='page-item active'><a class='page-link disable' href='#'>$this->total_paginas</a></li>";
            } else {
                $paginador = $paginador . "<li  pag = '$this->total_paginas' class='pagina page-item'><a class='page-link' href='#'>$this->total_paginas</a></li>";
            }
            if ($indice > $this->total_paginas) {
                $paginador = $paginador .  "<li  class='page-item disabled'>
                                             <a class='page-link' href='#'>Siguiente</a></li>";
            } else {
                $paginador = $paginador .  "<li pag = '" . ($indice) . "' class='siguiente page-item'>
                                             <a class='page-link' href='#'>Siguiente</a></li>";
            }
        } else {
            $paginador = $paginador .  "<li  class='page-item disabled'>
                                         <a class='page-link' href='#'>Anterior</a></li>";
            for ($i = 1; $i <= $this->total_paginas; $i++) {
                if ($i == $this->numPagina) {
                    $paginador = $paginador . "<li   class='page-item active'><a class='page-link disable' href='#'>{$i}</a></li>";
                } else {
                    $paginador = $paginador . "<li  pag = '{$i}' class='pagina page-item'><a class='page-link' href='#'>{$i}</a></li>";
                }
            }
            $paginador = $paginador .  "<li  class='page-item disabled'>
                                         <a class='page-link' href='#'>Siguiente</a></li>";
        }
        return $paginador;
    }
    private function limpiarEntorno()
    {

        $this->queryPersonalizado = "";
        $this->ordenar = "";
        $this->totalCampos = 0;
        $this->regresarId = false;
        $this->id = "";
    }
    /**
     * Get the value of queryPersonalizado
     */
    public function getQueryPersonalizado()
    {
        return $this->queryPersonalizado;
    }

    /**
     * Set the value of queryPersonalizado
     *
     * @return  self
     */
    public function setQueryPersonalizado($queryPersonalizado)
    {
        $this->queryPersonalizado = $queryPersonalizado;

        return $this;
    }

    /**
     * Get the value of tablaBase
     */
    public function getTablaBase()
    {
        return $this->tablaBase;
    }

    /**
     * Set the value of tablaBase
     *
     * @return  self
     */
    public function setTablaBase($tablaBase)
    {
        $this->tablaBase = $tablaBase;

        return $this;
    }

    /**
     * Get the value of ordenar
     */
    public function getOrdenar()
    {
        return $this->ordenar;
    }

    /**
     * Set the value of ordenar
     *
     * @return  self
     */
    public function setOrdenar($ordenar)
    {
        $this->ordenar = $ordenar;

        return $this;
    }
    /**
     * Get the value of regresarId
     */
    public function getregresarId()
    {
        return $this->regresarId;
    }

    /**
     * Set the value of regresarId
     *
     * @return  self
     */
    public function setRegresarId($regresarId)
    {
        $this->regresarId = $regresarId;

        return $this;
    }

    /**
     * Get the value of porPagina
     */
    public function getPorPagina()
    {
        return $this->porPagina;
    }

    /**
     * Set the value of porPagina
     *
     * @return  self
     */
    public function setPorPagina($porPagina)
    {
        $this->porPagina = $porPagina;

        return $this;
    }

    /**
     * Get the value of maximoEnlace
     */
    public function getMaximoEnlace()
    {
        return $this->maximoEnlace;
    }

    /**
     * Get the value of queryTotalRegistroPag
     */
    public function getQueryTotalRegistroPag()
    {
        return $this->queryTotalRegistroPag;
    }

    /**
     * Set the value of queryTotalRegistroPag
     *
     * @return  self
     */
    public function setQueryTotalRegistroPag($queryTotalRegistroPag)
    {
        $this->queryTotalRegistroPag = $queryTotalRegistroPag;

        return $this;
    }

    /**
     * Get the value of queryExtractRegistroPag
     */
    public function getQueryExtractRegistroPag()
    {
        return $this->queryExtractRegistroPag;
    }

    /**
     * Set the value of queryExtractRegistroPag
     *
     * @return  self
     */
    public function setQueryExtractRegistroPag($queryExtractRegistroPag)
    {
        $this->queryExtractRegistroPag = $queryExtractRegistroPag;

        return $this;
    }

    /**
     * Get the value of numPagina
     */
    public function getNumPagina()
    {
        return $this->numPagina;
    }

    /**
     * Set the value of numPagina
     *
     * @return  self
     */
    public function setNumPagina($numPagina)
    {
        $this->numPagina = $numPagina;

        return $this;
    }
}
