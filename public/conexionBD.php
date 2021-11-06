<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexion a BD</title>
</head>
<body>
    <h1>Ejemplo de conexión a la base de datos</h1>

    <?php
        $host = 'mysql';    //el nombre del contenedor de docker donde esta la base de datos
        $dbname = 'laravel0';
        $user = 'default';
        $password = 'secret';

        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;
        $opciones = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try{
            $conexion = new PDO($dsn, $user, $password, $opciones);
            echo 'Correcto <br> Estamos conectados a la base de datos';
            print_r($conexion);
        } catch(PDOException $e){
            echo 'Error en la conexion' . $e->getMessage();
        }
    ?>
</body>
</html>