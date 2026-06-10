<div class="w-full space-y-6">

    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">Datos Personales</h3>
        </div>

        <form action="<?php echo BASE_URL; ?>profile/update" method="POST" enctype="multipart/form-data" id="profileForm">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="usuario_nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre:</label>
                    <input type="text" id="usuario_nombre" name="usuario_nombre" value="<?= htmlspecialchars($user['usuario_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="usuario_apellido" class="block text-gray-700 text-sm font-bold mb-2">Apellido:</label>
                    <input type="text" id="usuario_apellido" name="usuario_apellido" value="<?= htmlspecialchars($user['usuario_apellido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div class="lg:col-span-2">
                    <label for="correo" class="block text-gray-700 text-sm font-bold mb-2">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($user['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="lg:col-span-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Foto de Perfil:</label>
                    <div class="flex items-center gap-4">
                        <?php
                        $imgPath = $user['profile_image'] ?? null;
                        $avatarSrc = $imgPath ? BASE_URL . 'uploads/avatars/' . $imgPath : BASE_URL . 'image/default-avatar.png';
                        ?>
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 border-2 border-gray-300">
                            <img id="profilePreview" src="<?= $avatarSrc; ?>" alt="Foto de perfil" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <input type="file" id="profile_image_input" name="profile_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF o WebP. Máximo 2MB. Se convertirá a WebP.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    <i class="fa fa-save mr-1"></i> Guardar Cambios
                </button>
                <a href="<?= BASE_URL; ?>dashboard" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">Cancelar</a>
            </div>
        </form>
    </div>

    <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">Cambiar Contraseña</h3>
        </div>

        <form action="<?php echo BASE_URL; ?>profile/change-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="lg:col-span-4">
                    <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña Actual:</label>
                    <input type="password" id="current_password" name="current_password" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required minlength="6">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="input-form rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required minlength="6">
                </div>
            </div>

            <div class="flex items-center justify-between border-t pt-5">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                    <i class="fa fa-key mr-1"></i> Cambiar Contraseña
                </button>
                <a href="<?= BASE_URL; ?>dashboard" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 transition duration-150">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div id="cropModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-700">Recortar Foto</h3>
            <button type="button" id="closeCropModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="p-4 flex-1 overflow-hidden">
            <div class="max-h-[50vh] flex items-center justify-center">
                <img id="cropImage" src="" alt="Recortar" class="max-w-full">
            </div>
        </div>
        <div class="flex justify-end gap-2 px-4 py-3 border-t bg-gray-50">
            <button type="button" id="cancelCrop" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded transition">Cancelar</button>
            <button type="button" id="confirmCrop" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">Aplicar Recorte</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(function() {
    const fileInput = document.getElementById('profile_image_input');
    const modal = document.getElementById('cropModal');
    const cropImage = document.getElementById('cropImage');
    const closeBtn = document.getElementById('closeCropModal');
    const cancelBtn = document.getElementById('cancelCrop');
    const confirmBtn = document.getElementById('confirmCrop');
    const preview = document.getElementById('profilePreview');
    const profileForm = document.getElementById('profileForm');

    let cropper = null;
    let currentFile = null;

    function openModal(src) {
        modal.classList.remove('hidden');
        cropImage.src = src;
        if (cropper) cropper.destroy();
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            cropBoxMovable: true,
            cropBoxResizable: true,
            background: false,
        });
    }

    function closeModal(keepFile) {
        modal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropImage.src = '';
        if (!keepFile) {
            currentFile = null;
            fileInput.value = '';
        }
    }

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen no debe superar los 2MB.');
            fileInput.value = '';
            return;
        }

        currentFile = file;
        const reader = new FileReader();
        reader.onload = function(ev) {
            openModal(ev.target.result);
        };
        reader.readAsDataURL(file);
    });

    confirmBtn.addEventListener('click', function() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
        });

        canvas.toBlob(function(blob) {
            const croppedFile = new File([blob], currentFile.name, {
                type: 'image/png',
                lastModified: Date.now(),
            });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            fileInput.files = dataTransfer.files;

            preview.src = canvas.toDataURL('image/png');

            closeModal(true);
        }, 'image/png');
    });

    closeBtn.addEventListener('click', function() { closeModal(); });
    cancelBtn.addEventListener('click', function() { closeModal(); });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
