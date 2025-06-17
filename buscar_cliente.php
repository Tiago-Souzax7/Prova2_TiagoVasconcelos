<?php
session_start();
require_once 'conexao.php';

if (!in_array($_SESSION['perfil'], [1,2,3,4])){
    header('Location: principal.php');
    exit();
}

$clientes = [];
$busca = $_POST['busca'] ?? "";
$success = $_GET['msg'] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $busca){
    if (is_numeric($busca)){
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    }else{
        $sql = "SELECT * FROM cliente WHERE nome LIKE :busca_nome ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM cliente ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Buscar Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Buscar Cliente</h2>
    <?php if($success): ?><div class="success"><?=htmlspecialchars($success)?></div><?php endif; ?>
    <form method="POST" action="">
        <label for="busca">Digite o ID ou nome (opcional):</label>
        <input type="text" id="busca" name="busca" value="<?=htmlspecialchars($busca)?>">
        <button type="submit">Pesquisar</button>
    </form>
    <?php if(count($clientes)): ?>
        <div class="tabela-centro-container">
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Endereço</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                <td><?= htmlspecialchars($cliente['nome']) ?></td>
                <td><?= htmlspecialchars($cliente['email']) ?></td>
                <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                <td>
                    <a href="alterar_cliente.php?id=<?= urlencode($cliente['id_cliente']) ?>" class="btn-acao">Alterar</a>
                    <form method="get" action="excluir_cliente.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">
                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
    <?php else: ?><p>Nenhum cliente encontrado.</p><?php endif; ?>
        <a href="javascript:history.back()" class="btn-voltar-top-right">
  <svg viewBox="0 0 24 24"><path d="M15.5 4l-1.42 1.41L18.67 10H4v2h14.67l-4.59 4.59L15.5 20l7-8z"/></svg>
  Voltar
</a>
</body>
</html>