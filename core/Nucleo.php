<?php
require('Conexion.php');
class Nucleo
{
    private $conexion;
    private $queryPersonalizado;
    private $tablaBase;
    private $ordenar;
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
                if (!empty($this->queryPersonalizado)) {
                    $consulta = $this->queryPersonalizado;
                } else {
                    if (GESTOR == 1) {
                        $consulta = $this->getInToPSQL($this->tablaBase);
                    } else if (GESTOR == 2) {
                        $consulta = $this->getInToMariaDB($$this->tablaBase);
                    }
                }
                if (!empty($consulta)) {
                    $resultado = $this->conexion->prepare($consulta);
                    $resultado->execute($campos);
                    if ($resultado->rowCount() > 0) {
                        $resultado->closeCursor();
                        return true;
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
    private function getInToPSQL($tablaBase)
    {
        try {
            if (!empty($this->tablaBase)) {
                $consulta = "SELECT column_name FROM information_schema.columns WHERE 
                table_schema = 'public' AND table_name = '$tablaBase'";
                $resultado = $this->conexion->prepare($consulta);
                if ($resultado->execute()) {
                    $salida = array();
                    $parametros = "";
                    while ($col = $resultado->fetch(PDO::FETCH_ASSOC)) {
                        $salida[] = $col['column_name'];
                        $parametros = $parametros . "? ,";
                    }
                    $resultado->closeCursor();
                    $parametros = substr($parametros, 0, -1);
                    return "INSERT INTO $tablaBase (" . implode(",", $salida) . ") VALUES (" . $parametros . ");";
                }
                $resultado->closeCursor();
            }
            return null;
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
                $resultado->execute();
                $salida = array();
                $parametros = "";
                while ($col = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    $salida[] = $col['Field'];
                    $parametros = $parametros . "? ,";
                }
                $resultado->closeCursor();
                $parametros = substr($parametros, 0, -1);
                return "INSERT INTO cargo (" . implode(",", $salida) . ") VALUES (" . $parametros . ");";
            }
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
}
