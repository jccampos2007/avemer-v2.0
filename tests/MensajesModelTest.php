<?php

use Tests\DatabaseTestCase;
use App\Modules\Mensajes\MensajesModel;

class MensajesModelTest extends DatabaseTestCase
{
    private MensajesModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new MensajesModel();
    }

    public function test_getById_returns_mensaje(): void
    {
        $m = $this->model->getById(999);
        $this->assertIsArray($m);
        $this->assertSame('TEST Mensaje', $m['titulo']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'titulo' => 'TEST Mensaje Create',
            'mensaje' => 'Created body',
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM mensajehtml WHERE titulo = 'TEST Mensaje Create'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM mensajehtml WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'titulo' => 'TEST Mensaje Updated',
            'mensaje' => 'Updated body',
        ]));

        $m = $this->model->getById(999);
        $this->assertSame('TEST Mensaje Updated', $m['titulo']);

        $this->model->update(999, ['titulo' => 'TEST Mensaje', 'mensaje' => 'Test message body']);
    }

    public function test_delete_removes_record(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("DELETE FROM buzon WHERE id_mensaje = 999");
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $pdo->exec("INSERT IGNORE INTO mensajehtml (id, titulo, mensaje) VALUES (999, 'TEST Mensaje', 'Test message body')");
        $pdo->exec("INSERT IGNORE INTO buzon (id, correo, id_mensaje, estado) VALUES (999, 'test@envio.com', 999, 0)");
    }
}
