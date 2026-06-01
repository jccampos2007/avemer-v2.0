<?php
namespace App\Modules\Cuota;

use App\Core\Database;
use PDO;

class CuotaModel
{
    private $pdo;
    private $table = 'cuota';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nombre, monto, oferta_academica_id, tipo_oferta_academica_id, diplomado_control_id, generado, fecha_vencimiento, fecha)
                VALUES (:nombre, :monto, :oferta_academica_id, :tipo_oferta_academica_id, :diplomado_control_id, 0, :fecha_vencimiento, CURDATE())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':monto' => $data['monto'],
            ':oferta_academica_id' => $data['oferta_academica_id'],
            ':tipo_oferta_academica_id' => $data['tipo_oferta_academica_id'],
            ':diplomado_control_id' => $data['diplomado_control_id'] ?? null,
            ':fecha_vencimiento' => $data['fecha_vencimiento']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET nombre = :nombre, monto = :monto, oferta_academica_id = :oferta_academica_id,
                tipo_oferta_academica_id = :tipo_oferta_academica_id, diplomado_control_id = :diplomado_control_id, fecha_vencimiento = :fecha_vencimiento
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':monto' => $data['monto'],
            ':oferta_academica_id' => $data['oferta_academica_id'],
            ':tipo_oferta_academica_id' => $data['tipo_oferta_academica_id'],
            ':diplomado_control_id' => $data['diplomado_control_id'] ?? null,
            ':fecha_vencimiento' => $data['fecha_vencimiento'],
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getCursos(): array
    {
        $sql = "SELECT ca.id, CONCAT(ca.numero , ' ', c.nombre) AS nombre
            FROM curso_abierto ca
            JOIN curso c ON ca.curso_id = c.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiplomados(): array
    {
        $sql = "SELECT da.id, CONCAT(da.numero , ' ', d.nombre) AS nombre
            FROM diplomado_abierto da
            JOIN diplomado d ON da.diplomado_id = d.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventos(): array
    {
        $sql = "SELECT ea.id, CONCAT(ea.numero , ' ', e.nombre) AS nombre
            FROM evento_abierto ea
            JOIN evento e ON ea.evento_id = e.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMaestrias(): array
    {
        $sql = "SELECT ma.id, CONCAT(ma.numero , ' ', nombre) AS nombre
            FROM maestria_abierto ma
            JOIN maestria m ON ma.maestria_id = m.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function getDiplomadoControles(int $diplomadoAbiertoId): array
    {
        $sql = "SELECT dc.id, dc.mensualidad AS costo, dc.fecha AS control_fecha, c.nombre AS capitulo_nombre, c.numero AS capitulo_numero
            FROM diplomado_control dc
            JOIN capitulo c ON dc.capitulo_id = c.id
            WHERE dc.diplomado_abierto_id = :diplomado_abierto_id
            ORDER BY c.numero ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':diplomado_abierto_id', $diplomadoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCuotasByOffer(int $tipoOfertaId, int $ofertaId): array
    {
        $selectExtra = '';
        $joinExtra = '';
        if ($tipoOfertaId === 2) {
            $selectExtra = ', dc.id AS diplomado_control_id, c.nombre AS capitulo_nombre, c.numero AS capitulo_numero, dc.mensualidad AS capitulo_costo';
            $joinExtra = 'LEFT JOIN diplomado_control dc ON c2.diplomado_control_id = dc.id
                          LEFT JOIN capitulo c ON dc.capitulo_id = c.id';
        } else {
            $selectExtra = ', NULL AS diplomado_control_id, NULL AS capitulo_nombre, NULL AS capitulo_numero, NULL AS capitulo_costo';
        }

        $sql = "
            SELECT
                c2.id,
                c2.nombre,
                c2.monto,
                c2.generado,
                c2.fecha_vencimiento,
                c2.fecha,
                toa.nombre AS tipo_oferta_nombre
                {$selectExtra}
            FROM
                {$this->table} c2
            JOIN
                tipo_oferta_academica toa ON c2.tipo_oferta_academica_id = toa.id
            {$joinExtra}
            WHERE
                c2.tipo_oferta_academica_id = :tipo_oferta_id AND c2.oferta_academica_id = :oferta_id
            ORDER BY
                c2.fecha DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tipo_oferta_id', $tipoOfertaId, PDO::PARAM_INT);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsByOffer(int $tipoOfertaId, int $ofertaId, int $cuotaId): array
    {
        $inscripcionTableName = '';
        $ofertaIdColumnName = '';

        switch ($tipoOfertaId) {
            case 1:
                $inscripcionTableName = 'inscripcion_curso';
                $ofertaIdColumnName = 'curso_abierto_id';
                break;
            case 2:
                $inscripcionTableName = 'inscripcion_diplomado';
                $ofertaIdColumnName = 'diplomado_abierto_id';
                break;
            case 3:
                $inscripcionTableName = 'inscripcion_evento';
                $ofertaIdColumnName = 'evento_abierto_id';
                break;
            case 4:
                $inscripcionTableName = 'inscripcion_maestria';
                $ofertaIdColumnName = 'maestria_abierto_id';
                break;
            default:
                return [];
        }

        $sql = "
            SELECT
                a.id AS alumno_id,
                CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo,
                a.ci_pasapote AS alumno_ci,
                CASE WHEN t.id IS NOT NULL THEN 1 ELSE 0 END AS tiene_deuda
            FROM
                alumno a
            JOIN
                {$inscripcionTableName} i ON a.id = i.alumno_id
            LEFT JOIN
                transaccion t ON a.id = t.alumno_id AND t.cuota_id = :cuota_id AND t.tipo = 1
            WHERE
                i.{$ofertaIdColumnName} = :oferta_id
            ORDER BY
                a.primer_nombre ASC, a.primer_apellido ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':cuota_id', $cuotaId, PDO::PARAM_INT);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasExistingDebt(int $alumnoId, int $cuotaId): bool
    {
        $sql = "SELECT COUNT(*) FROM transaccion WHERE alumno_id = :alumno_id AND cuota_id = :cuota_id AND tipo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->bindParam(':cuota_id', $cuotaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function insertTransaction(array $data): bool
    {
        $sql = "INSERT INTO transaccion (alumno_id, cuota_id, tipo, monto, fecha, estatus, id_transaccion_origen)
                VALUES (:alumno_id, :cuota_id, :tipo, :monto, NOW(), :estatus, :id_transaccion_origen)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':alumno_id' => $data['alumno_id'],
            ':cuota_id' => $data['cuota_id'],
            ':tipo' => $data['tipo'] ?? 1,
            ':monto' => $data['monto'],
            ':estatus' => $data['estatus'] ?? 1,
            ':id_transaccion_origen' => $data['id_transaccion_origen'] ?? null
        ]);
    }

    public function updateCuotaGenerado(int $cuotaId, int $generadoStatus): bool
    {
        $sql = "UPDATE {$this->table} SET generado = :generado WHERE id = :cuota_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':generado', $generadoStatus, PDO::PARAM_INT);
        $stmt->bindParam(':cuota_id', $cuotaId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
