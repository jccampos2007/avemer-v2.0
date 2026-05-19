<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carga el autoloader de Composer
require __DIR__ . '/../../../vendor/autoload.php';

function correo($titulo, $msj, $correo) {
    try {
        $mail = new PHPMailer(true);
        // --- Configuración del Servidor ---
        $mail->isSMTP();                              
        $mail->Host       = 'mail.privateemail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@grupoavemer.net';
        $mail->Password   = 'Avemer*g2026';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // $mail->Host       = 'smtp.gmail.com';
        // $mail->Username   = 'pruebastesting8@gmail.com';
        // $mail->Password   = 'pqqtgczwvtaoxxwj ';

        // --- Destinatarios ---
        $mail->setFrom('info@grupoavemer.net', 'Grupo Avemer');
        $mail->addAddress($correo);
        // $mail->AddBCC('preinscripcion@grupoavemer.com.ve', 'Copia de Correo enviado');
        $mail->AddBCC('ingdiazjc@gmail.com', 'Bcc jc');

        // --- Contenido ---
        $mail->isHTML(true);     
        $mail->CharSet = 'UTF-8';                                   // Formato HTML
        $mail->Subject = "=?UTF-8?B?".base64_encode($titulo)."=?=";
        $mail->Body    =  $msj;
        $mail->AltBody = strip_tags($msj);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}