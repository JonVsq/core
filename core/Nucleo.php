<?php
require('Conexion.php');
class Nucleo
{
    private $conexion;
    private $queryPersonalizado;
    public function __construct()
    {
        $obj = new Conexion();
        $this->conexion = $obj->conectar();
    }


    //-----------------------------------------------------------------------------------------------------
    //SECCION DE INSERCION
    public function insertarRegistro($tablaBase, array $campos)
    {
        try {
            $consulta = "";
            if (!empty($this->queryPersonalizado)) {
                $consulta = $this->queryPersonalizado;
            } else {
                if (GESTOR == 1) {
                    $consulta = $this->getInToPSQL($tablaBase);
                } else if (GESTOR == 2) {
                    $consulta = $this->getInToMariaDB($tablaBase);
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
            return null;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //OBTIENE EL SQL INSERT IN TO SI LA TABLA PERTENECE A POSTGRESQL
    private function getInToPSQL($tablaBase)
    {
        $consulta = "SELECT column_name FROM information_schema.columns WHERE 
                            table_schema = 'public' AND table_name = '$tablaBase'";
        try {
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
            return null;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //OBTIENE EL SQL INSERT IN TO SI LA TABLA PERTENECE A MARIADB
    private function getInToMariaDB($tablaBase)
    {
        $consulta = "show columns from $tablaBase";
        try {
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
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
    //-----------------------------------------------------------------------------------------------------

    //SECCION DE LISTAR (TODOS LOS CAMPOS)
    public function getDatos($tablaBase, $ordenar)
    {
        try {
            $consulta = "";
            if (!empty($this->queryPersonalizado)) {
                $consulta = $this->queryPersonalizado;
            } else {
                if (!empty($ordenar)) {
                    $consulta = "SELECT * FROM $tablaBase order by $ordenar;";
                } else {
                    $consulta = "SELECT * FROM $tablaBase";
                }
            }
            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();
            $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $resultado->closeCursor();
            if ($datos) {
                return $datos;
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
}
