<?php
/*
    RELLENAR LOS CAMPOS PARA CONECTARSE A UN GESTOR DE BASE DE DATOS.
    ACTUALMENTE SE PUEDE CONECTAR CON:
         -POSTGRESQL
         -MARIADB
    NOTA: IMPORTANTE EN CASO DE USAR XAMPP VERIFICAR 
          SI EL DRIVER DEL GESTOR DE BASE DE DATOS 
          ESTA HABILITADO.
          PUEDE HACER UN TEST EJECUTANDO ESTE SCRIPT: 
                    echo json_encode(PDO::getAvailableDrivers());
         
          DEBERIA VER EN PANTALLA: 
                    ["mysql","pgsql","sqlite"]

          POR DEFECTO EL DRIVER DE MARIADB VIENE ACTIVO, SI EL DE
          POSTGRESQL NO APARECE UBIQUE EL ARCHIVO php.ini UBICADO
          EN LA CARPETA DONDE SE INSTALO XAMPP GENERALMENTE EN "C:\xampp\php\php.ini"
          Y DESCOMENTE LOS PUNTO Y COMA DE LAS SIGUIENTES LINEAS: 
             ;extension=pdo_pgsql
             ;extension=pgsql
        DEJANDO DE LA SIGUIENTE MANERA: 
             extension=pdo_pgsql
             extension=pgsql
        GUARDE LOS CAMBIOS, REINICIE EL SERVIDOR Y EJECUTE NUEVAMENTE EL SCRIPT DE TEST
*/


//-------INGRESE LOS SIGUIENTES CAMPOS DEPENDIENDO DEL GESTOR A CONECTAR---------


//GESTOR COLOQUE: 1: POSTGRESQL O 2: MARIADB
define('GESTOR', 2);

//SI UD SELECCIONO POSTGRESQL ES OBLIGACION COLOCAR EL PUERTO DONDE
// SE ENCUENTRA EL GESTOR, SI NO DEJAR POR DEFECTO EL VALOR DE ESTE CAMPO
define('PUERTO', 5432);



//EN AMBOS CASOS ES OBLIGATORIO QUE RELLENE ESTOS CAMPOS EN DONDE ESTA ''

//EN DONDE SE ENCUENTRA LA BASE DE DATOS 
define('SERVIDOR', 'localhost');

//EL NOMBRE DE LA BASE DE DATOS QUE DESEA CONECTAR
define('NOMBRE_BD', 'nombreBase');

//EL USUARIO DEL GESTOS DE BASE DE DATOS
define('USUARIO', 'yourUser');

//CONTRASEÑA DEL GESTOR PARA ACCEDER
define('PASSWORD', 'yourPassword');
