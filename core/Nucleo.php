<?php
require('Conexion.php');
class Nucleo
{
    private $conexion;
    private $queryPersonalizado;
    private $tablaBase;
    private $ordenar;
    private $totalCampos;
    private $regresarId = false;
    private $id;
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
    //SECCION DE ELIMINACION
    public function eliminarTodo()
    {
        return $this->eliminaDatosTabla();
    }
    public function eliminarRegistro(array $campos)
    {
        return $this->eliminaPorQuery($campos);
    }


    private function nuevoRegistro($campos)
    {
        try {
            $consulta = "";
            if (!empty($this->tablaBase)) {
                $this->totalCampos = count($campos);
                if (!empty($this->queryPersonalizado)) {
                    $consulta = $this->queryPersonalizado;
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
                        return $this->regresarId ? $this->id : true;
                    }
                    $resultado->closeCursor();
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
                    return true;
                }
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
                    return true;
                }
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
                    return true;
                }
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
}
