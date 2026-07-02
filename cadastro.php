<?php
require_once "conexao.php";
session_start();

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $matricula = trim($_POST["matricula"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $confirmar = $_POST["confirmar"];

    // Validação
    if (
        empty($nome) ||
        empty($matricula) ||
        empty($email) ||
        empty($senha) ||
        empty($confirmar)
    ) {

        $erro = "Preencha todos os campos.";

    } elseif ($senha != $confirmar) {

        $erro = "As senhas não coincidem.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve possuir pelo menos 6 caracteres.";

    } else {

        // Verifica matrícula
        $sql = $pdo->prepare("
            SELECT id
            FROM usuarios
            WHERE matricula = ?
        ");

        $sql->execute([$matricula]);

        if ($sql->fetch()) {

            $erro = "Já existe um usuário com essa matrícula.";

        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO usuarios
                (
                    nome,
                    matricula,
                    email,
                    senha,
                    tipo
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    'usuario'
                )
            ");

            $insert->execute([
                $nome,
                $matricula,
                $email,
                $senhaHash
            ]);

            header("Location: login.php?cadastro=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Cadastro</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

<h2>Criar Conta</h2>

<?php if($erro): ?>

<div class="erro">

<?= htmlspecialchars($erro) ?>

</div>

<?php endif; ?>

<form method="POST">

<label>Nome</label>

<input
type="text"
name="nome"
required>

<label>Matrícula</label>

<input
type="text"
name="matricula"
required>

<label>E-mail</label>

<input
type="email"
name="email"
required>

<label>Senha</label>

<input
type="password"
name="senha"
required>

<label>Confirmar Senha</label>

<input
type="password"
name="confirmar"
required>

<button type="submit">

Cadastrar

</button>

</form>

<br>

<a href="login.php">

Já possui conta? Entrar

</a>

</div>

</body>

</html>