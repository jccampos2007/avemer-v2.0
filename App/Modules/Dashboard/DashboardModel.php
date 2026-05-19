<?php
namespace App\Modules\Dashboard;

use App\Core\Database;
use PDO;

class DashboardModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getInscripcionesStats(): array
    {
        $stats = [
            'diplomados' => ['total' => 0, 'activos' => 0],
            'eventos' => ['total' => 0, 'activos' => 0],
            'maestrias' => ['total' => 0, 'activos' => 0],
            'cursos' => ['total' => 0, 'activos' => 0]
        ];

        try {
            $stmt = $this->pdo->query("SELECT 
                (SELECT COUNT(*) FROM inscripcion_diplomado) as total, 
                (SELECT COUNT(id.id) FROM inscripcion_diplomado id JOIN diplomado_abierto da ON id.diplomado_abierto_id = da.id WHERE da.estatus_id = 1 AND da.deleted_at IS NULL) as activos");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $stats['diplomados']['total'] = (int) $res['total'];
                $stats['diplomados']['activos'] = (int) $res['activos'];
            }
        } catch (\Exception $e) {}

        try {
            $stmt = $this->pdo->query("SELECT 
                (SELECT COUNT(*) FROM inscripcion_evento) as total, 
                (SELECT COUNT(ie.id) FROM inscripcion_evento ie JOIN evento_abierto ea ON ie.evento_abierto_id = ea.id WHERE ea.estatus_id = 1 AND ea.deleted_at IS NULL) as activos");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $stats['eventos']['total'] = (int) $res['total'];
                $stats['eventos']['activos'] = (int) $res['activos'];
            }
        } catch (\Exception $e) {}

        try {
            $stmt = $this->pdo->query("SELECT 
                (SELECT COUNT(*) FROM inscripcion_maestria) as total, 
                (SELECT COUNT(im.id) FROM inscripcion_maestria im JOIN maestria_abierto ma ON im.maestria_abierto_id = ma.id WHERE ma.estatus_id = 1 AND ma.deleted_at IS NULL) as activos");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $stats['maestrias']['total'] = (int) $res['total'];
                $stats['maestrias']['activos'] = (int) $res['activos'];
            }
        } catch (\Exception $e) {}

        try {
            $stmt = $this->pdo->query("SELECT 
                (SELECT COUNT(*) FROM inscripcion_curso) as total, 
                (SELECT COUNT(ic.id) FROM inscripcion_curso ic JOIN curso_abierto ca ON ic.curso_abierto_id = ca.id WHERE ca.estatus_id = 1 AND ca.deleted_at IS NULL) as activos");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $stats['cursos']['total'] = (int) $res['total'];
                $stats['cursos']['activos'] = (int) $res['activos'];
            }
        } catch (\Exception $e) {}

        return $stats;
    }

    public function getInscripcionesLastMonthStats(): array
    {
        $items = [];

        $getRandomColor = function() {
            $r = mt_rand(50, 200);
            $g = mt_rand(50, 200);
            $b = mt_rand(50, 200);
            return [
                'bg' => "rgba($r, $g, $b, 0.7)",
                'border' => "rgba($r, $g, $b, 1)"
            ];
        };

        // Diplomados
        try {
            $sql = "SELECT 
                        d.nombre as label,
                        COUNT(id.id) as count
                    FROM diplomado_abierto da
                    JOIN diplomado d ON da.diplomado_id = d.id
                    LEFT JOIN inscripcion_diplomado id ON id.diplomado_abierto_id = da.id
                    WHERE da.estatus_id = 1 AND da.deleted_at IS NULL
                    GROUP BY da.id";
            $stmt = $this->pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $colors = $getRandomColor();
                $row['bg_color'] = $colors['bg'];
                $row['border_color'] = $colors['border'];
                $row['category_index'] = 0;
                $items[] = $row;
            }
        } catch (\Exception $e) {}

        // Eventos
        try {
            $sql = "SELECT 
                        e.nombre as label,
                        COUNT(ie.id) as count
                    FROM evento_abierto ea
                    JOIN evento e ON ea.evento_id = e.id
                    LEFT JOIN inscripcion_evento ie ON ie.evento_abierto_id = ea.id
                    WHERE ea.estatus_id = 1 AND ea.deleted_at IS NULL
                    GROUP BY ea.id";
            $stmt = $this->pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $colors = $getRandomColor();
                $row['bg_color'] = $colors['bg'];
                $row['border_color'] = $colors['border'];
                $row['category_index'] = 1;
                $items[] = $row;
            }
        } catch (\Exception $e) {}

        // Maestrias
        try {
            $sql = "SELECT 
                        m.nombre as label,
                        COUNT(im.id) as count
                    FROM maestria_abierto ma
                    JOIN maestria m ON ma.maestria_id = m.id
                    LEFT JOIN inscripcion_maestria im ON im.maestria_abierto_id = ma.id
                    WHERE ma.estatus_id = 1 AND ma.deleted_at IS NULL
                    GROUP BY ma.id";
            $stmt = $this->pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $colors = $getRandomColor();
                $row['bg_color'] = $colors['bg'];
                $row['border_color'] = $colors['border'];
                $row['category_index'] = 2;
                $items[] = $row;
            }
        } catch (\Exception $e) {}

        // Cursos
        try {
            $sql = "SELECT 
                        c.nombre as label,
                        COUNT(ic.id) as count
                    FROM curso_abierto ca
                    JOIN curso c ON ca.curso_id = c.id
                    LEFT JOIN inscripcion_curso ic ON ic.curso_abierto_id = ca.id
                    WHERE ca.estatus_id = 1 AND ca.deleted_at IS NULL
                    GROUP BY ca.id";
            $stmt = $this->pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $colors = $getRandomColor();
                $row['bg_color'] = $colors['bg'];
                $row['border_color'] = $colors['border'];
                $row['category_index'] = 3;
                $items[] = $row;
            }
        } catch (\Exception $e) {}

        return $items;
    }
}
