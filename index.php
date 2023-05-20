<?php

require 'flight/Flight.php';
require 'enumeradores.php';

$app = Flight::app();

// Registrar la base de datos
$app->register('db', 'PDO', ['mysql:host=localhost;dbname=api', 'root', '']);

// Filtro para configurar encabezados de seguridad
$app->before('start', function () {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
});

// Obtener todos los alumnos
$app->route('GET /alumnos', function () use ($app) {
    try {
        $sql = "SELECT * FROM alumno";
        $stmt = $app->db()->query($sql);
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $app->json($alumnos,HttpStatusCodes::OK);
    } catch (PDOException $e) {
        $error = [
            'error' => 'Error al obtener los alumnos',
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
        $app->halt(HttpStatusCodes::INTERNAL_SERVER_ERROR, json_encode($error));
    }
});

// Obtener alumno por ID
$app->route('GET /alumnos/@id', function ($id) use ($app) {
    try {
        $sql = "SELECT * FROM alumnos WHERE id=?";
        $stmt = $app->db()->prepare($sql);
        $stmt->execute([$id]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($alumno) {
            $app->json($alumno,HttpStatusCodes::OK);
        } else {
            $app->json(["error" => "No se encontró ningún alumno con el ID especificado"], HttpStatusCodes::NOT_FOUND);
        }
    } catch (PDOException $e) {
        $error = [
            'error' => 'Error al obtener el alumno',
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
       $app->halt(HttpStatusCodes::INTERNAL_SERVER_ERROR, json_encode($error));
    }
});

// Insertar alumno
$app->route('POST /alumnos', function () use ($app) {
    try {
        $nombre = $app->request()->data->nombre;
        $apellidos = $app->request()->data->apellidos;

        $sql = "INSERT INTO alumnos (nombre, apellidos) VALUES (?, ?)";
        $stmt = $app->db()->prepare($sql);
        $stmt->execute([$nombre, $apellidos]);

        $app->json(["message" => "Alumno agregado"],HttpStatusCodes::OK);
    } catch (PDOException $e) {
        $error = [
            'error' => 'Error al insertar el alumno',
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
        $app->halt(HttpStatusCodes::INTERNAL_SERVER_ERROR, json_encode($error));
    }
});

// Borrar alumno
$app->route('DELETE /alumnos/@id', function ($id) use ($app) {
    try {
        $sql = "DELETE FROM alumnos WHERE id=?";
        $stmt = $app->db()->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $app->json(["message" => "Alumno borrado"],HttpStatusCodes::OK);
        } else {
            $app->json(["error" => "No se encontró ningún alumno con el ID especificado"], HttpStatusCodes::NOT_FOUND);
        }
    } catch (PDOException $e) {
        $error = [
            'error' => 'Error al borrar el alumno',
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
         $app->halt(HttpStatusCodes::INTERNAL_SERVER_ERROR, json_encode($error));
    }
});

// Actualizar alumno
$app->route('PUT /alumnos/@id', function ($id) use ($app) {
    try {
        $nombre = $app->request()->data->nombre;
        $apellidos = $app->request()->data->apellidos;

        $sql = "UPDATE alumnos SET nombre=?, apellidos=? WHERE id=?";
        $stmt = $app->db()->prepare($sql);
        $stmt->execute([$nombre, $apellidos, $id]);

        if ($stmt->rowCount() > 0) {
            $app->json(["message" => "Alumno actualizado"],HttpStatusCodes::OK);
        } else {
            $app->json(["error" => "No se encontró ningún alumno con el ID especificado"], HttpStatusCodes::NOT_FOUND);
        }
    } catch (PDOException $e) {
        $error = [
            'error' => 'Error al actualizar el alumno',
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
        $app->halt(HttpStatusCodes::INTERNAL_SERVER_ERROR, json_encode($error));
    }
});

$app->start();


/**
    INFO

// Diferencias entre prepare() y query():

// prepare():
// - Se utiliza para preparar una consulta SQL con parámetros variables.
// - Permite reutilizar la consulta preparada con diferentes valores de parámetros.
// - Proporciona una capa adicional de seguridad al evitar ataques de inyección de SQL.
// - Se recomienda su uso cuando se espera ejecutar la misma consulta varias veces con diferentes valores de parámetros.
// - Requiere asociar los valores de los parámetros utilizando bindParam() o bindValue() antes de ejecutar la consulta con execute().

// query():
// - Se utiliza para ejecutar una consulta SQL directamente sin prepararla previamente.
// - Es útil cuando la consulta no requiere parámetros variables y puede ejecutarse de forma inmediata.
// - No proporciona la misma capa de seguridad que prepare() y puede ser vulnerable a ataques de inyección de SQL si se incluyen datos no confiables directamente en la consulta.
// - Es más simple de usar y puede ser adecuado para consultas rápidas y sencillas sin complicaciones adicionales.


Es importante tener en cuenta que el uso de :: y -> depende de cómo se hayan definido los métodos y propiedades en la clase. 
Si un método o propiedad es estático, se debe usar :: para acceder a él. 
Si un método o propiedad es de instancia, se debe usar -> para acceder a él en el contexto de un objeto específico.


   (Operador de resolución de ámbito estático):

   Se utiliza para acceder a métodos y propiedades estáticas de una clase sin necesidad de crear una instancia de la clase.
   Permite acceder a elementos que son compartidos por todas las instancias de la clase.
   Se utiliza cuando los métodos o propiedades son declarados como estáticos en la clase.
   Ejemplo: MiClase::metodoEstatico();

   -> (Operador de acceso a miembros de objeto):

   Se utiliza para acceder a métodos y propiedades de una instancia específica de una clase.
   Requiere crear una instancia (objeto) de la clase antes de poder acceder a sus métodos y propiedades.
   Se utiliza cuando los métodos o propiedades son de instancia, es decir, específicos de cada objeto.
   Ejemplo: $objeto->metodoDeInstancia();

PDO::FETCH_ASSOC

// Supongamos que tenemos una fila de una consulta con las siguientes columnas:
// id: 1
// nombre: John
// apellidos: Doe

$row = $stmt->fetch(PDO::FETCH_ASSOC);

// $row contendrá el siguiente arreglo asociativo:
// [
//     "id" => 1,
//     "nombre" => "John",
//     "apellidos" => "Doe"
// ]

halt()

La función halt() en el contexto del framework Flight de PHP se utiliza para detener la ejecución de la aplicación 
y enviar una respuesta HTTP con un código de estado y un cuerpo de respuesta personalizados.

La función halt() tiene la siguiente sintaxis:

Flight::halt($statusCode, $body);

$statusCode es el código de estado HTTP que se enviará en la respuesta.
$body es el cuerpo de la respuesta, que puede ser una cadena de texto 
o un arreglo asociativo que se convierte automáticamente a JSON.
Cuando se llama a halt(), la ejecución del código se interrumpe 
y se envía una respuesta HTTP con el código de estado y el cuerpo especificados. 
Esto puede ser útil para manejar errores y enviar mensajes de error personalizados
en lugar de mostrar una página en blanco o una respuesta genérica.

 */
