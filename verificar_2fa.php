<?php
session_start();

// Si no pasó la fase 1 (usuario+contraseña), redirigir al login
if (!isset($_SESSION['pre_auth_user'])) {
    header("Location: login.php");
    exit;
}

require 'vendor/autoload.php';
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

include("clases/mysql.inc.php");
include("Utilidades/CSRFProtection.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verificarFormulario();

    $pdo     = new mod_db();
    $conn    = $pdo->getConexion();
    $usuario = $_SESSION['pre_auth_user'];

    // Obtener el secreto 2FA del usuario
    $stmt = $conn->prepare("SELECT secret_2fa FROM usuarios WHERE Usuario = :u");
    $stmt->execute([':u' => $usuario]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $g    = new GoogleAuthenticator();
    $code = trim($_POST['codigo_2fa']);

    if ($row && $row['secret_2fa'] && $g->checkCode($row['secret_2fa'], $code)) {
        // Fase 2 exitosa — destruir sesión temporal y crear sesión definitiva
        $nombre = $_SESSION['pre_auth_user'];
        session_destroy();
        session_start();

        // Sesión definitiva (fase 2 completada)
        $_SESSION['autenticado']         = "SI";
        $_SESSION['Usuario']             = $nombre;
        $_SESSION['2fa_verificado']      = true;   // Confirmación de fase 2FA exitosa
        $_SESSION['2fa_timestamp']       = time();

        header("Location: formularios/PanelControl.php");
        exit;
    } else {
        $error = "Código incorrecto. Intenta de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <link rel="shortcut icon" href="patria/5564844.png">
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
    <style>
        body { background: #f0f4f8; }
        .verify-container {
            width: 420px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 35px 40px;
            text-align: center;
        }
        .verify-container h2 { color: #1a5276; margin-bottom: 8px; }
        .verify-container .sub { color: #666; font-size: 14px; margin-bottom: 25px; }
        .code-input {
            font-size: 32px;
            text-align: center;
            width: 180px;
            padding: 10px;
            border: 2px solid #1a5276;
            border-radius: 8px;
            letter-spacing: 8px;
            font-weight: bold;
            color: #1a5276;
        }
        .code-input:focus { outline: none; box-shadow: 0 0 8px rgba(26,82,118,0.4); }
        .btn-verify {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #1a5276;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-verify:hover { background: #154360; }
        .error-msg {
            background: #fdecea;
            border: 1px solid #e74c3c;
            color: #c0392b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .session-badge {
            background: #2980b9;
            color: white;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
            margin-bottom: 12px;
        }
        .back-link { margin-top: 15px; font-size: 13px; }
        .back-link a { color: #1a5276; text-decoration: none; }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div class="verify-container">
        <h2>🔐 Verificación 2FA</h2>

        <!-- Indicador de sesión fase 2 -->
        <div class="session-badge">
            Fase 2 — Usuario: <?php echo htmlspecialchars($_SESSION['pre_auth_user']); ?>
        </div>

        <p class="sub">
            Ingresa el código de 6 dígitos de <strong>Google Authenticator</strong>
        </p>

        <?php if ($error): ?>
            <div class="error-msg">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo CSRFProtection::campoHidden(); ?>

            <input
                type="text"
                name="codigo_2fa"
                class="code-input"
                maxlength="6"
                required
                placeholder="000000"
                autocomplete="off"
                inputmode="numeric"
                autofocus
            >

            <button type="submit" class="btn-verify">Verificar →</button>
        </form>

        <div class="back-link">
            <a href="login.php">← Volver al login</a>
        </div>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>
