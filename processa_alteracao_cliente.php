<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Verifica se o formulário foi enviado corretamente
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['id_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    $sql = "UPDATE cliente SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco WHERE id_cliente = :id_cliente";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Cliente alterado com sucesso!'); window.location.href='alterar_cliente.php';</script>";
    } else {
        echo "<script>alert('Erro ao alterar cliente!'); window.location.href='alterar_cliente.php';</script>";
    }
} else {
    echo "<script>alert('Requisição inválida!'); window.location.href='alterar_cliente.php';</script>";
}
?>