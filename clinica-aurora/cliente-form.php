<?php
/** Cadastro e edição de clientes. */
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$registro = [
    'nome' => '',
    'telefone' => '',
    'email' => '',
    'cpf' => '',
    'data_nascimento' => '',
    'status' => 'ativo',
    'observacoes' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $encontrado = $stmt->fetch();
    if (!$encontrado) {
        flash_set('danger', 'Cliente não encontrado.');
        redirect('clientes.php');
    }
    $registro = $encontrado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $cpf = trim((string) ($_POST['cpf'] ?? ''));
    $dataNascimento = trim((string) ($_POST['data_nascimento'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'ativo');
    $observacoes = trim((string) ($_POST['observacoes'] ?? ''));

    $erros = [];
    if ($nome === '') {
        $erros[] = 'Informe o nome.';
    }
    if ($telefone === '') {
        $erros[] = 'Informe o telefone.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (!in_array($status, ['ativo', 'inativo'], true)) {
        $erros[] = 'Status inválido.';
    }

    $registro = [
        'nome' => $nome,
        'telefone' => $telefone,
        'email' => $email,
        'cpf' => $cpf,
        'data_nascimento' => $dataNascimento,
        'status' => $status,
        'observacoes' => $observacoes,
    ];

    if (!$erros) {
        $payload = [
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':email' => $email,
            ':cpf' => $cpf !== '' ? $cpf : null,
            ':data_nascimento' => $dataNascimento !== '' ? $dataNascimento : null,
            ':status' => $status,
            ':observacoes' => $observacoes !== '' ? $observacoes : null,
        ];

        try {
            if ($id > 0) {
                $sql = 'UPDATE clientes
                        SET nome = :nome, telefone = :telefone, email = :email, cpf = :cpf,
                            data_nascimento = :data_nascimento, status = :status, observacoes = :observacoes
                        WHERE id = :id';
                $payload[':id'] = $id;
                $pdo->prepare($sql)->execute($payload);
                flash_set('success', 'Cliente atualizado com sucesso.');
            } else {
                $sql = 'INSERT INTO clientes (nome, telefone, email, cpf, data_nascimento, status, observacoes)
                        VALUES (:nome, :telefone, :email, :cpf, :data_nascimento, :status, :observacoes)';
                $pdo->prepare($sql)->execute($payload);
                flash_set('success', 'Cliente cadastrado com sucesso.');
            }
            redirect('clientes.php');
        } catch (PDOException $e) {
            flash_set('danger', 'Não foi possível salvar. E-mail ou CPF já cadastrado.');
        }
    } else {
        flash_set('danger', implode(' ', $erros));
    }
}

$tituloPagina = $id > 0 ? 'Editar cliente' : 'Novo cliente';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><?= e($tituloPagina) ?></h1>
    <p class="text-muted mb-0">Clientes ativos aparecem no formulário de agendamento.</p>
</div>

<div class="panel p-3 p-md-4">
    <form class="row g-3 needs-validation" method="post" novalidate>
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <div class="col-md-6">
            <label class="form-label" for="nome">Nome completo *</label>
            <input class="form-control" id="nome" name="nome" value="<?= e((string) $registro['nome']) ?>" required>
            <div class="invalid-feedback">Informe o nome.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="telefone">Telefone *</label>
            <input class="form-control" id="telefone" name="telefone" value="<?= e((string) $registro['telefone']) ?>" required>
            <div class="invalid-feedback">Informe o telefone.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">E-mail *</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e((string) $registro['email']) ?>" required>
            <div class="invalid-feedback">Informe um e-mail válido.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="cpf">CPF</label>
            <input class="form-control" id="cpf" name="cpf" value="<?= e((string) $registro['cpf']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="data_nascimento">Data de nascimento</label>
            <input class="form-control" type="date" id="data_nascimento" name="data_nascimento" value="<?= e((string) $registro['data_nascimento']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="status">Status *</label>
            <select class="form-select" id="status" name="status" required>
                <option value="ativo" <?= $registro['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $registro['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="observacoes">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= e((string) $registro['observacoes']) ?></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-aurora" type="submit"><i class="bi bi-check-lg me-1"></i> Salvar</button>
            <a class="btn btn-outline-secondary" href="clientes.php">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
