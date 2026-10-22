<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('servicos.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('danger', 'Identificador inválido.');
    redirect('servicos.php');
}

$check = $pdo->prepare('SELECT COUNT(*) FROM agendamentos WHERE servico_id = :id');
$check->execute([':id' => $id]);

if ((int) $check->fetchColumn() > 0) {
    flash_set('danger', 'Não é possível excluir: o serviço possui agendamentos vinculados.');
    redirect('servicos.php');
}

$stmt = $pdo->prepare('DELETE FROM servicos WHERE id = :id');
$stmt->execute([':id' => $id]);

flash_set($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Serviço excluído.' : 'Serviço não encontrado.');
redirect('servicos.php');
