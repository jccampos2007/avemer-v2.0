<?php
namespace App\Modules\Pagos;

use App\Core\Database;
use PDO;

class PagoModel
{
    private PDO $pdo;
    private string $table = 'pago';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getPaginated(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'p.id',
            1 => 'a.primer_nombre',
            2 => 'c.nombre',
            3 => 'fp.nombre',
            4 => 'b.nombre',
            5 => 'p.numero_control',
            6 => 'p.monto',
            7 => 'p.fecha',
            8 => 'ep.nombre',
        ];

        $sql = "SELECT p.id, a.primer_nombre, a.primer_apellido, a.ci_pasapote,
                       c.nombre AS cuota_nombre, fp.nombre AS forma_pago_nombre,
                       b.nombre AS banco_nombre, p.numero_control, p.monto, p.fecha,
                       p.alumno_id, p.cuota_id, p.forma_pago_id, p.banco_id, p.estatus_pago_id,
                       ep.nombre AS estatus_pago_nombre,
                       CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo
                FROM {$this->table} p
                JOIN alumno a ON p.alumno_id = a.id
                JOIN cuota c ON p.cuota_id = c.id
                JOIN forma_pago fp ON p.forma_pago_id = fp.id
                LEFT JOIN banco b ON p.banco_id = b.id
                JOIN estatus_pago ep ON p.estatus_pago_id = ep.id";
        $countSql = "SELECT COUNT(*) FROM {$this->table} p";

        $where = [];
        $queryParams = [];

        if (!empty($searchValue)) {
            $where[] = "(a.primer_nombre LIKE :search OR a.primer_apellido LIKE :search2
                        OR c.nombre LIKE :search3 OR fp.nombre LIKE :search4
                        OR COALESCE(b.nombre, '') LIKE :search5 OR COALESCE(p.numero_control, '') LIKE :search6)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search'] = $like;
            $queryParams[':search2'] = $like;
            $queryParams[':search3'] = $like;
            $queryParams[':search4'] = $like;
            $queryParams[':search5'] = $like;
            $queryParams[':search6'] = $like;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'p.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        if ((int)$length !== -1) {
            $sql .= " LIMIT :start, :length";
        }

        $stmt = $this->pdo->prepare($sql);
        if ((int)$length !== -1) {
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        }
        foreach ($queryParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                $row['id'],
                htmlspecialchars($row['alumno_nombre_completo'] ?? ''),
                htmlspecialchars($row['cuota_nombre'] ?? ''),
                htmlspecialchars($row['forma_pago_nombre'] ?? ''),
                htmlspecialchars($row['banco_nombre'] ?? ''),
                htmlspecialchars($row['numero_control'] ?? ''),
                number_format((float)$row['monto'], 2),
                htmlspecialchars($row['fecha'] ?? ''),
                htmlspecialchars($row['estatus_pago_nombre'] ?? ''),
                ''
            ];
        }

        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table} p");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $formattedData,
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo
                FROM {$this->table} p
                JOIN alumno a ON p.alumno_id = a.id
                WHERE p.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->table} (cuota_id, alumno_id, forma_pago_id, banco_id, numero_control, monto, fecha, estatus_pago_id)
                VALUES (:cuota_id, :alumno_id, :forma_pago_id, :banco_id, :numero_control, :monto, :fecha, :estatus_pago_id)";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':cuota_id' => $data['cuota_id'],
            ':alumno_id' => $data['alumno_id'],
            ':forma_pago_id' => $data['forma_pago_id'],
            ':banco_id' => $data['banco_id'],
            ':numero_control' => $data['numero_control'],
            ':monto' => $data['monto'],
            ':fecha' => $data['fecha'],
            ':estatus_pago_id' => $data['estatus_pago_id'] ?? 1,
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET cuota_id = :cuota_id, alumno_id = :alumno_id,
                forma_pago_id = :forma_pago_id, banco_id = :banco_id,
                numero_control = :numero_control, monto = :monto, fecha = :fecha,
                estatus_pago_id = :estatus_pago_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':cuota_id' => $data['cuota_id'],
            ':alumno_id' => $data['alumno_id'],
            ':forma_pago_id' => $data['forma_pago_id'],
            ':banco_id' => $data['banco_id'],
            ':numero_control' => $data['numero_control'],
            ':monto' => $data['monto'],
            ':fecha' => $data['fecha'],
            ':estatus_pago_id' => $data['estatus_pago_id'] ?? 1,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateStatus(int $id, int $estatusId): bool
    {
        $sql = "UPDATE {$this->table} SET estatus_pago_id = :estatus_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':estatus_id' => $estatusId,
            ':id' => $id,
        ]);
    }

    public function getFormaPagos(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM forma_pago ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBancos(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM banco ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstatusPagos(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM estatus_pago ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCuotasByAlumno(int $alumnoId): array
    {
        // Cuotas de cursos/diplomados/eventos (vía transaccion tipo=1)
        // UNION cuotas de maestrías (vía inscripcion_maestria)
        $sql = "SELECT DISTINCT c.id, c.nombre, c.monto, c.fecha_vencimiento,
                       toa.nombre AS tipo_oferta_nombre
                FROM transaccion t
                JOIN cuota c ON t.cuota_id = c.id
                JOIN tipo_oferta_academica toa ON c.tipo_oferta_academica_id = toa.id
                WHERE t.alumno_id = :alumno_id1 AND t.tipo = 1 AND t.estatus = 1

                UNION

                SELECT DISTINCT c.id, c.nombre, c.monto, c.fecha_vencimiento,
                       toa.nombre AS tipo_oferta_nombre
                FROM inscripcion_maestria im
                JOIN cuota c ON c.oferta_academica_id = im.maestria_abierto_id
                               AND c.tipo_oferta_academica_id = 4
                JOIN tipo_oferta_academica toa ON toa.id = c.tipo_oferta_academica_id
                WHERE im.alumno_id = :alumno_id2

                ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':alumno_id1' => $alumnoId, ':alumno_id2' => $alumnoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCuotasByAlumnoPendientes(int $alumnoId): array
    {
        // Subconsulta de pagos realizados (tipo=2) para calcular saldo
        $pagadoSubquery = "SELECT t2.cuota_id, t2.alumno_id, SUM(t2.monto) AS total_pagado
                           FROM transaccion t2
                           WHERE t2.tipo = 2
                           GROUP BY t2.cuota_id, t2.alumno_id";

        // Cuotas de cursos/diplomados/eventos (vía transaccion tipo=1) con saldo pendiente
        $sql = "SELECT c.id, c.nombre, c.monto, c.fecha_vencimiento,
                       toa.nombre AS tipo_oferta_nombre,
                       c.monto - COALESCE(pagado.total_pagado, 0) AS saldo_pendiente
                FROM (
                    SELECT DISTINCT t.cuota_id, t.alumno_id
                    FROM transaccion t
                    WHERE t.alumno_id = :alumno_id1 AND t.tipo = 1 AND t.estatus = 1

                    UNION

                    SELECT DISTINCT c2.id AS cuota_id, im.alumno_id
                    FROM inscripcion_maestria im
                    JOIN cuota c2 ON c2.oferta_academica_id = im.maestria_abierto_id
                                  AND c2.tipo_oferta_academica_id = 4
                    WHERE im.alumno_id = :alumno_id2
                ) AS fuente
                JOIN cuota c ON c.id = fuente.cuota_id
                JOIN tipo_oferta_academica toa ON toa.id = c.tipo_oferta_academica_id
                LEFT JOIN ({$pagadoSubquery}) pagado
                       ON pagado.cuota_id = fuente.cuota_id
                      AND pagado.alumno_id = fuente.alumno_id
                HAVING saldo_pendiente > 0
                ORDER BY c.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':alumno_id1' => $alumnoId, ':alumno_id2' => $alumnoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAlumnoById(int $id): ?array
    {
        $sql = "SELECT id, CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo FROM alumno WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getAlumnos(): array
    {
        $sql = "SELECT a.id, CONCAT(a.primer_nombre, ' ', a.primer_apellido, ' - CI: ', a.ci_pasapote) AS nombre_completo
                FROM alumno a ORDER BY a.primer_nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertTransaccion(array $data): bool
    {
        $sql = "INSERT INTO transaccion (alumno_id, cuota_id, tipo, monto, fecha, estatus, id_transaccion_origen)
                VALUES (:alumno_id, :cuota_id, :tipo, :monto, NOW(), :estatus, :id_transaccion_origen)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':alumno_id' => $data['alumno_id'],
            ':cuota_id' => $data['cuota_id'],
            ':tipo' => $data['tipo'] ?? 2,
            ':monto' => $data['monto'],
            ':estatus' => $data['estatus'] ?? 1,
            ':id_transaccion_origen' => $data['id_transaccion_origen'] ?? null,
        ]);
    }

    public function getTransaccionByPago(int $pagoId): ?array
    {
        $sql = "SELECT t.* FROM transaccion t
                JOIN pago p ON t.alumno_id = p.alumno_id AND t.cuota_id = p.cuota_id
                WHERE p.id = :pago_id AND t.tipo = 2
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pago_id' => $pagoId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getSaldoPendiente(int $alumnoId, int $cuotaId): float
    {
        $sql = "SELECT c.monto - COALESCE((
                    SELECT SUM(t2.monto) FROM transaccion t2
                    WHERE t2.alumno_id = :alumno_id2
                      AND t2.cuota_id = :cuota_id2
                      AND t2.tipo = 2
                ), 0) AS saldo
                FROM cuota c
                WHERE c.id = :cuota_id3";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':alumno_id2' => $alumnoId,
            ':cuota_id2' => $cuotaId,
            ':cuota_id3' => $cuotaId,
        ]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (float)$res['saldo'] : 0.0;
    }
}
