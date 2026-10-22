<?php
/**
 * Funções utilitárias compartilhadas (escape, flash, badges, paginação).
 */

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function badge_agendamento(string $status): string
{
    $map = [
        'agendado'   => ['bg-warning text-dark', 'Agendado'],
        'confirmado' => ['bg-success', 'Confirmado'],
        'concluido'  => ['bg-secondary', 'Concluído'],
        'cancelado'  => ['bg-danger', 'Cancelado'],
    ];
    [$class, $label] = $map[$status] ?? ['bg-light text-dark', $status];
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function badge_status_cadastro(string $status): string
{
    if ($status === 'ativo') {
        return '<span class="badge bg-success">Ativo</span>';
    }
    return '<span class="badge bg-danger">Inativo</span>';
}

function formatar_data_hora(?string $datetime): string
{
    if (!$datetime) {
        return '—';
    }
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    return $dt ? $dt->format('d/m/Y H:i') : e($datetime);
}

function formatar_moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function pagina_atual(): int
{
    $p = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
    return ($p !== false && $p !== null && $p > 0) ? $p : 1;
}

function render_paginacao(int $total, int $porPagina, int $pagina, string $baseQuery = ''): void
{
    $paginas = (int) ceil($total / max($porPagina, 1));
    if ($paginas <= 1) {
        return;
    }

    $qs = $baseQuery !== '' ? $baseQuery . '&' : '';
    echo '<nav class="mt-3" aria-label="Paginação"><ul class="pagination pagination-sm flex-wrap mb-0">';

    for ($i = 1; $i <= $paginas; $i++) {
        $active = $i === $pagina ? ' active' : '';
        echo '<li class="page-item' . $active . '">';
        echo '<a class="page-link" href="?' . e($qs) . 'pagina=' . $i . '">' . $i . '</a>';
        echo '</li>';
    }

    echo '</ul></nav>';
}
