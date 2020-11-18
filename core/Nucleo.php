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


    //-----------------------------------------------------------------------------------------------------
    //SECCION DE INSERCION
    public function insertarRegistro(array $campos)
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
                        return "INSERT INTO cargo (" . implode(",", $salida) . ") VALUES (" . $parametros . ");";
                    }
                    $resultado->closeCursor();
                }
            }
            return "";
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //-----------------------------------------------------------------------------------------------------

    //SECCION DE LISTAR (TODOS LOS CAMPOS)
    public function getDatos()
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
