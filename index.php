<?php

require 'flight/Flight.php';

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=api', 'root', ''));

//GET ALL ALUMNOS
Flight::route('GET /alumnos', function () {
    $sql = "SELECT * FROM `alumnos`";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->execute();
    $datos = $sentencia->fetchAll();
    Flight::json($datos);
});
// GET ALUMNO BY ID
Flight::route('GET /alumnos/@id', function ($id) {

    $sql = "SELECT * FROM `alumnos` WHERE id=?";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $id);
    $sentencia->execute();
    $datos = $sentencia->fetchAll();
    Flight::json($datos);
});
// INSERTAR ALUMNOS
Flight::route('POST /alumnos', function () {
    $nombre = (Flight::request()->data->nombre);
    $apellidos = (Flight::request()->data->apellidos);

    $sql = "INSERT INTO alumnos (nombre,apellidos) VALUES (?,?)";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $nombre);
    $sentencia->bindParam(2, $apellidos);
    $sentencia->execute();

    Flight::jsonp(["Alumno agregado"]);
});

// BORRAR REGISTRO 
Flight::route('DELETE /alumnos', function () {
    $id = (Flight::request()->data->id);

    $sql = "DELETE FROM alumnos WHERE id=?";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $id);
    $sentencia->execute();

    Flight::jsonp(["Alumno borrado"]);
});

// ACTULIAZAR REGISTRO 
Flight::route('PUT /alumnos', function () {

    $id = (Flight::request()->data->id);
    $nombre = (Flight::request()->data->nombre);
    $apellidos = (Flight::request()->data->apellidos);

    $sql = "UPDATE alumnos SET nombre=?,apellidos=? WHERE id=?";
    $sentencia = Flight::db()->prepare($sql);
    $sentencia->bindParam(1, $nombre);
    $sentencia->bindParam(2, $apellidos);
    $sentencia->bindParam(3, $id);
    $sentencia->execute();

    Flight::jsonp(["Alumno actualizado"]);
});

Flight::start();
