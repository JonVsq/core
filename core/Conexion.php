<?php
require('DatosConexion.php');
class Conexion
{
    //FUNCION CONECTAR
    public static function conectar()
    {
        $configurar = null;
        if (GESTOR == 2) {
            $configurar = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        }
        try {
            $conexion = null;
            if (GESTOR == 1) {
                $conexion = new PDO(
                    "pgsql:host=" . SERVIDOR . ";port=" . PUERTO . ";dbname=" . NOMBRE_BD . ';options=\'--client_encoding=UTF8\'',
                    USUARIO,
                    PASSWORD
                );
            } else if (GESTOR == 2) {
                $conexion = new PDO(
                    "mysql:host=" . SERVIDOR . "; dbname=" . NOMBRE_BD,
                    USUARIO,
                    PASSWORD,
                    $configurar
                );
            }
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            die("El error de la conexión fue: " . $e->getMessage());
        }
    }
}
