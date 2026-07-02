<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "conexao.php";

/**
 * Verifica se o usuário está logado
 */
function usuarioLogado()
{
    return isset($_SESSION["usuario_id"]);
}

/**
 * Redireciona caso não esteja logado
 */
function requerLogin()
{
    if (!usuarioLogado()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Retorna os dados do usuário logado
 */
function usuarioAtual()
{
    global $pdo;

    if (!usuarioLogado()) {
        return null;
    }

    $sql = $pdo->prepare("
        SELECT *
        FROM usuarios
        WHERE id = ?
    ");

    $sql->execute([$_SESSION["usuario_id"]]);

    return $sql->fetch();
}

/**
 * Verifica se é administrador
 */
function ehAdmin()
{
    $usuario = usuarioAtual();

    return $usuario && $usuario["tipo"] === "admin";
}

/**
 * Exige administrador
 */
function requerAdmin()
{
    requerLogin();

    if (!ehAdmin()) {

        header("Location: index.php");

        exit;
    }
}
?>