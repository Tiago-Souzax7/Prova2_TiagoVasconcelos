<?php
session_start();
require 'conexao.php';

$success = $error = "";
$cliente = ["nome"=>"", "email"=>"", "telefone"=>"", "endereco"=>""];
$id_cliente = $_GET['id'] ?? null;

// Só ADM, Secretaria ou Cliente pode acessar
if (!in_array($_SESSION['perfil'], [1,2,4])) {
    header('Location: principal.php');
    exit();
}

// Buscar dados do cliente para exibir no formulário
if ($id_cliente && is_numeric($id_cliente) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $sql = "SELECT * FROM cliente WHERE id_cliente = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        $error = "Cliente não encontrado!";
        $cliente = ["nome"=>"", "email"=>"", "telefone"=>"", "endereco"=>""];
    }
}

// Atualizar dados do cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_cliente'])) {
    $id_cliente = $_POST['id_cliente'];
    $nome = trim($_POST['nome'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? "")); // remove máscara
    $endereco = trim($_POST['endereco'] ?? "");

    // Validação back-end
    if (!$nome || !$email || !$telefone || !$endereco) {
        $error = "Todos os campos são obrigatórios!";
    } elseif (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/u', $nome)) {
        $error = "O nome deve conter apenas letras e espaços!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "E-mail inválido!";
    } elseif (!preg_match('/^\d{10,15}$/', $telefone)) {
        $error = "Telefone deve conter apenas números (10-15 dígitos)!";
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
            $success = "Dados alterados com sucesso!";
            $cliente = ["nome"=>$nome, "email"=>$email, "telefone"=>$telefone, "endereco"=>$endereco];
        } else {
            $error = "Erro ao alterar!";
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
    // Permitir apenas números no campo telefone
    function somenteNumeros(e) {
        let char = String.fromCharCode(e.which);
        if (!/^\d$/.test(char) && e.keyCode !== 8 && e.keyCode !== 9) {
            e.preventDefault();
        }
    }
    // Máscara dinâmica para telefone: (99) 99999-9999 ou (99) 9999-9999
    function formatarTelefone(e) {
        let input = e.target;
        let value = input.value.replace(/\D/g, "");
        let formatted = "";

        if(value.length < 3) {
            formatted = value;
        } else if(value.length < 7) {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2);
        } else if(value.length <= 10) {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2,6) + "-" + value.substring(6,10);
        } else {
            formatted = "(" + value.substring(0,2) + ") " + value.substring(2,7) + "-" + value.substring(7,11);
        }

        input.value = formatted;
    }
    // Validar e-mail no frontend
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
        nome.addEventListener('keypress', somenteLetras);
        nome.addEventListener('paste', function(e) {
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/.test(paste)) {
                e.preventDefault();
            }
        });

        let tel = document.getElementById('telefone');
        tel.addEventListener('input', formatarTelefone);
        tel.addEventListener('keypress', somenteNumeros);
        tel.addEventListener('paste', function(e) {
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^\d+$/.test(paste.replace(/\D/g, ""))) {
                e.preventDefault();
            }
        });

        document.getElementById('form_cliente').addEventListener('submit', validarFormulario);
    }
    </script>
</head>
<body>
    <h2>Cadastro de Clientes</h2>
    <?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST" action="" autocomplete="off" id="form_cliente">
        <input type="hidden" name="id_cliente" value="<?=htmlspecialchars($id_cliente)?>">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" maxlength="100" required
               pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+"
               title="Apenas letras e espaços"
               value="<?=htmlspecialchars($cliente['nome'] ?? "")?>">

        <label for="email">E-mail:</label>
        <input type="email" name="email" id="email" maxlength="100" required
               pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
               title="Digite um e-mail válido"
               value="<?=htmlspecialchars($cliente['email'] ?? "")?>">

        <label for="telefone">Telefone:</label>
        <input type="text" name="telefone" id="telefone" maxlength="15" required
               pattern="\d{10,15}"
               title="Apenas números (10-15 dígitos)"
               placeholder="(47) 98765-4321"
               value="<?php
                   // Mostra o telefone formatado se existir
                   $tel = preg_replace('/\D/', '', $cliente['telefone'] ?? "");
                   if(strlen($tel) === 11) {
                       echo '('.substr($tel,0,2).') '.substr($tel,2,5).'-'.substr($tel,7,4);
                   } elseif(strlen($tel) === 10) {
                       echo '('.substr($tel,0,2).') '.substr($tel,2,4).'-'.substr($tel,6,4);
                   } else {
                       echo htmlspecialchars($cliente['telefone'] ?? "");
                   }
               ?>">

        <label for="endereco">Endereço:</label>
        <input type="text" name="endereco" id="endereco" maxlength="150" required
               placeholder="Digite o endereço completo"
               value="">

        <button type="submit">Alterar</button>
        <button type="reset" onclick="setTimeout(()=>{document.getElementById('nome').focus()},100)">Limpar</button>
    </form>
    <a href="javascript:history.back()" class="btn-voltar-top-right">
  <svg viewBox="0 0 24 24"><path d="M15.5 4l-1.42 1.41L18.67 10H4v2h14.67l-4.59 4.59L15.5 20l7-8z"/></svg>
  Voltar
</a>
</body>
</html>