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
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pruebastesting8@gmail.com';
        $mail->Password   = 'pqqtgczwvtaoxxwj ';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // --- Destinatarios ---
        $mail->setFrom('pruebastesting8@gmail.com', 'Grupo Avemer');
        $mail->addAddress($correo);
        // $mail->AddBCC('preinscripcion@grupoavemer.com.ve', 'Copia de Correo enviado');

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

// correo(
//     'Asunto por defecto Test',
//     'testing send smart data',
//     'cesaralejandrorojas0@gmail.com'
//     );