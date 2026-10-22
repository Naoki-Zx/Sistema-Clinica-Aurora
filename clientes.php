<?php
/**
 * Módulo de clientes — listagem, busca e vínculo com agendamentos (FK).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$tituloPagina = 'Clientes';
$porPagina = 8;
$pagina = pagina_atual();
$busca = trim((string) ($_GET['q'] ?? ''));

$where = '';
$params = [];
if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $where = 'WHERE nome LIKE :busca_nome OR email LIKE :busca_email OR telefone LIKE :busca_telefone';
    $params = [
        ':busca_nome' => $termo,
        ':busca_email' => $termo,
        ':busca_telefone' => $termo,
    ];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM clientes $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$offset = ($pagina - 1) * $porPagina;

$listStmt = $pdo->prepare(
    "SELECT c.*,
            (SELECT COUNT(*) FROM agendamentos a WHERE a.cliente_id = c.id) AS total_agendamentos
     FROM clientes c
     $where
     ORDER BY c.nome
     LIMIT :limite OFFSET :offset"
);
foreach ($params as $chave => $valor) {
    $listStmt->bindValue($chave, $valor);
}
$listStmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$registros = $listStmt->fetchAll();

$queryBase = $busca !== '' ? 'q=' . urlencode($busca) : '';

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">Clientes</h1>
        <p class="text-muted mb-0"><?= $total ?> cadastro(s) — associados aos agendamentos.</p>
    </div>
    <a href="cliente-form.php" class="btn btn-aurora">
        <i class="bi bi-person-plus me-1"></i> Novo cliente
    </a>
</div>

<div class="panel p-3 p-md-4">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-10">
            <label class="form-label visually-hidden" for="q">Buscar</label>
            <input class="form-control" type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, e-mail ou telefone">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contato</th>
                    <th>CPF</th>
                    <th>Agendamentos</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$registros): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum cliente encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($registros as $item): ?>
                    <tr>
                        <td><?= e($item['nome']) ?></td>
                        <td>
                            <div><?= e($item['telefone']) ?></div>
                            <small class="text-muted"><?= e($item['email']) ?></small>
                        </td>
                        <td><?= e($item['cpf'] ?: '—') ?></td>
                        <td><span class="badge bg-teal"><?= (int) $item['total_agendamentos'] ?></span></td>
                        <td><?= badge_status_cadastro($item['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="cliente-form.php?id=<?= (int) $item['id'] ?>" title="Editar">
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
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php render_paginacao($total, $porPagina, $pagina, $queryBase); ?>
</div>

<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="cliente-excluir.php">
            <div class="modal-header">
                <h2 class="modal-title h5">Confirmar exclusão</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="excluirId">
                <p class="mb-0">Excluir o cliente <strong id="excluirNome"></strong>? Só é permitido se não houver agendamentos vinculados.</p>
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
