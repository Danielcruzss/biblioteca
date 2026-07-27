<?php

require_once "auth.php";

requerLogin();

$usuario = usuarioAtual();

include "includes/menu.php";

/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS
|--------------------------------------------------------------------------
*/

$totalLivros = $pdo->query("
SELECT COUNT(*) FROM livros
")->fetchColumn();

$totalUsuarios = $pdo->query("
SELECT COUNT(*) FROM usuarios
")->fetchColumn();

$emprestados = $pdo->query("
SELECT COUNT(*) FROM emprestimos
WHERE status='emprestado'
")->fetchColumn();

$devolvidos = $pdo->query("
SELECT COUNT(*) FROM emprestimos
WHERE status='devolvido'
")->fetchColumn();

$disponiveis = $pdo->query("
SELECT SUM(disponivel)
FROM livros
")->fetchColumn();

$ultimos = $pdo->query("
SELECT
usuarios.nome,
livros.titulo,
emprestimos.data_emprestimo,
emprestimos.status
FROM emprestimos

INNER JOIN usuarios
ON usuarios.id=emprestimos.usuario_id

INNER JOIN livros
ON livros.id=emprestimos.livro_id

ORDER BY emprestimos.data_emprestimo DESC

LIMIT 5
")->fetchAll();

?>

<div class="page">

<h1 class="page-title">

Bem-vindo,
<span><?= htmlspecialchars($usuario["nome"]) ?></span>

</h1>

<p>

Matrícula:
<strong><?= htmlspecialchars($usuario["matricula"]) ?></strong>

</p>

<br>

<div class="stats-grid">

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $totalLivros ?>

</div>

<div class="stat-label">

Livros cadastrados

</div>

</div>

</div>

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $disponiveis ?: 0 ?>

</div>

<div class="stat-label">

Livros disponíveis

</div>

</div>

</div>

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $emprestados ?>

</div>

<div class="stat-label">

Empréstimos ativos

</div>

</div>

</div>

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $totalUsuarios ?>

</div>

<div class="stat-label">

Usuários

</div>

</div>

</div>

</div>

<br>

<div class="table-wrap">

<table>

<thead>

<tr>

<th colspan="4">

Últimos empréstimos

</th>

</tr>

<tr>

<th>Usuário</th>

<th>Livro</th>

<th>Data</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($ultimos as $item): ?>

<tr>

<td>

<?= htmlspecialchars($item["nome"]) ?>

</td>

<td>

<?= htmlspecialchars($item["titulo"]) ?>

</td>

<td>

<?= date("d/m/Y H:i",strtotime($item["data_emprestimo"])) ?>

</td>

<td>

<?php if($item["status"]=="emprestado"): ?>

<span class="tag tag-warning">

Emprestado

</span>

<?php else: ?>

<span class="tag tag-success">

Devolvido

</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<br>

<div class="form-card">

<h2>Acesso rápido</h2>

<div style="display:flex;gap:15px;flex-wrap:wrap;">

<a class="btn btn-primary" href="acervo.php">

Ver Acervo

</a>

<a class="btn btn-success" href="emprestimos.php">

Meus Empréstimos

</a>

<?php if(ehAdmin()): ?>

<a class="btn btn-warning" href="admin.php">

Gerenciar Livros

</a>

<a class="btn btn-outline" href="usuarios.php">

Gerenciar Usuários

</a>

<?php endif; ?>

</div>

</div>

<?php if(ehAdmin()): ?>

<br>

<div class="form-card">

<h2>Resumo do Sistema</h2>

<p><strong>Total de empréstimos:</strong> <?= $emprestados + $devolvidos ?></p>

<p><strong>Empréstimos ativos:</strong> <?= $emprestados ?></p>

<p><strong>Empréstimos devolvidos:</strong> <?= $devolvidos ?></p>

<p><strong>Total de usuários:</strong> <?= $totalUsuarios ?></p>

<p><strong>Total de livros:</strong> <?= $totalLivros ?></p>

</div>

<?php endif; ?>

</div>

</body>

</html>