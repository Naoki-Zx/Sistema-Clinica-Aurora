<?php
/** Layout: sidebar desktop + offcanvas hambúrguer no mobile. */
declare(strict_types=1);

$paginaAtual = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

function nav_ativo(string $arquivo, string $paginaAtual): string
{
    return $arquivo === $paginaAtual ? 'active' : '';
}

$itensMenu = [
    ['index.php', 'bi-speedometer2', 'Dashboard'],
    ['agendamentos.php', 'bi-calendar-check', 'Agendamentos'],
    ['clientes.php', 'bi-people', 'Clientes'],
    ['servicos.php', 'bi-scissors', 'Serviços'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina ?? 'Clínica Aurora') ?> · Aurora Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark aurora-topbar d-lg-none sticky-top">
    <div class="container-fluid">
        <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMobile" aria-controls="menuMobile">
            <i class="bi bi-list" aria-hidden="true"></i>
            <span class="visually-hidden">Abrir menu</span>
        </button>
        <a class="navbar-brand ms-2 mb-0" href="index.php">Aurora Studio</a>
    </div>
</nav>

<div class="offcanvas offcanvas-start aurora-sidebar" tabindex="-1" id="menuMobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white">Aurora Studio</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column aurora-nav">
            <?php foreach ($itensMenu as [$href, $icone, $label]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= nav_ativo($href, $paginaAtual) ?>" href="<?= e($href) ?>">
                        <i class="bi <?= e($icone) ?> me-2"></i><?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 d-none d-lg-flex flex-column aurora-sidebar min-vh-100 p-0">
            <div class="brand-block px-3 py-4">
                <div class="brand-mark">A</div>
                <div>
                    <strong class="d-block">Aurora Studio</strong>
                    <small>Clínica &amp; Salão</small>
                </div>
            </div>
            <ul class="nav flex-column aurora-nav px-2">
                <?php foreach ($itensMenu as [$href, $icone, $label]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_ativo($href, $paginaAtual) ?>" href="<?= e($href) ?>">
                            <i class="bi <?= e($icone) ?> me-2"></i><?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-auto px-3 py-3 small text-white-50">
                Painel administrativo
            </div>
        </aside>

        <main class="col-lg-10 ms-sm-auto px-3 px-md-4 py-4 aurora-main">
            <?php $flash = flash_get(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'danger') ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>
