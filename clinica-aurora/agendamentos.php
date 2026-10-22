<?php
/**
 * Listagem de agendamentos com busca por cliente/serviço e paginação.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$tituloPagina = 'Agendamentos';
$porPagina = 8;
$pagina = pagina_atual();
$busca = trim((string) ($_GET['q'] ?? ''));
$filtroStatus = trim((string) ($_GET['status'] ?? ''));

$where = [];
$params = [];

if ($busca !== '') {
    $termo = '%' . $busca . '%';
    $where[] = '(c.nome LIKE :busca_cliente OR s.nome LIKE :busca_servico)';
    $params[':busca_cliente'] = $termo;
    $params[':busca_servico'] = $termo;
}

$statusValidos = ['agendado', 'confirmado', 'concluido', 'cancelado'];
if (in_array($filtroStatus, $statusValidos, true)) {
    $where[] = 'a.status = :status';
    $params[':status'] = $filtroStatus;
}

$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM agendamentos a
     INNER JOIN clientes c ON c.id = a.cliente_id
     INNER JOIN servicos s ON s.id = a.servico_id
     $sqlWhere"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$offset = ($pagina - 1) * $porPagina;

$listStmt = $pdo->prepare(
    "SELECT a.id, a.data_hora, a.status, a.observacoes,
            c.nome AS cliente, c.telefone,
            s.nome AS servico, s.preco
     FROM agendamentos a
     INNER JOIN clientes c ON c.id = a.cliente_id
     INNER JOIN servicos s ON s.id = a.servico_id
     $sqlWhere
     ORDER BY a.data_hora DESC
     LIMIT :limite OFFSET :offset"
);

foreach ($params as $chave => $valor) {
    $listStmt->bindValue($chave, $valor);
}
$listStmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$registros = $listStmt->fetchAll();

$queryBase = http_build_query(array_filter([
    'q' => $busca !== '' ? $busca : null,
    'status' => $filtroStatus !== '' ? $filtroStatus : null,
]));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">Agendamentos</h1>
        <p class="text-muted mb-0"><?= $total ?> registro(s) encontrado(s).</p>
    </div>
    <a href="agendamento-form.php" class="btn btn-aurora">
        <i class="bi bi-plus-lg me-1"></i> Novo agendamento
    </a>
</div>

<div class="panel p-3 p-md-4">
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-6">
            <label class="form-label visually-hidden" for="q">Buscar</label>
            <input class="form-control" type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Buscar por cliente ou serviço">
        </div>
        <div class="col-md-4">
            <label class="form-label visually-hidden" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">Todos os status</option>
                <?php foreach ($statusValidos as $st): ?>
                    <option value="<?= e($st) ?>" <?= $filtroStatus === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Data/hora</th>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$registros): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum agendamento encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($registros as $item): ?>
                    <tr>
                        <td><?= e(formatar_data_hora($item['data_hora'])) ?></td>
                        <td>
                            <div><?= e($item['cliente']) ?></div>
                            <small class="text-muted"><?= e($item['telefone']) ?></small>
                        </td>
                        <td><?= e($item['servico']) ?></td>
                        <td><?= e(formatar_moeda($item['preco'])) ?></td>
                        <td><?= badge_agendamento($item['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="agendamento-form.php?id=<?= (int) $item['id'] ?>" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalExcluir"
                                    data-id="<?= (int) $item['id'] ?>"
                                    data-nome="<?= e($item['cliente'] . ' — ' . formatar_data_hora($item['data_hora'])) ?>"
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
        <form class="modal-content" method="post" action="agendamento-excluir.php">
            <div class="modal-header">
                <h2 class="modal-title h5">Confirmar exclusão</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="excluirId">
                <p class="mb-0">Deseja realmente excluir o agendamento de <strong id="excluirNome"></strong>?</p>
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
