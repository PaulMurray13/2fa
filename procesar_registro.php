<?php
session_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once "Utilidades/CSRFProtection.php";
CSRFProtection::verificarFormulario();

include("clases/mysql.inc.php");
include("clases/SanitizarEntrada.php");
include("clases/RegistroUsuario.php");
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

$pdo        = new mod_db();
$arrMensaje = array();

try {
    $MyRegistro = new RegistroUsuario($_POST, $pdo, $arrMensaje);

    if (count($arrMensaje) == 0) {
        $Accion = $_POST['Accion'];

        if ($Accion == "Guardar") {
            $MyRegistro->Guardar_RegistroUsuario();

            // Generar secreto 2FA y guardar en BD
            $g      = new GoogleAuthenticator();
            $secret = $g->generateSecret();
            $MyRegistro->GuardarMySecreto($secret);

            // Generar URL del QR
            $nombre_usuario = $MyRegistro->getUsuario();
            $nombre_app     = 'MiSistemaLogin';
            // GoogleQrUrl::generate() ya devuelve la URL completa del QR
            $qr_url = GoogleQrUrl::generate($nombre_usuario, $secret, $nombre_app, 200);

            // SESIÓN FASE QR: confirmar que el QR fue generado exitosamente
            $_SESSION['qr_url']           = $qr_url;
            $_SESSION['usuario_nuevo']    = $nombre_usuario;
            $_SESSION['qr_fase_exitosa']  = true;   // Sesión que confirma fase QR exitosa
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escanea tu QR - Configuración 2FA</title>
    <link rel="shortcut icon" href="patria/5564844.png">
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
    <style>
        body { background: #f0f4f8; }
        .qr-container {
            width: 480px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 35px 40px;
            text-align: center;
        }
        .qr-container h2 { color: #1a5276; font-size: 22px; margin-bottom: 6px; }
        .qr-container .sub { color: #555; font-size: 14px; margin-bottom: 20px; }
        .qr-container img {
            border: 3px solid #1a5276;
            border-radius: 8px;
            padding: 8px;
            margin: 10px 0;
        }
        .qr-steps {
            background: #eaf4fb;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 18px 0;
            text-align: left;
            font-size: 13px;
            color: #333;
        }
        .qr-steps ol { margin: 6px 0 0; padding-left: 18px; }
        .qr-steps li { margin-bottom: 6px; }
        .session-badge {
            background: #27ae60;
            color: white;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .btn-login {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 35px;
            background: #1a5276;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-login:hover { background: #154360; }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div class="qr-container">
        <h2>✅ ¡Registro exitoso!</h2>

        <!-- Indicador de sesión QR activa -->
        <?php if (isset($_SESSION['qr_fase_exitosa']) && $_SESSION['qr_fase_exitosa']): ?>
        <div class="session-badge">🔐 Sesión QR activa — fase de configuración 2FA</div>
        <?php endif; ?>

        <p class="sub">
            Hola <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong>,
            escanea este código con <strong>Google Authenticator</strong>
        </p>

        <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="Código QR 2FA">

        <div class="qr-steps">
            <strong>📱 Pasos para configurar:</strong>
            <ol>
                <li>Abre <strong>Google Authenticator</strong> en tu celular</li>
                <li>Toca el botón <strong>+</strong> → "Escanear código QR"</li>
                <li>Apunta la cámara a este código</li>
                <li>El sistema quedará registrado en tu app</li>
                <li>Listo — ya puedes iniciar sesión con 2FA</li>
            </ol>
        </div>

        <a href="login.php" class="btn-login">Ir al Login →</a>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>

<?php
        }
    } else {
        echo "<div style='text-align:center; margin-top:50px;'>";
        echo "<h3 style='color:red'>Errores en el formulario:</h3><ul>";
        foreach ($arrMensaje as $val) {
            echo "<li>" . htmlspecialchars($val) . "</li>";
        }
        echo "</ul>";
        echo "<a href='formularios/registrese.php'>← Volver al registro</a></div>";
    }

} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
} finally {
    $pdo       = null;
    $MyRegistro = null;
}
?>