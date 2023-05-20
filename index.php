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
        Flight::json(["error" => "Error al obtener los alumnos: " . $e->getMessage()], 500);
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
