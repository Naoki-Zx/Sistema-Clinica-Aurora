<?php
/**
 * Dashboard — cards estatísticos e últimos agendamentos.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$tituloPagina = 'Dashboard';

$totalClientes = (int) $pdo->query("SELECT COUNT(*) FROM clientes WHERE status = 'ativo'")->fetchColumn();
$totalServicos = (int) $pdo->query("SELECT COUNT(*) FROM servicos WHERE status = 'ativo'")->fetchColumn();
$totalHoje = (int) $pdo->query(
    "SELECT COUNT(*) FROM agendamentos
     WHERE DATE(data_hora) = CURDATE()
       AND status IN ('agendado', 'confirmado')"
)->fetchColumn();
$totalPendentes = (int) $pdo->query(
    "SELECT COUNT(*) FROM agendamentos WHERE status = 'agendado'"
)->fetchColumn();

$ultimos = $pdo->query(
    "SELECT a.id, a.data_hora, a.status, c.nome AS cliente, s.nome AS servico
     FROM agendamentos a
     INNER JOIN clientes c ON c.id = a.cliente_id
     INNER JOIN servicos s ON s.id = a.servico_id
     ORDER BY a.data_hora DESC
     LIMIT 8"
)->fetchAll();

$porStatus = $pdo->query(
    "SELECT status, COUNT(*) AS total
     FROM agendamentos
     GROUP BY status"
)->fetchAll();

$mapaStatus = [
    'agendado'   => 0,
    'confirmado' => 0,
    'concluido'  => 0,
    'cancelado'  => 0,
];
foreach ($porStatus as $linha) {
    $mapaStatus[$linha['status']] = (int) $linha['total'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Resumo operacional da Clínica Aurora.</p>
    </div>
    <a href="agendamento-form.php" class="btn btn-aurora">
        <i class="bi bi-plus-lg me-1"></i> Novo agendamento
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Agendamentos hoje</div>
                    <div class="fs-3 fw-semibold"><?= $totalHoje ?></div>
                </div>
                <div class="icon-wrap bg-teal"><i class="bi bi-calendar-event"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Pendentes</div>
                    <div class="fs-3 fw-semibold"><?= $totalPendentes ?></div>
                </div>
                <div class="icon-wrap bg-warning text-dark"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Clientes ativos</div>
                    <div class="fs-3 fw-semibold"><?= $totalClientes ?></div>
                </div>
                <div class="icon-wrap bg-success"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Serviços ativos</div>
                    <div class="fs-3 fw-semibold"><?= $totalServicos ?></div>
                </div>
                <div class="icon-wrap bg-gold"><i class="bi bi-scissors"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="panel p-4 h-100">
            <h2 class="h5 mb-3">Distribuição por status</h2>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between px-0">Agendados <?= badge_agendamento('agendado') ?> <strong><?= $mapaStatus['agendado'] ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0">Confirmados <?= badge_agendamento('confirmado') ?> <strong><?= $mapaStatus['confirmado'] ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0">Concluídos <?= badge_agendamento('concluido') ?> <strong><?= $mapaStatus['concluido'] ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0">Cancelados <?= badge_agendamento('cancelado') ?> <strong><?= $mapaStatus['cancelado'] ?></strong></li>
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Últimos agendamentos</h2>
                <a href="agendamentos.php" class="small">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data/hora</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$ultimos): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum agendamento cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ultimos as $item): ?>
                            <tr>
                                <td><?= e(formatar_data_hora($item['data_hora'])) ?></td>
                                <td><?= e($item['cliente']) ?></td>
                                <td><?= e($item['servico']) ?></td>
                                <td><?= badge_agendamento($item['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
