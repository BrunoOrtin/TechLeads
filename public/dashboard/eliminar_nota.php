<?php

require_once __DIR__ . "/../../config/bootstrap.php";

requereixLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("location: leads.php");
    exit;
}

// tomamos los datos del post de lead

$nota_id = $_POST["nota_id"] ?? "";
$lead_id = $_POST["lead_id"] ?? "";


if (!ctype_digit($nota_id) || (int) $nota_id <= 0) {
    header('Location: leads.php');
    exit;
}

$nota_id = (int) $nota_id; // pasamos el valor a int para la clase

$nota = $notaDAO->findById($nota_id); // entontramos la nota 

if ($nota === null) {
    header('Location: leads.php');
    exit;
}

// identificamos que el usuario conectad de ssesion

$usuariNow = $usuariDAO->findByUsuari($_SESSION["usuari"]);

$usuariRol = $usuariNow->getRol();
$usuariId = $usuariNow->getId();

if ($usuariRol !== "administrador" && $nota->getUsuariId() !== $usuariId) {
    header("location: lead.php?id=" . $lead_id);
    exit;
}

$notaDAO->delete($nota_id);

header("location: lead.php?id=" . $lead_id);
exit;
