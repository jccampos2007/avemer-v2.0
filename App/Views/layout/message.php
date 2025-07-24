<?php
// php_mvc_app/app/Views/layout/message.php
// Para mostrar mensajes de éxito/error (flash messages)
// $message variable se pasa desde el Controller::view()

if ($message):
    $type = $message['type'];
    $text = $message['text'];
    $bgColor = ($type == 'success') ? 'bg-green-500' : 'bg-red-500';
?>
    <div class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white <?php echo $bgColor; ?> transition-opacity duration-300 ease-out opacity-100"
        id="flash-message"
        onclick="this.remove()">
        <?php echo htmlspecialchars($text); ?>
    </div>

    <script>
        // Desaparecer el mensaje después de 5 segundos
        setTimeout(function() {
            const msg = document.getElementById('flash-message');
            if (msg) {
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500); // Eliminar después de la transición
            }
        }, 5000);
    </script>
<?php endif; ?>