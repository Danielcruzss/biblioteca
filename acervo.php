<?php

require_once "auth.php";

requerLogin();

$usuario = usuarioAtual();

$mensagem = "";
$erro = "";

// EMPRESTAR LIVRO

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["emprestar"])) {

    $livro_id = (int)$_POST["livro_id"];

    try {
        $sql = $pdo->prepare("SELECT * FROM livros WHERE id=?");
        $sql->execute([$livro_id]);
        $livro = $sql->fetch();

        if (!$livro) {
            $erro = "Livro não encontrado.";
        } elseif ($livro["disponivel"] <= 0) {
            $erro = "Livro indisponível.";
        } else {
            
            // Verifica se já tem o livro
            $verifica = $pdo->prepare("
                SELECT id 
                FROM emprestimos 
                WHERE usuario_id=? AND livro_id=? AND status='emprestado'
            ");
            $verifica->execute([$usuario["id"], $livro_id]);

            if ($verifica->fetch()) {
                $erro = "Você já possui este livro emprestado.";
            } else {
                
                // INSERE O EMPRÉSTIMO (Adicionando status e data_emprestimo por segurança)
                $insert = $pdo->prepare("
                    INSERT INTO emprestimos (usuario_id, livro_id, status, data_emprestimo)
                    VALUES (?, ?, 'emprestado', NOW())
                ");
                $insert->execute([$usuario["id"], $livro_id]);

                // ATUALIZA O ESTOQUE
                $update = $pdo->prepare("
                    UPDATE livros
                    SET disponivel=disponivel-1
                    WHERE id=?
                ");
                $update->execute([$livro_id]);

                $mensagem = "Livro emprestado com sucesso!";
            }
        }
    } catch (PDOException $e) {
        // Se o banco de dados recusar, ele mostra exatamente o porquê na tela!
        $erro = "Erro no banco de dados: " . $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| PESQUISA
|--------------------------------------------------------------------------
*/

$pesquisa = "";

if(isset($_GET["pesquisa"])){

    $pesquisa = trim($_GET["pesquisa"]);

}

if($pesquisa!=""){

    $like="%".$pesquisa."%";

    $sql=$pdo->prepare("
        SELECT *
        FROM livros
        WHERE
        titulo LIKE ?
        OR autor LIKE ?
        OR categoria LIKE ?
        ORDER BY titulo
    ");

    $sql->execute([
        $like,
        $like,
        $like
    ]);

    $livros=$sql->fetchAll();

}else{

    $livros=$pdo->query("
        SELECT *
        FROM livros
        ORDER BY titulo
    ")->fetchAll();

}

include "includes/menu.php";

?>

<div class="page">

<div class="page-header">

<h1 class="page-title">

Acervo da Biblioteca

</h1>

</div>

<?php if($mensagem): ?>

<div class="alert alert-success">

<?= htmlspecialchars($mensagem) ?>

</div>

<?php endif; ?>

<?php if($erro): ?>

<div class="alert alert-danger">

<?= htmlspecialchars($erro) ?>

</div>

<?php endif; ?>

<form class="search-bar" method="GET">

<input
type="search"
name="pesquisa"
placeholder="Pesquisar por título, autor ou categoria..."

value="<?= htmlspecialchars($pesquisa) ?>">

<button
class="btn btn-primary">

Pesquisar

</button>

</form>

<div class="books-grid">

<?php if(count($livros)==0): ?>

<div class="empty-state">

<h3>Nenhum livro encontrado.</h3>

</div>

<?php endif; ?>

<?php foreach($livros as $livro): ?>

<div class="book-card">

<div class="book-cover">

<div class="book-cover-image">
    <?php if($livro["capa"]): ?>
        <img src="uploads/capas/<?= htmlspecialchars($livro["capa"]) ?>" alt="Capa">
    <?php else: ?>
        <div class="book-cover-placeholder">📚 <span>Sem capa</span></div>
    <?php endif; ?>
    
</div>

<?php if($livro["disponivel"]>0): ?>

<div class="book-status-badge status-disponivel">

Disponível

</div>

<?php else: ?>

<div class="book-status-badge status-emprestado">

Indisponível

</div>

<?php endif; ?>

</div>

<div class="book-info">

<div class="book-categoria">

<?= htmlspecialchars($livro["categoria"]) ?>

</div>

<div class="book-titulo">

<?= htmlspecialchars($livro["titulo"]) ?>

</div>

<div class="book-autor">

<?= htmlspecialchars($livro["autor"]) ?>

</div>

<div class="book-exemplares">

<strong>Disponíveis:</strong>
<?= $livro["disponivel"] ?>


<?php if($livro["disponivel"] > 0): ?>

<form method="POST">

<input
type="hidden"
name="livro_id"
value="<?= $livro["id"] ?>">

<button
type="submit"
name="emprestar"
class="btn btn-success">

Emprestar

</button>

</form>

<?php else: ?>

<button
class="btn btn-danger"
disabled>

Indisponível

</button>

<?php endif; ?>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</body>

</html>