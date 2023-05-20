<?php

require 'flight/Flight.php';


Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=api', 'root', ''));


// Filtro para configurar encabezados de seguridad
Flight::before('start', function () { // estable un filtro antes de que la aplicacion se ejecute
    header("Content-Type: application/json"); // Solo respuestas tipo JSON
    header("Access-Control-Allow-Origin: *"); // Permitir solicitudes de cualquier origen
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // metodos permitidos
    header("Access-Control-Allow-Headers: Content-Type"); //permitir solicitudes que incluyan datos en JSON
});


// Obtener todos los alumnos
Flight::route('GET /alumnos', function () {
    try {
        $sql = "SELECT * FROM alumnos";
        $stmt = Flight::db()->query($sql); // ejecutar la consulta
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC); // mostrar solo los datos sin indices de las columnas
        Flight::json($alumnos);
    } catch (PDOException $e) {
        // consulta invalida
        Flight::json(["error" => "Error al obtener los alumnos: " . $e->getTraceAsString()], 500);
    }
});

// Obtener alumno por ID
Flight::route('GET /alumnos/@id', function ($id) {
    try {
        $sql = "SELECT * FROM alumnos WHERE id=?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($alumno) {
            Flight::json($alumno);
        } else {
            Flight::json(["error" => "No se encontró ningún alumno con el ID especificado"], 404);
        }
    } catch (PDOException $e) {
        Flight::json(["error" => "Error al obtener el alumno: " . $e->getMessage()], 500);
    }
});

// Insertar alumno
Flight::route('POST /alumnos', function () {
    try {
        $request = Flight::request();
        $nombre = $request->data->nombre;
        $apellidos = $request->data->apellidos;

        $sql = "INSERT INTO alumnos (nombre, apellidos) VALUES (?, ?)";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$nombre, $apellidos]);

        Flight::json(["message" => "Alumno agregado"]);
    } catch (PDOException $e) {
        Flight::json(["error" => "Error al insertar el alumno: " . $e->getMessage()], 500);
    }
});

// Borrar alumno
Flight::route('DELETE /alumnos/@id', function ($id) {
    try {
        $sql = "DELETE FROM alumnos WHERE id=?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            Flight::json(["message" => "Alumno borrado"]);
        } else {
            Flight::json(["error" => "No se encontró ningún alumno con el ID especificado"], 404);
        }
    } catch (PDOException $e) {
        Flight::json(["error" => "Error al borrar el alumno: " . $e->getMessage()], 500);
    }
});

// Actualizar alumno
Flight::route('PUT /alumnos/@id', function ($id) {
    try {
        $request = Flight::request();
        $nombre = $request->data->nombre;
        $apellidos = $request->data->apellidos;

        $sql = "UPDATE alumnos SET nombre=?, apellidos=? WHERE id=?";
        $stmt = Flight::db()->prepare($sql);
        $stmt->execute([$nombre, $apellidos, $id]);

        if ($stmt->rowCount() > 0) {
            Flight::json(["message" => "Alumno actualizado"]);
        } else {
            Flight::json(["error" => "No se encontró ningún alumno con el ID especificado"], 404);
        }
    } catch (PDOException $e) {
        Flight::json(["error" => "Error al actualizar el alumno: " . $e->getMessage()], 500);
    }
});

Flight::start();

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



 */
