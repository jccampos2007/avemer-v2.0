<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carga el autoloader de Composer
require '../../../vendor/autoload.php';

function correo($titulo, $msj, $correo) {
    try {
        $mail = new PHPMailer(true);
        // --- Configuración del Servidor ---
        $mail->isSMTP();                              
        $mail->Host       = 'sub5.mail.dreamhost.com"';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@grupoavemer.com.ve';
        $mail->Password   = 'Pass.Seg91';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // --- Destinatarios ---
        $mail->setFrom('info@grupoavemer.com.ve', 'Grupo Avemer');
        $mail->addAddress($correo);
        $mail->AddBCC('preinscripcion@grupoavemer.com.ve', 'Copia de Correo enviado');

        // --- Contenido ---
        $mail->isHTML(true);     
        $mail->CharSet = 'UTF-8';                                   // Formato HTML
        $mail->Subject = "=?UTF-8?B?".base64_encode($titulo)."=?=";
        $mail->Body    =  $msj;
        $mail->AltBody =  $msj;

        $mail->send();
        echo 'El mensaje se envió correctamente';
    } catch (Exception $e) {
        echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
    }
}