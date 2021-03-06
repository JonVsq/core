# MINI CORE CRUD PHP

BIENVENIDO A ESTE PEQUEÑO PROYECTO LA IDEA ES MEJORARLO POCO A POCO Y QUE AYUDE A AGILIZAR LA PROGRAMACION, EVITANDO HACER PASOS REPETITIVOS Y AHORRANDO UN PAR DE LINEAS EN LA OPERACIONES QUE YA CONOCEMOS, SOLO DEBES INCLUIR LA CARPETA CORE EN TU PROYECTO Y A UTILIZARLA.

**CONFIGURACION**

LA PRIMERA CONFIGURACION QUE DEBE HACERSE ES EN EL ARCHIVO
"DatosConexion.php", AHI DEBES INGRESAR LOS DATOS NECESARIOS
PARA CONECTARSE A UN GESTOR DE BASE DE DATOS.

ACTUALMENTE SOLO ES POSIBLE CONECTARSE A POSTGRESQL Y MARIADB(MYSQL).

**EJEMPLO DE CONFIGURACION POSTGRESQL.**

```php
define('GESTOR', 1);

define('PUERTO', 5432);

define('SERVIDOR', 'localhost');

define('NOMBRE_BD', 'miBasedatosPostgres');

define('USUARIO', 'yourUser');

define('PASSWORD', 'yourPassword');
```

**EJEMPLO DE CONFIGURACION MARIADB(MYSQL).**

```php
define('GESTOR', 2);

define('PUERTO', 5432);

define('SERVIDOR', 'localhost');

define('NOMBRE_BD', 'miBasedatosMariaDB');

define('USUARIO', 'yourUser');

define('PASSWORD', 'yourPassword');
```

_NOTA: ENCONTRARAS MAS INFORMACION SOBRE ESTOS CAMPOS EN "DatosConexion.php"._

# PRIMER INSERT: insertarRegistro().

_ESTE METODO RETORNA:_

_TRUE SI SE REALIZO LA INSERCION._

_FALSE SI NO SE REALIZO LA INSERCION._

_VALOR ENTERO SI SE CONFIGURO PARA REGRESAR EL ID EN TABLAS CON ID AUTOINCREMENTABLE O SERIAL._

## 1. INCLUIR EL ARCHIVO "Nucleo.php" EN TU CONTROLADOR O CLASE DONDE PROCEDERAS A CAPTURAR LOS DATOS:

```php
require('Nucleo.php');
```

## 2. CREA UN OBJETO DE ESTA CLASE CUANDO LO VAYAS A UTILIZAR:

```php
$nucleo = new Nucleo();
```

## 3. PARA INSERTAR ES MUY FACIL, SIMPLEMENTE DEBES ASIGNAR EL NOMBRE DE LA TABLA EN TU BASE DE DATOS DONDE SE GUARDARAN LOS DATOS Y LLAMAR AL METODO "insertarRegistro()" EL CUAL RECIBE 1 PARAMETRO,UN ARRAY CON LOS VALORES QUE QUIERES INGRESAR, RECUERDA QUE ESTA FUNCION RETORNA TRUE SI LA TRANSACCION TUVO EXITO, RETORNA FALSE SI NO SE PUDO REALIZAR LA INSERCION Y EN CASO DE QUE CONFIGURES EL METODO PARA TABLAS CON ID AUTOINCREMENTABLE O SERIAL REGRESARA UN VALOR ENTERO.

**EJEMPLOS**
_SUPONIENDO QUE SE TIENE UNA TABLA EN LA BASE DE DATOS LLAMADA "cargo" LA CUAL CONTIENE LOS SIGUIENTES CAMPOS "id,nombre,sueldo,temporal", BASTA CON SOLO ASIGNAR EL NOMBRE DE LA TABLA Y PASAR UN ARRAY CON LOS VALORES OBTENIDOS, YA SEA MEDIANTE UN FORMULARIO, JSON O CUALQUIER FUENTE RESPETANDO EL ORDEN Y EL TIPO DE LOS CAMPOS EN LA BASE DE DATOS SEGUN ESTE EJEMPLO LA INSERCION SERIA:_

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
if ($nucleo->insertarRegistro(array(15, "DOCENTE", 675, true))) {
     echo 'REGISTRO ALMACENADO';
  } else {
         echo 'REGISTRO NO ALMACENADO';
  }
    $nucleo = null;
```

_NOTA: ESTA FORMA SOLO SE RECOMIENDA CUANDO INGRESARAS TODOS LOS CAMPOS INCLUIDO LA LLAVE PRINCIPAL, DE NO SER ASI SE RECOMIENDA USAR UNA QUERY PERSONALIZADA O HABILITAR LA OPCION DE RETORNAR EL ID EN CASO DE TABLAS CON ID AUTOINCREMENTABLE O SERIAL._

## 4. QUE PASA SI DESEO INSERTAR UNA INSTRUCCION SQL PERSONALIZADA?

EL PEQUEÑO CORE CUENTA CON UNA VARIABLE LLAMADA "queryPersonalizado" EN LA CUAL PUEDES SIN NINGUN PROBLEMA ESCRIBIR TU PROPIA INSTRUCCION SQL, CLARO SIN ERRORES DE SINTAXIS LO DEMAS EL PEQUEÑO CORE LO HACE.

## 5. COMO ACCEDO A LA VARIABLE "queryPersonalizado"?

A TRAVES DE SU METODO SET, RETOMANDO EL EJEMPLO ANTERIOR AHORA SE HARA DE MANERA QUE NO DESEEMOS INGRESAR EL "id" O "llave principal" DE LA TABLA "cargo", POSTERIORMENTE LLAMANDO AL METODO "insertarRegistro()" Y DE IGUAL MANERA PASAR UN ARRAY CON LOS CAMPOS QUE DESEAS INGRESAR, RETOMANDO EL CASO ANTERIOR EL ID ES AUTO INCREMENTABLE, ES DECIR NO HAY NECESIDAD DE INCLUIRLO:

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
$nucleo->setQueryPersonalizado('nombre,sueldo,temporal');
if ($nucleo->insertarRegistro(array("MECANICO", 800, true))) {
    echo 'REGISTRO ALMACENADO';
} else {
    echo 'REGISTRO NO ALMACENADO';
}
$nucleo = null;
```

_NOTA: EN EL CASO DE QUE NO DESEES INGRESAR MAS DE DOS CAMPOS, UTILIZA LA VARIABLE "queryPersonalizado" Y SOLAMENTE ESCRIBE LOS CAMPOS QUE DESEAS INGRESAR SEPARADOS POR "," SIEMPRE Y CUANDO NO AFECTEN EN LA EJECUCION DE LA CONSULTA SQL._

## 6. SI LA LLAVE PRINCIPAL DE LA TABLA DONDE GUARDARAS UN REGISTRO ES AUTOINCREMENTABLE EN CASO DE MARIADB O SERIAL EN CASO DE POSTGRESQL, NO ES NECESARIO QUE INSERTES UN ID Y EL REGISTRO ES SIMILAR SOLO NECESITAS INDICAR EN QUE TABLA Y ENVIAR LOS VALORES POR ARRAY.

**NOTA: SI ES IMPORTANTE QUE LA COLUMNA DEL ID SEA LA PRIMERA COLUMNA EN TUS TABLAS DE LA BASE DE DATOS PARA QUE EL INSERT SIN LA QUERY PERSONALIZADA FUNCIONE**

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
if ($nucleo->insertarRegistro(array("DOCENTE", 675, true))) {
    echo 'REGISTRO ALMACENADO';
} else {
    echo 'REGISTRO NO ALMACENADO';
}
$nucleo = null;
```

## 7. SI TU INSERCION FUE HACIA UNA TABLA CON ID AUTOINCREMENTABLE O SERIAL Y NECESITAS SABER QUE ID LE FUE ASIGNADO BASTA CON ASIGNAR TRUE A LA VARIABLE "regresarId" A TRAVES DE SU METODO SET, ESTO ES MUY UTIL CUANDO SE REALIZAN REGISTROS EN TABLAS RELACIONALES:

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
$nucleo->setRegresarId(true);
$id = $nucleo->insertarRegistro(array("DOCTOR", 890, true));
if (is_numeric($id)) {
    echo $id;
} else {
    echo 'REGISTRO NO ALMACENADO';
}
$nucleo = null;
```

Y ASI DE FACIL SE HACE UNA INSERCION CON ESTE MINI CORE

# OBTENER REGISTROS DE LA BASE DE DATOS: getDatos().

_ESTE METODO REGRESA UN ARRAY ASOCIATIVO CON LA INFORMACION SOLICITADA._

## 1. PARA OBTENER LOS REGISTROS BASTA CON SETEAR EL NOMBRE DE LA TABLA EN DONDE SE HARA LA CONSULTA, RETORNA UN ARRAY ASOCIATIVO CON LA INFORMACION DE LA TABLA Y RETORNA NULL SI LA TABLA NO SE ENCUENTRA O NO HA SIDO SETEADA:

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
$datos = $nucleo->getDatos();
var_dump($datos);
$nucleo = null;
```

## 2. DE IGUAL MANERA PUEDES HACER CONSULTAS CON UNA QUERY PERSONALIZADA:

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('cargo');
$nucleo->setQueryPersonalizado('SELECT c.nombre FROM cargo as c');
$datos = $nucleo->getDatos();
var_dump($datos);
$nucleo = null;
```

# ELIMINAR REGISTROS DE UNA TABLA EN LA BASE DE DATOS: eliminarTodo()

## 1. BASTA CON SOLO INDICAR LA TABLA A LA QUE DESEAMOS BORRAR TODOS LOS REGISTROS Y ESPERAR LA RESPUESTA.

_NOTA: ESTA FUNCION ELIMINA TODOS LOS REGISTROS DE UNA TABLA EN LA BASE DE DATOS RETORNA TRUE SI SE REALIZO LA OPERACION Y FALSE SI NO SE COMPLETO, RECUERDA ESTA ACCION NO SE PUEDE DESHACER. ES UTIL CUANDO EN TABLAS DE MOVIMIENTOS A LAS QUE SE LES REALIZA BACKUP DEBEN LIMPIARSE._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
if ($nucleo->eliminarTodo()) {
    echo 'REGISTROS ELIMINADOS';;
} else {
    echo 'REGISTROS NO ELIMINADOS';
}
$nucleo = null;
```

# ELIMINAR REGISTROS DE UNA TABLA EN LA BASE DE DATOS CON QUERY PERSONALIZADO: eliminarRegistro()**

## 1. PARA ELIMINAR REGISTROS EN LA MANERA QUE MEJOR CONVENGA PARA TU APLICACION, SOLO DEBES ESCRIBIR TU SQL EN LA VARIABLE "queryPersonalizado" A TRAVES DE SU METODO SET Y ENVIAR LOS PARAMETROS A LA FUNCION "eliminarRegistro()" MEDIANTE UN ARRAY.

_VEAMOS UN PEQUEÑO EJEMPLO EN DONDE DESEAMOS ELIMINAR UN REGISTRO DE LA TABLA "viajes" SIEMPRE Y CUANDO SU ID SEA IGUAL A 1._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
$nucleo->setQueryPersonalizado('WHERE id = ?;');
if ($nucleo->eliminarRegistro(array(1))) {
    echo 'REGISTRO ELIMINADO';;
} else {
    echo 'REGISTRO NO ELIMINADO';
}
$nucleo = null;
```

_AHORA VEAMOS UN EJEMPLO EN DONDE LA ELIMINACION ES UN POCO MAS COMPLEJA._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
$nucleo->setQueryPersonalizado('WHERE id = ? AND fecha = ? AND activo = ?;');
if ($nucleo->eliminarRegistro(array(3, '2020-11-02', true))) {
    echo 'REGISTRO ELIMINADO';;
} else {
    echo 'REGISTRO NO ELIMINADO';
}
$nucleo = null;
```

_NOTA: RECUERDA ESCRIBIR TU SQL A PARTIR DE LA CONDICION WHERE YA QUE EL METODO INCLUIRA LA INSTRUCCION "DELETE FROM "NombreTabla"._

# MODIFICACION DE REGISTROS: modificarRegistro()

## 1. SI ACTUALIZAS UN REGISTRO SOLO POR LA CONDICION DE QUE EL ID QUE ESTA EN LA BASE DE DATOS SEA IGUAL AL ID QUE DESEAS MODIFICAR ENTONCES SOLO BASTA CON SOLO INDICAR A QUE TABLA SE DESEA HACER UNA MODIFICACION, LLAMAR AL METODO Y PASARLE UN ARRAY CON TODOS LOS CAMPOS, RESPETANDO EL ORDEN EN EL QUE SE ENCUENTRAN EN LA BASE DE DATOS.

_NOTA: ES IMPORTANTE QUE LA LLAVE PRINCIPAL ES DECIR LA PRIMARY KEY DE TU TABLA SEA LA PRIMER COLUMNA DE LA TABLA PARA QUE LA MODIFICACION FUNCIONE, PARA ESTE CASO EL ID DEL REGISTRO DE EJEMPLO ES 23 Y ES EL UNICO CAMPO QUE NO SUFRE AFECTACION ALGUNA SOLO ES UTILIZADO PARA ENCONTRAR EL REGISTRO._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
if ($nucleo->modificarRegistro(array(23, 2, 3, '2019-01-12', 0))) {
    echo 'REGISTRO MODIFICADO';
} else {
    echo 'REGISTRO NO MODIFICADO';
}
$nucleo = null;
```

## 2. SI ACTUALIZAS REGISTROS CON CONDICIONES MAS ESTRICTAS PUEDES PERFECTAMENTE USAR LA VARIABLE "queryPersonalizado" Y CREAR EL SQL MAS IDONEO PARA LA CONDICION QUE NECESITES.

_NOTA: SOLO DEBES INGRESAR EL SQL A PARTIR DE LOS CAMPOS QUE MODIFICARAS, EL ARRAY QUE ENVIES AL METODO DEBE CUMPLIR CON EL ORDEN DE LOS PARAMETROS DECLARADOS, NOTESE COMO LOS CAMPOS DEL ARRAY CUMPLEN CON LA LOGICA DE LOS PARAMETROS EN LA SQL._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
$nucleo->setQueryPersonalizado('id_tecnico = ?, id_vehiculo = ?, fecha = ?,
                                activo = ? WHERE id = ?;');
if ($nucleo->modificarRegistro(array(3, 3, '2016-02-12', 0, 23))) {
    echo 'REGISTRO MODIFICADO';
} else {
    echo 'REGISTRO NO MODIFICADO';
}
$nucleo = null;
```

_ACA TIENES OTRO EJEMPLO._

```php
$nucleo = new Nucleo();
$nucleo->setTablaBase('viajes');
$nucleo->setQueryPersonalizado('id_tecnico = ?, id_vehiculo = ? WHERE id = ?
                                AND activo = false;');
if ($nucleo->modificarRegistro(array(1, 2, 23))) {
    echo 'REGISTRO MODIFICADO';
} else {
    echo 'REGISTRO NO MODIFICADO';
}
$nucleo = null;
```
