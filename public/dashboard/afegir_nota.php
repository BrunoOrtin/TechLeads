<?php

use App\Model\Nota;

require_once __DIR__ . "/../../config/bootstrap.php";
requereixLogin();

if ($_SERVER["REQUEST_METHOD"]  !== "POST") {
    header("Location: leads.php");
    exit;
}
// recogemos los errores 

$errors = [];

/**recogida de datos */
$lead_id = $_POST["lead_id"] ?? "";
$texto = trim($_POST["text"] ?? "");
$usuariActual = $usuariDAO -> findByUsuari($_SESSION["usuari"]);
$usuariId = $usuariActual->getId();

//creamos una nueva nota
$nota = new Nota(
    text: $texto,
    usuari_id: $usuariId,
    lead_id: $lead_id
);

// comprobamos y la creamos como int
if (!is_string($lead_id) || !ctype_digit($lead_id) || (int) $lead_id <= 0) {
    header('Location: leads.php');
    exit;
    }
    
    $lead_id = (int) $lead_id;
    
    
    $lead = $leadDAO->findById($lead_id);
    if ($lead === null) {
        header('Location: leads.php');
        exit;
        }
        
// guardamos la nota en la base de datos
  
$notaDAO -> insert($nota);
header("Location: lead.php?id=" . $lead_id);


$texto = trim($_POST["text"] ?? "");

/*validacion notas */
if (empty($texto)){

    $_SESSION["errors_nota"] = "la nota no puede estar vacia";
    $_SESSION["old_nota"] = $texto;


    header("Location: lead.php?id=" . $lead_id);
    exit;

}

