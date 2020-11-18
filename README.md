#MINI CORE CRUD PHP

BIENVENIDO A ESTE PEQUEÑO PROYECTO
LA IDEA ES MEJORARLO POCO A POCO Y QUE AYUDE A AGILIZAR
LA PROGRAMACION, EVITANDO HACER PASOS REPETITIVOS Y AHORRANDO
UN PAR DE LINEAS EN LA OPERACIONES QUE YA CONOCEMOS.

----------------------CONFIGURACION -----------------

LA PRIMERA CONFIGURACION QUE DEBE HACERSE ES EN EL ARCHIVO
"DatosConexion.php", AHI DEBES INGRESAR LOS DATOS NECESARIOS
PARA CONECTARSE A UN GESTOR DE BASE DE DATOS.

ACTUALMENTE SOLO ES POSIBLE CONECTARSE A POSTGRESQL Y MARIADB(MYSQL).

---------------EJEMPLO DE CONFIGURACION POSTGRESQL.------------------

define('GESTOR', 1);

define('PUERTO', 5432);

define('SERVIDOR', 'localhost');

define('NOMBRE_BD', 'miBasedatosPostgres');

define('USUARIO', 'yourUser');

define('PASSWORD', 'yourPassword');

---------------EJEMPLO DE CONFIGURACION MARIADB(MYSQL).------------------

define('GESTOR', 2);

define('PUERTO', 5432);

define('SERVIDOR', 'localhost');

define('NOMBRE_BD', 'miBasedatosMariaDB');

define('USUARIO', 'root');

define('PASSWORD', 'myPassword');

NOTA: ENCONTRARAS MAS INFORMACION SOBRE ESTOS CAMPOS EN "DatosConexion.php".

---------------PRIMER INSERT.------------------

1. INCLUIR EL ARCHIVO "Nucleo.php" EN TU CONTROLADOR O CLASE DONDE
   PROCEDERAS A CAPTURAR LOS DATOS:
        include('Nucleo.php');

2. CREA UN OBJETO DE ESTA CLASE CUANDO LO VAYAS A UTILIZAR:
        $nucleo = new Nucleo();

3. PARA INSERTAR ES MUY FACIL, SIMPLEMENTE DEBES ASIGNAR EL NOMBRE DE LA
   TABLA EN TU BASE DE DATOS DONDE SE GUARDARAN LOS DATOS Y LLAMAR AL METODO
   "insertarRegistro()" EL CUAL RECIBE 1 PARAMETRO,UN ARRAY CON LOS VALORES QUE QUIERES INGRESAR, RECUERDA QUE ESTA FUNCION RETORNA TRUE SI LA TRANSACCION TUVO EXITO Y RETORNA FALSE SI NO SE PUDO REALIZAR LA INSERCION.

EJEMPLO:
SUPONIENDO QUE SE TIENE UNA TABLA EN LA BASE DE DATOS LLAMADA "cargo" LA CUAL CONTIENE LOS SIGUIENTES CAMPOS "id,nombre,sueldo,temporal", BASTA CON
SOLO ASIGNAR EL NOMBRE DE LA TABLA Y PASAR UN ARRAY CON LOS VALORES OBTENIDOS YA SEA MEDIANTE UN FORMULARIO, JSON O CUALQUIER FUENTE RESPETANDO EL ORDEN Y EL TIPO DE LOS CAMPOS EN LA BASE DE DATOS SEGUN ESTE EJEMPLO:

        $nucleo = new Nucleo();
        $nucleo->setTablaBase('cargo');
        if ($nucleo->insertarRegistro(array(1, "DOCENTE", 675, true))) {
           echo 'REGISTRO ALMACENADO';
        } else {
           echo 'REGISTRO NO ALMACENADO';
        }
        $nucleo = null;

NOTA: ESTA FORMA SOLO SE RECOMIENDA CUANDO INGRESARAS TODOS LOS CAMPOS INCLUIDO LA LLAVE PRINCIPAL, DE NO SER ASI SE RECOMIENDA USAR UNA QUERY PERSONALIZADA.

4. QUE PASA SI DESEO INSERTAR UNA INSTRUCCION SQL PERSONALIZADA?
   EL PEQUEÑO CORE CUENTA CON UNA VARIABLE LLAMADA "queryPersonalizado" EN LA CUAL PUEDES SIN NINGUN PROBLEMA ESCRIBIR TU PROPIA INSTRUCCION SQL, CLARO SIN ERRORES DE SINTAXIS LO DEMAS EL PEQUEÑO CORE LO HACE.

5. COMO ACCEDO A LA VARIABLE "queryPersonalizado"?
   A TRAVES DE SU METODO SET, RETOMANDO EL EJEMPLO ANTERIOR AHORA SE HARA DE MANERA QUE NO DESEEMOS INGRESAR EL "id" O "llave principal" DE LA TABLA "cargo" Y POSTERIORMENTE LLAMANDO AL METODO "insertarRegistro()" Y DE IGUAL MANERA PASAR
   UN ARRAY CON LOS CAMPOS QUE DESEAS INGRESAR, RETOMANDO EL CASO ANTERIOR EL ID ES AUTO INCREMENTABLE, ES DECIR NO HAY NECESIDAD DE INCLUIRLO:

        $nucleo = new Nucleo();
        $nucleo->setTablaBase('cargo');
        $nucleo->setQueryPersonalizado('INSERT INTO cargo(nombre,sueldo,temporal) VALUES(?,?,?);');
        if ($nucleo->insertarRegistro(array("DOCENTE", 675, true))) {
            echo 'REGISTRO ALMACENADO';
        } else {
            echo 'REGISTRO NO ALMACENADO';
        }
        $nucleo = null;

Y ASI DE FACIL SE HACE UNA INSERCION CON ESTE MINI CORE

----------- OBTENER REGISTROS DE LA BASE DE DATOS --------------

1. PARA OBTENER LOS REGISTROS BASTA CON SOLO SETEAR EL NOMBRE DE LA TABLA EN DONDE SE HARA LA COSULTA, RETORNA UN ARRAY ASOCIATIVO CON LA INFORMACION DE LA TABLA Y RETORNA NULL SI LA TABLA NO SE ENCUENTRA O NO HA SIDO SETEADA:

        $nucleo = new Nucleo();
        $nucleo->setTablaBase('cargo');
        $datos = $nucleo->getDatos();
        var_dump($datos);
        $nucleo = null;

2. DE IGUAL MANERA PUEDES HACER CONSULTAS CON UNA QUERY PERSONALIZADA:

        $nucleo = new Nucleo();
        $nucleo->setTablaBase('cargo');
        $nucleo->setQueryPersonalizado('SELECT c.nombre FROM cargo as c');
        $datos = $nucleo->getDatos();
        var_dump($datos);
        $nucleo = null;