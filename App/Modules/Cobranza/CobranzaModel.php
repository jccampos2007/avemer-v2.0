<?php
namespace App\Modules\Cobranza;

use PDO;

class CobranzaModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = \App\Core\Database::getInstance()->getConnection();
    }

    public function getPaginated(array $params): array
    {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $searchValue = trim($params['search']['value'] ?? '');
        $orderColumnIndex = (int)($params['order'][0]['column'] ?? 0);
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'dias_vencido',
            1 => 'alumno_nombre',
            2 => 'tipo_oferta_nombre',
            3 => 'cuota_nombre',
            4 => 'c.monto',
            5 => 'c.fecha_vencimiento',
            6 => 'saldo_pendiente',
            7 => 'dias_vencido',
        ];

        $sql = "SELECT
                    t.id AS deuda_id,
                    a.id AS alumno_id,
                    CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre,
                    CONCAT(COALESCE(a.tipo_documento,''), a.ci_pasaporte) AS alumno_ci,
                    a.correo,
                    a.tlf_celular,
                    c.id AS cuota_id,
                    c.nombre AS cuota_nombre,
                    c.monto,
                    c.fecha_vencimiento,
                    toa.nombre AS tipo_oferta_nombre,
                    CASE c.tipo_oferta_academica_id
                        WHEN 1 THEN (SELECT cur.nombre FROM curso_abierto ca JOIN curso cur ON ca.curso_id = cur.id WHERE ca.id = c.oferta_academica_id)
                        WHEN 2 THEN (SELECT dip.nombre FROM diplomado_abierto da JOIN diplomado dip ON da.diplomado_id = dip.id WHERE da.id = c.oferta_academica_id)
                        WHEN 3 THEN (SELECT ev.nombre FROM evento_abierto ea JOIN evento ev ON ea.evento_id = ev.id WHERE ea.id = c.oferta_academica_id)
                        WHEN 4 THEN (SELECT mae.nombre FROM maestria_abierto ma JOIN maestria mae ON ma.maestria_id = mae.id WHERE ma.id = c.oferta_academica_id)
                    END AS oferta_nombre,
                    COALESCE(pag.total_pagado, 0) AS total_pagado,
                    (c.monto - COALESCE(pag.total_pagado, 0)) AS saldo_pendiente,
                    DATEDIFF(CURDATE(), c.fecha_vencimiento) AS dias_vencido
                FROM transaccion t
                JOIN cuota c ON t.cuota_id = c.id
                JOIN alumno a ON t.alumno_id = a.id
                JOIN tipo_oferta_academica toa ON c.tipo_oferta_academica_id = toa.id
                LEFT JOIN (
                    SELECT cuota_id, alumno_id, SUM(monto) AS total_pagado
                    FROM pago
                    WHERE estatus_pago_id = 2
                    GROUP BY cuota_id, alumno_id
                ) pag ON pag.cuota_id = c.id AND pag.alumno_id = a.id
                WHERE t.tipo = 1
                  AND t.estatus = 1
                  AND (c.monto - COALESCE(pag.total_pagado, 0)) > 0";

        $countSql = "SELECT COUNT(*) FROM transaccion t
                     JOIN cuota c ON t.cuota_id = c.id
                     JOIN alumno a ON t.alumno_id = a.id
                     JOIN tipo_oferta_academica toa ON c.tipo_oferta_academica_id = toa.id
                     LEFT JOIN (
                        SELECT cuota_id, alumno_id, SUM(monto) AS total_pagado
                        FROM pago WHERE estatus_pago_id = 2
                        GROUP BY cuota_id, alumno_id
                     ) pag ON pag.cuota_id = c.id AND pag.alumno_id = a.id
                     WHERE t.tipo = 1 AND t.estatus = 1
                       AND (c.monto - COALESCE(pag.total_pagado, 0)) > 0";

        $where = [];
        $queryParams = [];

        if (!empty($searchValue)) {
            $where[] = "(a.primer_nombre LIKE :search OR a.primer_apellido LIKE :search2
                        OR CONCAT(COALESCE(a.tipo_documento,''), a.ci_pasaporte) LIKE :search_ci
                        OR c.nombre LIKE :search3
                        OR toa.nombre LIKE :search4
                        OR COALESCE(
                            (SELECT ca.numero FROM curso_abierto ca WHERE ca.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 1),
                            (SELECT da.numero FROM diplomado_abierto da WHERE da.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 2),
                            (SELECT ea.numero FROM evento_abierto ea WHERE ea.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 3),
                            (SELECT ma.numero FROM maestria_abierto ma WHERE ma.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 4),
                            ''
                        ) LIKE :search5
                        OR COALESCE(
                            (SELECT cur.nombre FROM curso_abierto ca JOIN curso cur ON ca.curso_id = cur.id WHERE ca.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 1),
                            (SELECT dip.nombre FROM diplomado_abierto da JOIN diplomado dip ON da.diplomado_id = dip.id WHERE da.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 2),
                            (SELECT ev.nombre FROM evento_abierto ea JOIN evento ev ON ea.evento_id = ev.id WHERE ea.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 3),
                            (SELECT mae.nombre FROM maestria_abierto ma JOIN maestria mae ON ma.maestria_id = mae.id WHERE ma.id = c.oferta_academica_id AND c.tipo_oferta_academica_id = 4),
                            ''
                        ) LIKE :search6)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search'] = $like;
            $queryParams[':search2'] = $like;
            $queryParams[':search_ci'] = $like;
            $queryParams[':search3'] = $like;
            $queryParams[':search4'] = $like;
            $queryParams[':search5'] = $like;
            $queryParams[':search6'] = $like;
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
            $countSql .= " AND " . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'dias_vencido';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        if ($length !== -1) {
            $sql .= " LIMIT :start, :length";
        }

        $stmt = $this->pdo->prepare($sql);
        if ($length !== -1) {
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        }
        foreach ($queryParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                $row['deuda_id'],
                htmlspecialchars(($row['alumno_nombre'] ?? '') . "\nCI: " . ($row['alumno_ci'] ?? '') . "\nTel: " . ($row['tlf_celular'] ?? '') . "\nEmail: " . ($row['correo'] ?? '')),
                htmlspecialchars($row['alumno_nombre'] ?? ''),
                htmlspecialchars($row['alumno_ci'] ?? ''),
                htmlspecialchars($row['tlf_celular'] ?? ''),
                htmlspecialchars($row['correo'] ?? ''),
                htmlspecialchars(($row['tipo_oferta_nombre'] ?? '') . ' - ' . ($row['oferta_nombre'] ?? '')),
                htmlspecialchars($row['cuota_nombre'] ?? ''),
                number_format((float)$row['monto'], 2),
                htmlspecialchars($row['fecha_vencimiento'] ?? ''),
                (int)$row['saldo_pendiente'] > 0 ? number_format((float)$row['saldo_pendiente'], 2) : '0.00',
                (int)$row['dias_vencido'],
                $row['alumno_id'],
                $row['cuota_id'],
                ''
            ];
        }

        $recordsTotal = $this->getTotalCount();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ];
    }

    private function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) FROM transaccion t
                JOIN cuota c ON t.cuota_id = c.id
                JOIN alumno a ON t.alumno_id = a.id
                LEFT JOIN (
                    SELECT cuota_id, alumno_id, SUM(monto) AS total_pagado
                    FROM pago WHERE estatus_pago_id = 2
                    GROUP BY cuota_id, alumno_id
                ) pag ON pag.cuota_id = c.id AND pag.alumno_id = a.id
                WHERE t.tipo = 1 AND t.estatus = 1
                  AND (c.monto - COALESCE(pag.total_pagado, 0)) > 0";
        $stmt = $this->pdo->query($sql);
        return (int)$stmt->fetchColumn();
    }
}
