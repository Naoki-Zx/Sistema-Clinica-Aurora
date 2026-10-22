<?php
/** Exclusão de cliente — bloqueada se houver agendamentos (FK RESTRICT). */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('clientes.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('danger', 'Identificador inválido.');
    redirect('clientes.php');
}

$check = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE cliente_id = :id');
$check->execute([':id' => $id]);

if ((int) $check->fetchColumn() > 0) {
    flash_set('danger', 'Não é possível excluir: o cliente possui agendamentos vinculados.');
    redirect('clientes.php');
}

$stmt = $pdo->prepare('DELETE FROM clientes WHERE id = :id');
$stmt->execute([':id' => $id]);

flash_set($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Cliente excluído.' : 'Cliente não encontrado.');
redirect('clientes.php');
