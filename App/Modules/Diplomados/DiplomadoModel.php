<?php
// php_mvc_app/app/Modules/Diplomados/Models/DiplomadoModel.php
namespace App\Modules\Diplomados; // Nuevo namespace

use App\Core\Database;
use PDO;

class DiplomadoModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM diplomado ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM diplomado WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $diplomado = $stmt->fetch();
        return $diplomado ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO diplomado (duracion_id, nombre, descripcion, siglas, costo, inicial) VALUES (:duracion_id, :nombre, :descripcion, :siglas, :costo, :inicial)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'duracion_id' => $data['duracion_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'siglas' => $data['siglas'],
            'costo' => $data['costo'],
            'inicial' => $data['inicial']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE diplomado SET duracion_id = :duracion_id, nombre = :nombre, descripcion = :descripcion, siglas = :siglas, costo = :costo, inicial = :inicial WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'duracion_id' => $data['duracion_id'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'siglas' => $data['siglas'],
            'costo' => $data['costo'],
            'inicial' => $data['inicial'],
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM diplomado WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
