<?php
/**
 * Cadastro e edição de agendamentos — validação HTML5 + prepared statements.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$registro = [
    'cliente_id'  => '',
    'servico_id'  => '',
    'data_hora'   => '',
    'status'      => 'agendado',
    'observacoes' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM agendamentos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $encontrado = $stmt->fetch();
    if (!$encontrado) {
        flash_set('danger', 'Agendamento não encontrado.');
        redirect('agendamentos.php');
    }
    $registro = $encontrado;
}

$clientes = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome")->fetchAll();
$servicos = $pdo->query("SELECT id, nome, preco FROM servicos WHERE status = 'ativo' ORDER BY nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $clienteId = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $servicoId = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
    $dataHora = trim((string) ($_POST['data_hora'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'agendado');
    $observacoes = trim((string) ($_POST['observacoes'] ?? ''));

    $statusValidos = ['agendado', 'confirmado', 'concluido', 'cancelado'];
    $erros = [];

    if (!$clienteId) {
        $erros[] = 'Selecione um cliente.';
    }
    if (!$servicoId) {
        $erros[] = 'Selecione um serviço.';
    }
    if ($dataHora === '') {
        $erros[] = 'Informe data e hora.';
    }
    if (!in_array($status, $statusValidos, true)) {
        $erros[] = 'Status inválido.';
    }

    $registro = [
        'cliente_id'  => $clienteId,
        'servico_id'  => $servicoId,
        'data_hora'   => $dataHora,
        'status'      => $status,
        'observacoes' => $observacoes,
    ];

    if (!$erros) {
        $dataSql = str_replace('T', ' ', $dataHora);
        if (strlen($dataSql) === 16) {
            $dataSql .= ':00';
        }

        if ($id > 0) {
            $sql = 'UPDATE agendamentos
                    SET cliente_id = :cliente_id, servico_id = :servico_id,
                        data_hora = :data_hora, status = :status, observacoes = :observacoes
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cliente_id'  => $clienteId,
                ':servico_id'  => $servicoId,
                ':data_hora'   => $dataSql,
                ':status'      => $status,
                ':observacoes' => $observacoes !== '' ? $observacoes : null,
                ':id'          => $id,
            ]);
            flash_set('success', 'Agendamento atualizado com sucesso.');
        } else {
            $sql = 'INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status, observacoes)
                    VALUES (:cliente_id, :servico_id, :data_hora, :status, :observacoes)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cliente_id'  => $clienteId,
                ':servico_id'  => $servicoId,
                ':data_hora'   => $dataSql,
                ':status'      => $status,
                ':observacoes' => $observacoes !== '' ? $observacoes : null,
            ]);
            flash_set('success', 'Agendamento cadastrado com sucesso.');
        }

        redirect('agendamentos.php');
    }

    flash_set('danger', implode(' ', $erros));
}

$tituloPagina = $id > 0 ? 'Editar agendamento' : 'Novo agendamento';
$dataInput = $registro['data_hora']
    ? date('Y-m-d\TH:i', strtotime((string) $registro['data_hora']))
    : '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($tituloPagina) ?></h1>
    <p class="text-muted mb-0">Campos obrigatórios marcados com *.</p>
</div>

<div class="panel p-3 p-md-4">
    <form class="row g-3 needs-validation" method="post" novalidate>
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <div class="col-md-6">
            <label class="form-label" for="cliente_id">Cliente *</label>
            <select class="form-select" id="cliente_id" name="cliente_id" required>
                <option value="">Selecione</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= (int) $cliente['id'] ?>" <?= (string) $registro['cliente_id'] === (string) $cliente['id'] ? 'selected' : '' ?>>
                        <?= e($cliente['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecione o cliente.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="servico_id">Serviço *</label>
            <select class="form-select" id="servico_id" name="servico_id" required>
                <option value="">Selecione</option>
                <?php foreach ($servicos as $servico): ?>
                    <option value="<?= (int) $servico['id'] ?>" <?= (string) $registro['servico_id'] === (string) $servico['id'] ? 'selected' : '' ?>>
                        <?= e($servico['nome']) ?> — <?= e(formatar_moeda($servico['preco'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecione o serviço.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="data_hora">Data e hora *</label>
            <input class="form-control" type="datetime-local" id="data_hora" name="data_hora" value="<?= e($dataInput) ?>" required>
            <div class="invalid-feedback">Informe data e hora.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="status">Status *</label>
            <select class="form-select" id="status" name="status" required>
                <?php foreach (['agendado', 'confirmado', 'concluido', 'cancelado'] as $st): ?>
                    <option value="<?= e($st) ?>" <?= $registro['status'] === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label" for="observacoes">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= e((string) $registro['observacoes']) ?></textarea>
        </div>

        <div class="col-12 d-flex gap-2">
            <button class="btn btn-aurora" type="submit"><i class="bi bi-check-lg me-1"></i> Salvar</button>
            <a class="btn btn-outline-secondary" href="agendamentos.php">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
