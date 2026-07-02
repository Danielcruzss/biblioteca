<?php

require_once "auth.php";

requerLogin();

$usuario = usuarioAtual();

$mensagem = "";

/*
|--------------------------------------------------------------------------
| DEVOLVER LIVRO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["devolver"])) {

    $emprestimo_id = (int)$_POST["emprestimo_id"];
    $livro_id = (int)$_POST["livro_id"];

    $sql = $pdo->prepare("
        UPDATE emprestimos
        SET
            status='devolvido',
            data_devolucao=NOW()
        WHERE
            id=?
        AND
            usuario_id=?
        AND
            status='emprestado'
    ");

    $sql->execute([
        $emprestimo_id,
        $usuario["id"]
    ]);

    $sql = $pdo->prepare("
        UPDATE livros
        SET disponivel=disponivel+1
        WHERE id=?
    ");

    $sql->execute([$livro_id]);

    $mensagem = "Livro devolvido com sucesso.";

}

/*
|--------------------------------------------------------------------------
| LISTAR EMPRÉSTIMOS
|--------------------------------------------------------------------------
*/

$sql = $pdo->prepare("
SELECT
emprestimos.*,
livros.titulo,
livros.autor,
livros.categoria
FROM emprestimos

INNER JOIN livros
ON livros.id=emprestimos.livro_id

WHERE usuario_id=?

ORDER BY data_emprestimo DESC
");

$sql->execute([$usuario["id"]]);

$emprestimos = $sql->fetchAll();

$ativos = 0;
$devolvidos = 0;

foreach($emprestimos as $e){

    if($e["status"]=="emprestado"){
        $ativos++;
    }else{
        $devolvidos++;
    }

}

include "menu.php";

?>

<div class="page">

<h1 class="page-title">

Meus Empréstimos

</h1>

<?php if($mensagem): ?>

<div class="alert alert-success">

<?= $mensagem ?>

</div>

<?php endif; ?>

<div class="stats-grid">

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= count($emprestimos) ?>

</div>

<div class="stat-label">

Total

</div>

</div>

</div>

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $ativos ?>

</div>

<div class="stat-label">

Ativos

</div>

</div>

</div>

<div class="stat-card">

<div class="stat-info">

<div class="stat-num">

<?= $devolvidos ?>

</div>

<div class="stat-label">

Devolvidos

</div>

</div>

</div>

</div>

<br>

<?php if(count($emprestimos)==0): ?>

<div class="empty-state">

<h3>

Você ainda não realizou empréstimos.

</h3>

</div>

<?php else: ?>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Título</th>

<th>Autor</th>

<th>Categoria</th>

<th>Data</th>

<th>Dias</th>

<th>Status</th>

<th>Ação</th>

</tr>

</thead>

<tbody>

<?php foreach($emprestimos as $item):

$dias = floor(
    (time()-strtotime($item["data_emprestimo"]))
    /86400
);

?>

<tr>

<td><?= htmlspecialchars($item["titulo"]) ?></td>

<td><?= htmlspecialchars($item["autor"]) ?></td>

<td><?= htmlspecialchars($item["categoria"]) ?></td>

<td>

<?= date("d/m/Y",strtotime($item["data_emprestimo"])) ?>

</td>

<td>

<?= $dias ?>

</td>

<td>

<?php if($item["status"]=="emprestado"): ?>

<?php if($dias>7): ?>

<span class="tag tag-danger">

Atrasado

</span>

<?php else: ?>

<span class="tag tag-warning">

Emprestado

</span>

<?php endif; ?>

<?php else: ?>

<span class="tag tag-success">

Devolvido

</span>

<?php endif; ?>

</td>

<td>

<?php if($item["status"]=="emprestado"): ?>

<form method="POST">

<input
type="hidden"
name="emprestimo_id"
value="<?= $item["id"] ?>">

<input
type="hidden"
name="livro_id"
value="<?= $item["livro_id"] ?>">

<button
class="btn btn-success"
type="submit"
name="devolver">

Devolver

</button>

</form>

<?php else: ?>

—

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

</body>

</html>