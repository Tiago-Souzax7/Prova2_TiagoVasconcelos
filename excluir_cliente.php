<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    header('Location: principal.php');
    exit();
}

// Inicializa variáveis para mensagens e clientes
$clientes = [];
$success = $error = "";

// Se um ID for passado via GET, tenta excluir o cliente
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cliente = $_GET['id'];

    // Exclui o cliente do banco de dados
    $sql = "DELETE FROM cliente WHERE id_cliente = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $success = "Cliente excluído com sucesso!";
    } else {
        $error = "Erro ao excluir cliente!";
    }
}

// Busca todos os clientes cadastrados em ordem alfabética
$sql = "SELECT * FROM cliente ORDER BY nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Excluir Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Excluir Cliente</h2>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($clientes)): ?>
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
                    <form method="get" action="excluir_cliente.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">
                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
    <?php else: ?>
        <p>Nenhum cliente encontrado.</p>
    <?php endif; ?>

    <a href="javascript:history.back()" class="btn-voltar-top-right">
  <svg viewBox="0 0 24 24"><path d="M15.5 4l-1.42 1.41L18.67 10H4v2h14.67l-4.59 4.59L15.5 20l7-8z"/></svg>
  Voltar
</a>
</body>
</html>