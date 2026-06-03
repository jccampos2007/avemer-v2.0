<?php
namespace App\Modules\Asistencia;

use App\Core\Database;
use PDO;

class AsistenciaModel
{
    private PDO $pdo;
    private string $table = 'asistencia';
    private string $detalleTable = 'asistencia_detalle';

    private array $tables = [
        1 => [
            'abierto' => 'curso_abierto',
            'control' => 'curso_control',
            'inscripcion' => 'inscripcion_curso',
            'base' => 'curso',
            'base_fk' => 'curso_id',
            'abierto_fk' => 'curso_abierto_id',
            'control_id_col' => 'id',
            'control_oferta_fk' => 'curso_abierto_id',
        ],
        2 => [
            'abierto' => 'diplomado_abierto',
            'control' => 'diplomado_control',
            'inscripcion' => 'inscripcion_diplomado',
            'base' => 'diplomado',
            'base_fk' => 'diplomado_id',
            'abierto_fk' => 'diplomado_abierto_id',
            'control_id_col' => 'id',
            'control_oferta_fk' => 'diplomado_abierto_id',
        ],
        3 => [
            'abierto' => 'evento_abierto',
            'control' => 'evento_control',
            'inscripcion' => 'inscripcion_evento',
            'base' => 'evento',
            'base_fk' => 'evento_id',
            'abierto_fk' => 'evento_abierto_id',
            'control_id_col' => 'id',
            'control_oferta_fk' => 'evento_abierto_id',
        ],
        4 => [
            'abierto' => 'maestria_abierto',
            'control' => 'maestria_control',
            'inscripcion' => 'inscripcion_maestria',
            'base' => 'maestria',
            'base_fk' => 'maestria_id',
            'abierto_fk' => 'maestria_abierto_id',
            'control_id_col' => 'id',
            'control_oferta_fk' => 'maestria_abierto_id',
        ],
    ];

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getCursos(): array
    {
        $sql = "SELECT ca.id, CONCAT(ca.numero, ' ', c.nombre) AS nombre
            FROM curso_abierto ca
            JOIN curso c ON ca.curso_id = c.id ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiplomados(): array
    {
        $sql = "SELECT da.id, CONCAT(da.numero, ' ', d.nombre) AS nombre
            FROM diplomado_abierto da
            JOIN diplomado d ON da.diplomado_id = d.id ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventos(): array
    {
        $sql = "SELECT ea.id, CONCAT(ea.numero, ' ', e.nombre) AS nombre
            FROM evento_abierto ea
            JOIN evento e ON ea.evento_id = e.id ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMaestrias(): array
    {
        $sql = "SELECT ma.id, CONCAT(ma.numero, ' ', m.nombre) AS nombre
            FROM maestria_abierto ma
            JOIN maestria m ON ma.maestria_id = m.id ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOfertaInfo(int $tipoOfertaId, int $ofertaId): ?array
    {
        switch ($tipoOfertaId) {
            case 1:
                $sql = "SELECT ca.id, ca.costo, ca.inicial, c.nombre AS oferta_nombre, CONCAT(ca.numero, ' - ', c.nombre) AS oferta_label
                    FROM curso_abierto ca JOIN curso c ON ca.curso_id = c.id WHERE ca.id = :id";
                break;
            case 2:
                $sql = "SELECT da.id, da.costo, da.inicial, d.nombre AS oferta_nombre, CONCAT(da.numero, ' - ', d.nombre) AS oferta_label
                    FROM diplomado_abierto da JOIN diplomado d ON da.diplomado_id = d.id WHERE da.id = :id";
                break;
            case 3:
                $sql = "SELECT ea.id, ea.costo, ea.inicial, e.nombre AS oferta_nombre, CONCAT(ea.numero, ' - ', e.nombre) AS oferta_label
                    FROM evento_abierto ea JOIN evento e ON ea.evento_id = e.id WHERE ea.id = :id";
                break;
            case 4:
                $sql = "SELECT ma.id, ma.costo, ma.inicial, m.nombre AS oferta_nombre, CONCAT(ma.numero, ' - ', m.nombre) AS oferta_label
                    FROM maestria_abierto ma JOIN maestria m ON ma.maestria_id = m.id WHERE ma.id = :id";
                break;
            default:
                return null;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getOrCreateMaster(int $tipoOfertaId, int $ofertaId, string $observacion = ''): int
    {
        $sql = "SELECT id FROM {$this->table}
                WHERE tipo_oferta_academica_id = :tipo_id AND oferta_id = :oferta_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo_id' => $tipoOfertaId, ':oferta_id' => $ofertaId]);
        $id = $stmt->fetchColumn();

        if ($id) {
            if ($observacion) {
                $sql = "UPDATE {$this->table} SET observacion = :observacion WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':observacion' => $observacion, ':id' => $id]);
            }
            return (int)$id;
        }

        $sql = "INSERT INTO {$this->table} (tipo_oferta_academica_id, oferta_id, observacion)
                VALUES (:tipo_id, :oferta_id, :observacion)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tipo_id' => $tipoOfertaId,
            ':oferta_id' => $ofertaId,
            ':observacion' => $observacion ?: null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getClases(int $tipoOfertaId, int $ofertaId, int $masterId): array
    {
        if (!isset($this->tables[$tipoOfertaId])) return [];
        $m = $this->tables[$tipoOfertaId];

        switch ($tipoOfertaId) {
            case 1:
                $sql = "SELECT cc.id, cc.fecha, cc.tema AS detalle,
                               CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS docente_nombre
                        FROM curso_control cc
                        JOIN docente doc ON cc.docente_id = doc.id
                        WHERE cc.{$m['control_oferta_fk']} = :oferta_id
                        ORDER BY cc.fecha ASC";
                break;
            case 2:
                $sql = "SELECT dc.id, dc.fecha, CONCAT(c.numero, ' ', c.nombre) AS detalle,
                               CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS docente_nombre
                        FROM diplomado_control dc
                        JOIN docente doc ON dc.docente_id = doc.id
                        LEFT JOIN capitulo c ON dc.capitulo_id = c.id
                        WHERE dc.{$m['control_oferta_fk']} = :oferta_id
                        ORDER BY dc.fecha ASC";
                break;
            case 3:
                $sql = "SELECT ec.id, ec.fecha, d.nombre AS detalle,
                               CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS docente_nombre
                        FROM evento_control ec
                        JOIN docente doc ON ec.docente_id = doc.id
                        LEFT JOIN detalle d ON ec.detalle_id = d.id
                        WHERE ec.{$m['control_oferta_fk']} = :oferta_id
                        ORDER BY ec.fecha ASC";
                break;
            case 4:
                $sql = "SELECT mc.id, mc.fecha, mc.tema AS detalle,
                               CONCAT(doc.primer_nombre, ' ', doc.primer_apellido) AS docente_nombre
                        FROM maestria_control mc
                        JOIN docente doc ON mc.docente_id = doc.id
                        WHERE mc.{$m['control_oferta_fk']} = :oferta_id
                        ORDER BY mc.fecha ASC";
                break;
            default:
                return [];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oferta_id' => $ofertaId]);
        $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach attendance stats per class
        $statsSql = "SELECT clase_id,
                            COUNT(*) AS total_registrados,
                            SUM(presente) AS asistentes
                     FROM {$this->detalleTable}
                     WHERE asistencia_id = :master_id
                     GROUP BY clase_id";
        $statsStmt = $this->pdo->prepare($statsSql);
        $statsStmt->execute([':master_id' => $masterId]);
        $stats = [];
        foreach ($statsStmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $stats[(int)$s['clase_id']] = $s;
        }

        foreach ($clases as &$c) {
            $sid = (int)$c['id'];
            $c['total_registrados'] = isset($stats[$sid]) ? (int)$stats[$sid]['total_registrados'] : 0;
            $c['asistentes'] = isset($stats[$sid]) ? (int)$stats[$sid]['asistentes'] : 0;
        }

        return $clases;
    }

    public function getAlumnos(int $tipoOfertaId, int $ofertaId, int $masterId, int $claseId): array
    {
        if (!isset($this->tables[$tipoOfertaId])) return [];
        $m = $this->tables[$tipoOfertaId];

        $sql = "SELECT a.id AS alumno_id,
                       CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre,
                       a.ci_pasaporte AS alumno_ci,
                       COALESCE(ad.presente, 0) AS asiste
                FROM {$m['inscripcion']} i
                JOIN alumno a ON i.alumno_id = a.id
                LEFT JOIN {$this->detalleTable} ad
                    ON ad.alumno_id = a.id
                    AND ad.asistencia_id = :master_id
                    AND ad.clase_id = :clase_id
                WHERE i.{$m['abierto_fk']} = :oferta_id
                ORDER BY a.primer_nombre ASC, a.primer_apellido ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':master_id' => $masterId,
            ':clase_id' => $claseId,
            ':oferta_id' => $ofertaId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInscritos(int $tipoOfertaId, int $ofertaId): array
    {
        if (!isset($this->tables[$tipoOfertaId])) return [];
        $m = $this->tables[$tipoOfertaId];

        $sql = "SELECT a.id FROM {$m['inscripcion']} i
                JOIN alumno a ON i.alumno_id = a.id
                WHERE i.{$m['abierto_fk']} = :oferta_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oferta_id' => $ofertaId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveAsistencia(int $masterId, int $claseId, int $tipoOfertaId, int $ofertaId, array $alumnoIds): bool
    {
        $this->pdo->beginTransaction();
        try {
            $alumnoIds = array_map('intval', $alumnoIds);
            $alumnoIds = array_flip($alumnoIds);
            $inscritos = $this->getInscritos($tipoOfertaId, $ofertaId);

            $sql = "INSERT INTO {$this->detalleTable} (asistencia_id, clase_id, alumno_id, presente)
                    VALUES (:master_id, :clase_id, :alumno_id, :presente)
                    ON DUPLICATE KEY UPDATE presente = VALUES(presente)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($inscritos as $alumnoId) {
                $presente = isset($alumnoIds[$alumnoId]) ? 1 : 0;
                $stmt->execute([
                    ':master_id' => $masterId,
                    ':clase_id' => $claseId,
                    ':alumno_id' => $alumnoId,
                    ':presente' => $presente,
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log('Error saving attendance: ' . $e->getMessage());
            return false;
        }
    }

    public function getAttendanceData(): array
    {
        $sql = "SELECT a.id, a.tipo_oferta_academica_id,
                       toa.nombre AS tipo_oferta_nombre,
                       ad.alumno_id,
                       CONCAT(al.primer_nombre, ' ', al.primer_apellido) AS alumno_nombre,
                       ad.presente,
                       COALESCE(a.observacion, '') AS observacion
                FROM {$this->table} a
                JOIN tipo_oferta_academica toa ON a.tipo_oferta_academica_id = toa.id
                JOIN {$this->detalleTable} ad ON a.id = ad.asistencia_id
                JOIN alumno al ON ad.alumno_id = al.id
                ORDER BY a.id DESC, ad.clase_id ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

}
