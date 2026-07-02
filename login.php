<?php
require_once "auth.php";

if (usuarioLogado()) {
    header("Location: index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $matricula = trim($_POST["matricula"]);
    $senha = $_POST["senha"];

    if (empty($matricula) || empty($senha)) {

        $erro = "Preencha todos os campos.";

    } else {

        $sql = $pdo->prepare("
            SELECT *
            FROM usuarios
            WHERE matricula = ?
        ");

        $sql->execute([$matricula]);

        $usuario = $sql->fetch();

        if ($usuario) {

            if (password_verify($senha, $usuario["senha"])) {

                $_SESSION["usuario_id"] = $usuario["id"];

                header("Location: index.php");
                exit;

            } else {

                $erro = "Senha incorreta.";

            }

        } else {

            $erro = "Usuário não encontrado.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>Login</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

    <h2>Login</h2>

    <?php if($erro): ?>

        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Matrícula</label>

        <input
            type="text"
            name="matricula"
            required
        >

        <label>Senha</label>

        <input
            type="password"
            name="senha"
            required
        >

        <button type="submit">
            Entrar
        </button>

    </form>

    <br>

    <a href="cadastro.php">
        Criar conta
    </a>

</div>

</body>

</html>