<?php
/**
 * VerificarCorreo.php
 * Verifica si el correo O el usuario ya existen en la BD (frontend validation vía AJAX)
 */
include("mysql.inc.php");

$classPDO = new mod_db();
$conn     = $classPDO->getConexion();

try {
    // Verificar correo
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $query = $conn->prepare("SELECT id FROM usuarios WHERE Correo = :email");
        $query->bindParam(":email", $email, PDO::PARAM_STR);
        $query->execute();
        echo ($query->rowCount() >= 1) ? "existe" : "libre";
    }

    // Verificar usuario
    if (isset($_POST['usuario'])) {
        $usuario = trim($_POST['usuario']);
        $query   = $conn->prepare("SELECT id FROM usuarios WHERE Usuario = :usuario");
        $query->bindParam(":usuario", $usuario, PDO::PARAM_STR);
        $query->execute();
        echo ($query->rowCount() >= 1) ? "existe" : "libre";
    }

} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}
?>
