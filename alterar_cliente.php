<?php
session_start();
require 'conexao.php';

// Só ADM, Secretaria ou Cliente pode acessar
if (!in_array($_SESSION['perfil'], [1,2,4])) {
    header('Location: principal.php');
    exit();
}

$cliente = null;
$erro = "";
$sucesso = "";

// Se o formulário de busca for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca_cliente'])) {
    $busca = trim($_POST['busca_cliente']);

    // Verifica se a busca é por ID numérico ou nome
    if (is_numeric($busca)) {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM cliente WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $erro = "Cliente não encontrado!";
    }
}

// Se o formulário de alteração for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_cliente']) && isset($_POST['alterar_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $nome = trim($_POST['nome'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $telefone = trim($_POST['telefone'] ?? "");
    $endereco = trim($_POST['endereco'] ?? "");

    // Validação back-end (sem validação de telefone)
    if (!$nome || !$email || !$telefone || !$endereco) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/u', $nome)) {
        $erro = "O nome deve conter apenas letras e espaços!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } else {
        $sql = "UPDATE cliente SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco WHERE id_cliente = :id_cliente";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':telefone' => $telefone,
            ':endereco' => $endereco,
            ':id_cliente' => $id_cliente
        ])) {
            $sucesso = "Cliente alterado com sucesso!";
            // Atualiza exibição dos dados
            $cliente = ["id_cliente"=>$id_cliente, "nome"=>$nome, "email"=>$email, "telefone"=>$telefone, "endereco"=>$endereco];
        } else {
            $erro = "Erro ao alterar cliente!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Cliente</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    // Permitir apenas letras e espaços no campo nome
    function somenteLetras(e) {
        let char = String.fromCharCode(e.which);
        if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s]$/.test(char) && e.keyCode !== 8 && e.keyCode !== 9) {
            e.preventDefault();
        }
    }
    // Máscara dinâmica para telefone: (99) 99999-9999 ou (99) 9999-9999
    function formatarTelefone(e) {
        let input = e.target;
        let value = input.value.replace(/\D/g, "");
        let formatted = "";

        if(value.length === 0) {
            formatted = "";
        } else if(value.length < 3) {
            formatted = value;
        } else if(value.length < 7) {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2);
        } else if(value.length === 10) {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2,6) + "-" + value.substring(6,10);
        } else if(value.length >= 11) {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2,7) + "-" + value.substring(7,11);
        } else {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2);
        }

        input.value = formatted;
    }
    function validarEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    function validarFormulario(e) {
        var email = document.getElementById('email').value.trim();
        if (!validarEmail(email)) {
            alert('E-mail inválido!');
            document.getElementById('email').focus();
            e.preventDefault();
            return false;
        }
        return true;
    }
    window.onload = function() {
        let nome = document.getElementById('nome');
        if(nome) {
            nome.addEventListener('keypress', somenteLetras);
            nome.addEventListener('paste', function(e) {
                let paste = (e.clipboardData || window.clipboardData).getData('text');
                if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/.test(paste)) {
                    e.preventDefault();
                }
            });
        }

        let tel = document.getElementById('telefone');
        if(tel) {
            tel.addEventListener('input', formatarTelefone);
            // Não bloqueia mais digitação de letras, pois não há validação back-end
            tel.addEventListener('paste', function(e) {
                let paste = (e.clipboardData || window.clipboardData).getData('text');
                if (!/^\d+$/.test(paste.replace(/\D/g, ""))) {
                    e.preventDefault();
                }
            });
            // Aplica máscara ao carregar
            tel.value = tel.value.replace(/\D/g, '');
            formatarTelefone({target: tel});
        }

        let f = document.getElementById('form_alterar_cliente');
        if(f) f.addEventListener('submit', validarFormulario);
    }
    </script>
</head>
<body>
    <h2>Alterar Cliente</h2>
    <?php if($erro): ?><div class="error"><?= $erro ?></div><?php endif; ?>
    <?php if($sucesso): ?><div class="success"><?= $sucesso ?></div><?php endif; ?>

    <!-- Formulário para buscar cliente por ID ou Nome -->
    <form action="" method="POST">
        <label for="busca_cliente">Digite o ID ou Nome do cliente:</label>
        <input type="text" id="busca_cliente" name="busca_cliente" required>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($cliente): ?>
        <!-- Formulário para alterar cliente -->
        <form action="" method="POST" id="form_alterar_cliente" autocomplete="off">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente']) ?>">
            <input type="hidden" name="alterar_cliente" value="1">

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" maxlength="100" required
                pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+"
                title="Apenas letras e espaços"
                value="<?= htmlspecialchars($cliente['nome']) ?>">

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" maxlength="100" required
                pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
                title="Digite um e-mail válido"
                value="<?= htmlspecialchars($cliente['email']) ?>">

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" maxlength="15" required
                title="Digite o telefone"
                value="<?= htmlspecialchars($cliente['telefone']) ?>">

            <label for="endereco">Endereço:</label>
            <input type="text" id="endereco" name="endereco" maxlength="150" required
                placeholder="Digite o endereço completo"
                value="<?= htmlspecialchars($cliente['endereco']) ?>">

            <button type="submit">Salvar Alterações</button>
            <button type="button" onclick="window.location.href='principal.php'">Cancelar</button>        
        </form>
    <?php endif; ?>

    
</body>
<a href="javascript:history.back()" class="btn-voltar-top-right">
  <svg viewBox="0 0 24 24"><path d="M15.5 4l-1.42 1.41L18.67 10H4v2h14.67l-4.59 4.59L15.5 20l7-8z"/></svg>
  Voltar
</a>
</html>