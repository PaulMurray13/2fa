<?php
session_start();
require_once "../Utilidades/CSRFProtection.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <link rel="shortcut icon" href="../patria/5564844.png">
    <link rel="stylesheet" href="../css/cmxform.css">
    <link rel="stylesheet" href="../Estilos/Techmania.css">
    <style>
        body { background: #f0f4f8; }
        .registro-container {
            width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 35px 45px;
        }
        .registro-container h2 {
            text-align: center;
            color: #1a5276;
            margin-bottom: 25px;
            font-size: 22px;
            border-bottom: 2px solid #eaf4fb;
            padding-bottom: 12px;
        }
        .campo { margin-bottom: 16px; }
        .campo label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 13px;
        }
        .campo input, .campo select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .campo input:focus, .campo select:focus {
            border-color: #1a5276;
            outline: none;
            box-shadow: 0 0 5px rgba(26,82,118,0.3);
        }
        .btn-registrar {
            width: 100%;
            padding: 12px;
            background: #1a5276;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
            font-weight: bold;
        }
        .btn-registrar:hover { background: #154360; }
        #mensaje-estado {
            display: block;
            text-align: center;
            margin-bottom: 12px;
            font-size: 13px;
            min-height: 20px;
        }
        label.error { color: red; font-size: 12px; font-weight: normal; }
        .login-link { text-align: center; margin-top: 16px; font-size: 13px; }
        .login-link a { color: #1a5276; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .input-ok    { border-color: #27ae60 !important; }
        .input-error { border-color: #c0392b !important; }
        .checking    { border-color: #f39c12 !important; }
    </style>
</head>
<body>
<div id="wrap">
    <div id="headerlogin"></div>

    <div class="registro-container">
        <h2>📝 Registro de Usuario</h2>

        <span id="mensaje-estado"></span>

        <form id="form1" method="POST" action="../procesar_registro.php">
            <?php echo CSRFProtection::campoHidden(); ?>
            <input type="hidden" name="Accion" value="Guardar">

            <div class="campo">
                <label>Nombre: *</label>
                <input type="text" name="nombre" id="nombre" placeholder="Tu nombre">
            </div>

            <div class="campo">
                <label>Apellido: *</label>
                <input type="text" name="apellido" id="apellido" placeholder="Tu apellido">
            </div>

            <div class="campo">
                <label>Sexo: *</label>
                <select name="sexo" id="sexo">
                    <option value="">-- Selecciona --</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div class="campo">
                <label>Usuario: *</label>
                <input type="text" name="usuario" id="usuario" placeholder="Solo letras, números y _">
                <span id="msg-usuario" style="font-size:12px;"></span>
            </div>

            <div class="campo">
                <label>Correo electrónico: *</label>
                <input type="email" name="email1" id="email1" placeholder="correo@ejemplo.com">
                <span id="msg-email" style="font-size:12px;"></span>
            </div>

            <div class="campo">
                <label>Contraseña: * (mínimo 8 caracteres)</label>
                <input type="password" name="clave" id="clave" placeholder="Mínimo 8 caracteres">
            </div>

            <div class="campo">
                <label>Repetir Contraseña: *</label>
                <input type="password" name="clave_again" id="clave_again" placeholder="Repite tu contraseña">
            </div>

            <button type="submit" class="btn-registrar" id="btn-submit">Registrarse</button>
        </form>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="../login.php">Inicia sesión</a>
        </div>
    </div>

    <?php include("../comunes/footer.php"); ?>
</div>

<script>
$(document).ready(function() {

    // Validación en tiempo real: usuario duplicado
    var usuarioOk = true;
    $("#usuario").on("blur", function() {
        var val = $(this).val().trim();
        if (val.length < 3) return;
        $.post("../clases/VerificarCorreo.php", { usuario: val }, function(resp) {
            resp = $.trim(resp);
            if (resp === "existe") {
                $("#msg-usuario").html("<span style='color:red'>❌ Usuario ya en uso</span>");
                $("#usuario").addClass("input-error").removeClass("input-ok");
                usuarioOk = false;
            } else {
                $("#msg-usuario").html("<span style='color:green'>✅ Disponible</span>");
                $("#usuario").addClass("input-ok").removeClass("input-error");
                usuarioOk = true;
            }
        });
    });

    // Validación en tiempo real: correo duplicado
    var emailOk = true;
    $("#email1").on("blur", function() {
        var val = $(this).val().trim();
        if (val.length < 5) return;
        $.post("../clases/VerificarCorreo.php", { email: val }, function(resp) {
            resp = $.trim(resp);
            if (resp === "existe") {
                $("#msg-email").html("<span style='color:red'>❌ Correo ya en uso</span>");
                $("#email1").addClass("input-error").removeClass("input-ok");
                emailOk = false;
            } else {
                $("#msg-email").html("<span style='color:green'>✅ Disponible</span>");
                $("#email1").addClass("input-ok").removeClass("input-error");
                emailOk = true;
            }
        });
    });

    // Validación jQuery Validate
    $("#form1").validate({
        rules: {
            nombre:     { required: true, minlength: 2 },
            apellido:   { required: true, minlength: 2 },
            sexo:       { required: true },
            usuario:    { required: true, minlength: 3, pattern: /^[a-zA-Z0-9_]+$/ },
            clave:      { required: true, minlength: 8 },
            clave_again:{ required: true, equalTo: "#clave" },
            email1:     { required: true, email: true }
        },
        messages: {
            nombre:      { required: "El nombre es obligatorio", minlength: "Mínimo 2 caracteres" },
            apellido:    { required: "El apellido es obligatorio" },
            sexo:        { required: "Selecciona el sexo" },
            usuario:     { required: "El usuario es obligatorio", minlength: "Mínimo 3 caracteres" },
            clave:       { required: "La contraseña es obligatoria", minlength: "Mínimo 8 caracteres" },
            clave_again: { required: "Repite la contraseña", equalTo: "Las contraseñas no coinciden" },
            email1:      { required: "El correo es obligatorio", email: "Ingresa un correo válido" }
        },
        submitHandler: function(form) {
            // Verificación final de usuario y email únicos antes de enviar
            if (!usuarioOk) {
                $("#mensaje-estado").html("<span style='color:red'>❌ El usuario ya está en uso.</span>");
                return false;
            }
            if (!emailOk) {
                $("#mensaje-estado").html("<span style='color:red'>❌ El correo ya está en uso.</span>");
                return false;
            }
            form.submit();
        }
    });

    // Regla de patrón personalizada
    $.validator.addMethod("pattern", function(value, element, param) {
        return this.optional(element) || param.test(value);
    }, "Solo letras, números y guión bajo (_)");
});
</script>
</body>
</html>
