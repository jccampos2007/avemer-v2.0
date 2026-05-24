<?php

namespace App\Core;

class AssetController
{
    public function serveJs($module, $file)
    {
        $module = basename($module);
        $file = basename($file);

        $path = APP_ROOT . '/App/Modules/' . $module . '/' . $file;

        if (!file_exists($path)) {
            http_response_code(404);
            header('Content-Type: application/javascript');
            echo '// asset not found';
            return;
        }

        header('Content-Type: application/javascript');
        header('Cache-Control: public, max-age=86400');
        readfile($path);
    }
}
