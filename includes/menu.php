<?php
require_once __DIR__ . '/auth.php';

// Detecta se estamos dentro da pasta admin
$base = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';

$usuario = usuarioAtual();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Biblioteca Universitária</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= $base ?>css/style.css">

</head>

<body>

<nav class="navbar">

    <a href="<?= $base ?>index.php" class="navbar-brand">

        <div class="logo-icon">

            <svg viewBox="0 0 40 40" fill="none">

                <rect width="40" height="40" rx="10" fill="#ffffff22"/>

                <path d="M10 30V13a2 2 0 012-2h7v19H12a2 2 0 01-2-2z" fill="#fff"/>

                <path d="M21 11h7a2 2 0 012 2v17a2 2 0 01-2 2h-7V11z" fill="#ffffffaa"/>

            </svg>

        </div>

        <span class="logo-text">
            Biblioteca
            <span class="logo-sub">Universitária</span>
        </span>

    </a>

    <div class="navbar-links">

        <a href="<?= $base ?>index.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php' ? 'active':'' ?>">
            Início
        </a>

        <a href="<?= $base ?>acervo.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF'])=='acervo.php' ? 'active':'' ?>">
            Acervo
        </a>

        <?php if(usuarioLogado()): ?>

            <a href="<?= $base ?>emprestimos.php"
               class="nav-link <?= basename($_SERVER['PHP_SELF'])=='emprestimos.php' ? 'active':'' ?>">
                Meus Empréstimos
            </a>

        <?php endif; ?>

        <?php if(ehAdmin()): ?>

            <a href="<?= $base ?>admin.php"
               class="nav-link <?= basename($_SERVER['PHP_SELF'])=='admin.php' ? 'active':'' ?>">
                Administração
            </a>

        <?php endif; ?>

    </div>

    <div class="navbar-user">

        <?php if(usuarioLogado()): ?>

            <span class="user-greeting">

                Olá,
                <strong><?= htmlspecialchars($usuario["nome"]) ?></strong>

            </span>

            <?php if(ehAdmin()): ?>

                <span class="badge-admin">

                    Admin

                </span>

            <?php endif; ?>

            <a href="<?= $base ?>logout.php" class="btn-logout">

                Sair

            </a>

        <?php else: ?>

            <a href="<?= $base ?>login.php" class="btn-entrar">

                Entrar

            </a>

        <?php endif; ?>

    </div>

</nav>