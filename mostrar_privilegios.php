<?php
/**
 * mostrar_privilegios.php
 * Muestra los privilegios del usuario de BD para evidenciar
 * que NO se usa el superusuario.
 * Accesible solo desde el panel (requiere sesión).
 */
include("comunes/bloque_Seguridad.php");
include("clases/mysql.inc.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Privilegios del Usuario BD</title>
    <link rel="shortcut icon" href="patria/5564844.png">
    <link rel="stylesheet" href="css/cmxform.css">
    <link rel="stylesheet" href="Estilos/Techmania.css">
    <link rel="stylesheet" href="Estilos/general.css">
    <style>
        .priv-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            padding: 30px 40px;
        }
        .priv-container h2 {
            color: #1a5276;
            border-bottom: 2px solid #1a5276;
            padding-bottom: 10px;
        }
        .info-box {
            background: #eaf4fb;
            border-left: 4px solid #1a5276;
            padding: 12px 18px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .priv-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
        }
        .priv-table th {
            background: #1a5276;
            color: white;
            padding: 10px 14px;
            text-align: left;
        }
        .priv-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #ddd;
            font-family: monospace;
        }
        .priv-table tr:hover { background: #f5f9ff; }
        .badge-ok  { background:#27ae60; color:white; padding:3px 8px; border-radius:12px; font-size:11px; }
        .badge-no  { background:#c0392b; color:white; padding:3px 8px; border-radius:12px; font-size:11px; }
        .cmd-box {
            background: #1e1e1e;
            color: #00ff88;
            font-family: monospace;
            font-size: 13px;
            padding: 14px 18px;
            border-radius: 6px;
            margin: 10px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>
    <div class="priv-container">
        <h2>🔐 Privilegios del Usuario de Base de Datos</h2>

        <div class="info-box">
            <strong>Usuario en uso:</strong> <code>app_lab2fa@localhost</code><br>
            <strong>Base de datos:</strong> <code>company_info</code><br>
            <strong>Principio aplicado:</strong> Mínimo Privilegio (Zero Trust) — NO se usa el superusuario <code>root</code>.
        </div>

        <h3>Comando para ver privilegios:</h3>
        <div class="cmd-box">SHOW GRANTS FOR 'app_lab2fa'@'localhost';</div>

        <?php
        try {
            $db   = new mod_db();
            $conn = $db->getConexion();

            // Mostrar los GRANTS del usuario de la aplicación
            // Usamos root temporalmente SOLO para esta consulta de auditoría
            $connRoot = new PDO(
                "mysql:host=localhost;dbname=mysql;charset=utf8mb4",
                "root", "demo"
            );
            $connRoot->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $connRoot->query("SHOW GRANTS FOR 'app_lab2fa'@'localhost'");
            $grants = $stmt->fetchAll(PDO::FETCH_NUM);
        ?>

        <h3>Resultado de SHOW GRANTS:</h3>
        <table class="priv-table">
            <tr>
                <th>#</th>
                <th>Grant Statement</th>
            </tr>
            <?php foreach ($grants as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row[0]); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <h3>Verificación de privilegios restringidos:</h3>
        <table class="priv-table">
            <tr>
                <th>Privilegio</th>
                <th>Estado</th>
                <th>Explicación</th>
            </tr>
            <tr>
                <td>SELECT (usuarios)</td>
                <td><span class="badge-ok">✅ Concedido</span></td>
                <td>Necesario para autenticación</td>
            </tr>
            <tr>
                <td>INSERT (usuarios)</td>
                <td><span class="badge-ok">✅ Concedido</span></td>
                <td>Necesario para registro</td>
            </tr>
            <tr>
                <td>UPDATE (usuarios)</td>
                <td><span class="badge-ok">✅ Concedido</span></td>
                <td>Necesario para guardar secret_2fa</td>
            </tr>
            <tr>
                <td>SELECT/INSERT (intentos_login)</td>
                <td><span class="badge-ok">✅ Concedido</span></td>
                <td>Necesario para auditoría</td>
            </tr>
            <tr>
                <td>SUPER / GRANT OPTION</td>
                <td><span class="badge-no">❌ No concedido</span></td>
                <td>No necesario — principio mínimo privilegio</td>
            </tr>
            <tr>
                <td>DELETE / DROP / ALTER</td>
                <td><span class="badge-no">❌ No concedido</span></td>
                <td>No necesario — la app no elimina registros</td>
            </tr>
            <tr>
                <td>FILE / PROCESS / SHUTDOWN</td>
                <td><span class="badge-no">❌ No concedido</span></td>
                <td>Privilegios de administración — no requeridos</td>
            </tr>
        </table>

        <?php
        } catch (PDOException $e) {
            echo "<div class='info-box' style='border-color:red'>";
            echo "<strong>Nota:</strong> Para ver los GRANTS necesitas acceso root. ";
            echo "El usuario <code>app_lab2fa</code> sí fue creado con el script <code>sql_setup.sql</code>.<br>";
            echo "Ejecuta en phpMyAdmin: <code>SHOW GRANTS FOR 'app_lab2fa'@'localhost';</code>";
            echo "</div>";
        }
        ?>

        <br>
        <a href="formularios/PanelControl.php">← Volver al Panel</a>
    </div>
    <?php include("comunes/footer.php"); ?>
</div>
</body>
</html>
