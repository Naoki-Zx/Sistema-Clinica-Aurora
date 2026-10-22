<?php
/** Exclusão de agendamento via POST (modal de confirmação). */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('agendamentos.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('danger', 'Identificador inválido.');
    redirect('agendamentos.php');
}

$stmt = $pdo->prepare('DELETE FROM agendamentos WHERE id = :id');
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() > 0) {
    flash_set('success', 'Agendamento excluído.');
} else {
    flash_set('danger', 'Agendamento não encontrado.');
}

redirect('agendamentos.php');
