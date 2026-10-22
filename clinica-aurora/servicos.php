<?php
/**
 * Catálogo de serviços — usado como FK nos agendamentos.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$tituloPagina = 'Serviços';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $duracao = filter_input(INPUT_POST, 'duracao_minutos', FILTER_VALIDATE_INT);
    $preco = str_replace(',', '.', trim((string) ($_POST['preco'] ?? '0')));
    $status = (string) ($_POST['status'] ?? 'ativo');

    if ($nome === '' || !$duracao || !is_numeric($preco) || !in_array($status, ['ativo', 'inativo'], true)) {
        flash_set('danger', 'Preencha nome, duração, preço e status corretamente.');
        redirect('servicos.php');
    }

    $payload = [
        ':nome' => $nome,
        ':duracao_minutos' => $duracao,
        ':preco' => $preco,
        ':status' => $status,
    ];

    if ($id > 0) {
        $payload[':id'] = $id;
        $pdo->prepare(
            'UPDATE servicos SET nome = :nome, duracao_minutos = :duracao_minutos, preco = :preco, status = :status WHERE id = :id'
        )->execute($payload);
        flash_set('success', 'Serviço atualizado.');
    } else {
        $pdo->prepare(
            'INSERT INTO servicos (nome, duracao_minutos, preco, status) VALUES (:nome, :duracao_minutos, :preco, :status)'
        )->execute($payload);
        flash_set('success', 'Serviço cadastrado.');
    }
    redirect('servicos.php');
}

$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$edicao = [
    'id' => 0,
    'nome' => '',
    'duracao_minutos' => 30,
    'preco' => '',
    'status' => 'ativo',
];

if ($editarId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM servicos WHERE id = :id');
    $stmt->execute([':id' => $editarId]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $edicao = $encontrado;
    }
}

$servicos = $pdo->query('SELECT * FROM servicos ORDER BY nome')->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Serviços</h1>
    <p class="text-muted mb-0">Catálogo associado aos agendamentos por chave estrangeira.</p>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="panel p-3 p-md-4">
            <h2 class="h5 mb-3"><?= $edicao['id'] ? 'Editar serviço' : 'Novo serviço' ?></h2>
            <form class="needs-validation" method="post" novalidate>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="<?= (int) $edicao['id'] ?>">
                <div class="mb-3">
                    <label class="form-label" for="nome">Nome *</label>
                    <input class="form-control" id="nome" name="nome" value="<?= e((string) $edicao['nome']) ?>" required>
                    <div class="invalid-feedback">Informe o nome.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="duracao_minutos">Duração (min) *</label>
                    <input class="form-control" type="number" min="10" id="duracao_minutos" name="duracao_minutos" value="<?= e((string) $edicao['duracao_minutos']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="preco">Preço *</label>
                    <input class="form-control" id="preco" name="preco" value="<?= e((string) $edicao['preco']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status *</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="ativo" <?= $edicao['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="inativo" <?= $edicao['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
                <button class="btn btn-aurora" type="submit"><i class="bi bi-check-lg me-1"></i> Salvar</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Duração</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($servicos as $item): ?>
                        <tr>
                            <td><?= e($item['nome']) ?></td>
                            <td><?= (int) $item['duracao_minutos'] ?> min</td>
                            <td><?= e(formatar_moeda($item['preco'])) ?></td>
                            <td><?= badge_status_cadastro($item['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="servicos.php?editar=<?= (int) $item['id'] ?>" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir"
                                        data-id="<?= (int) $item['id'] ?>"
                                        data-nome="<?= e($item['nome']) ?>"
                                        title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="servico-excluir.php">
            <div class="modal-header">
                <h2 class="modal-title h5">Confirmar exclusão</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="excluirId">
                <p class="mb-0">Excluir o serviço <strong id="excluirNome"></strong>? Só é permitido se não houver agendamentos vinculados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Excluir</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('modalExcluir')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('excluirId').value = button.getAttribute('data-id');
    document.getElementById('excluirNome').textContent = button.getAttribute('data-nome');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
