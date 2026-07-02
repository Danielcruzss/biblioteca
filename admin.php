<?php

require_once "auth.php";

requerAdmin();

$usuario = usuarioAtual();

$mensagem = "";
$erro = "";

/*
|--------------------------------------------------------------------------
| EDITAR
|--------------------------------------------------------------------------
*/

$editando = false;
$livroEditar = null;

if (isset($_GET["editar"])) {

    $id = (int)$_GET["editar"];

    $sql = $pdo->prepare("
        SELECT *
        FROM livros
        WHERE id = ?
    ");

    $sql->execute([$id]);

    $livroEditar = $sql->fetch();

    if ($livroEditar) {
        $editando = true;
    }
}

/*
|--------------------------------------------------------------------------
| SALVAR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salvar"])) {

    $titulo = trim($_POST["titulo"]);
    $autor = trim($_POST["autor"]);
    $categoria = trim($_POST["categoria"]);
    $quantidade = (int)$_POST["quantidade"];

    /*
    |--------------------------------------------------------------------------
    | CAPA
    |--------------------------------------------------------------------------
    */

    $capa = null;

    if (!empty($_POST["id"])) {

        $busca = $pdo->prepare("
            SELECT capa
            FROM livros
            WHERE id=?
        ");

        $busca->execute([
            (int)$_POST["id"]
        ]);

        $dados = $busca->fetch();

        if ($dados) {
            $capa = $dados["capa"];
        }

    }

    if (
        isset($_FILES["capa"]) &&
        $_FILES["capa"]["error"] == UPLOAD_ERR_OK
    ) {

        $permitidas = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];

        $extensao = strtolower(
            pathinfo(
                $_FILES["capa"]["name"],
                PATHINFO_EXTENSION
            )
        );

        if (in_array($extensao, $permitidas)) {

            if (!is_dir(__DIR__ . "/uploads/capas")) {

                mkdir(
                    __DIR__ . "/uploads/capas",
                    0777,
                    true
                );

            }

            $novoNome =
                uniqid("livro_")
                . "."
                . $extensao;

            if (

                move_uploaded_file(

                    $_FILES["capa"]["tmp_name"],

                    __DIR__
                    . "/uploads/capas/"
                    . $novoNome

                )

            ) {

                $capa = $novoNome;

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÃO
    |--------------------------------------------------------------------------
    */

    if (

        empty($titulo) ||

        empty($autor) ||

        empty($categoria) ||

        $quantidade <= 0

    ) {

        $erro = "Preencha todos os campos.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | EDITAR
        |--------------------------------------------------------------------------
        */

        if (!empty($_POST["id"])) {

            $id = (int)$_POST["id"];

            $busca = $pdo->prepare("
                SELECT quantidade,
                       disponivel
                FROM livros
                WHERE id=?
            ");

            $busca->execute([$id]);

            $antigo = $busca->fetch();

            $emprestados =
                $antigo["quantidade"]
                -
                $antigo["disponivel"];

            $novoDisponivel =
                max(
                    0,
                    $quantidade
                    -
                    $emprestados
                );

            $update = $pdo->prepare("

                UPDATE livros

                SET

                titulo=?,

                autor=?,

                categoria=?,

                quantidade=?,

                disponivel=?,

                capa=?

                WHERE id=?

            ");

            $update->execute([

                $titulo,

                $autor,

                $categoria,

                $quantidade,

                $novoDisponivel,

                $capa,

                $id

            ]);

            $mensagem = "Livro atualizado com sucesso.";

        }

        /*
        |--------------------------------------------------------------------------
        | NOVO LIVRO
        |--------------------------------------------------------------------------
        */

        else {

            $insert = $pdo->prepare("

                INSERT INTO livros

                (

                    titulo,

                    autor,

                    categoria,

                    quantidade,

                    disponivel,

                    capa

                )

                VALUES

                (

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?

                )

            ");

            $insert->execute([

                $titulo,

                $autor,

                $categoria,

                $quantidade,

                $quantidade,

                $capa

            ]);

            $mensagem = "Livro cadastrado com sucesso.";

        }

    }

}

/*
|--------------------------------------------------------------------------
| EXCLUIR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["excluir"])) {

    $id = (int)$_POST["id"];

    // Busca a capa para apagar do disco
    $sql = $pdo->prepare("
        SELECT capa
        FROM livros
        WHERE id=?
    ");

    $sql->execute([$id]);

    $livro = $sql->fetch();

    // Verifica se existem empréstimos ativos
    $sql = $pdo->prepare("
        SELECT COUNT(*) total
        FROM emprestimos
        WHERE livro_id=?
        AND status='emprestado'
    ");

    $sql->execute([$id]);

    $dados = $sql->fetch();

    if ($dados["total"] > 0) {

        $erro = "Não é possível excluir um livro que está emprestado.";

    } else {

        // Exclui imagem
        if (
            !empty($livro["capa"]) &&
            file_exists(__DIR__ . "/uploads/capas/" . $livro["capa"])
        ) {

            unlink(__DIR__ . "/uploads/capas/" . $livro["capa"]);

        }

        $delete = $pdo->prepare("
            DELETE
            FROM livros
            WHERE id=?
        ");

        $delete->execute([$id]);

        $mensagem = "Livro excluído com sucesso.";

    }

}

/*
|--------------------------------------------------------------------------
| PESQUISA
|--------------------------------------------------------------------------
*/

$pesquisa = "";

if (isset($_GET["pesquisa"])) {

    $pesquisa = trim($_GET["pesquisa"]);

}

if ($pesquisa != "") {

    $like = "%".$pesquisa."%";

    $sql = $pdo->prepare("
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

    $livros = $sql->fetchAll();

} else {

    $livros = $pdo->query("
        SELECT *
        FROM livros
        ORDER BY titulo
    ")->fetchAll();

}

include "menu.php";
?>

<div class="container">

<h1>Painel Administrativo</h1>

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

<form method="GET" class="search-bar">

<input
type="text"
name="pesquisa"
placeholder="Pesquisar livro..."
value="<?= htmlspecialchars($pesquisa) ?>">

<button
type="submit"
class="btn btn-primary">

Pesquisar

</button>

</form>

<hr>

<h2>

<?= $editando ? "Editar Livro" : "Cadastrar Livro" ?>

</h2>

<form
method="POST"
enctype="multipart/form-data">

<?php if($editando): ?>

<input
type="hidden"
name="id"
value="<?= $livroEditar["id"] ?>">

<?php endif; ?>

<label>Título</label>

<input
type="text"
name="titulo"
required
value="<?= $editando ? htmlspecialchars($livroEditar["titulo"]) : "" ?>">

<label>Autor</label>

<input
type="text"
name="autor"
required
value="<?= $editando ? htmlspecialchars($livroEditar["autor"]) : "" ?>">

<label>Categoria</label>

<input
type="text"
name="categoria"
required
value="<?= $editando ? htmlspecialchars($livroEditar["categoria"]) : "" ?>">

<label>Quantidade</label>

<input
type="number"
name="quantidade"
min="1"
required
value="<?= $editando ? $livroEditar["quantidade"] : 1 ?>">

<label>Capa do Livro</label>

<input
type="file"
name="capa"
accept=".jpg,.jpeg,.png,.webp,image/*">

<?php if($editando && !empty($livroEditar["capa"])): ?>

<br><br>

<strong>Capa atual:</strong>

<br><br>

<img
src="uploads/capas/<?= htmlspecialchars($livroEditar["capa"]) ?>"
style="
width:120px;
border-radius:8px;
border:1px solid #ddd;
">

<br><br>

<?php endif; ?>

<button
type="submit"
name="salvar"
class="btn btn-success">

<?= $editando ? "Atualizar Livro" : "Cadastrar Livro" ?>

</button>

<?php if($editando): ?>

<a
href="admin.php"
class="btn btn-secondary">

Cancelar

</a>

<?php endif; ?>

</form>

<hr>

<h2>Livros cadastrados</h2>

<table>

<tr>

<th>ID</th>

<th>Capa</th>

<th>Título</th>

<th>Autor</th>

<th>Categoria</th>

<th>Total</th>

<th>Disponível</th>

<th>Ações</th>

</tr>

<?php foreach ($livros as $livro): ?>

<tr>

<td><?= $livro["id"] ?></td>

<td>

<?php if(!empty($livro["capa"])): ?>

<img
src="uploads/capas/<?= htmlspecialchars($livro["capa"]) ?>"
style="
width:60px;
height:90px;
object-fit:cover;
border-radius:6px;
border:1px solid #ddd;
">

<?php else: ?>

<span style="color:#999;">

Sem capa

</span>

<?php endif; ?>

</td>

<td>

<?= htmlspecialchars($livro["titulo"]) ?>

</td>

<td>

<?= htmlspecialchars($livro["autor"]) ?>

</td>

<td>

<?= htmlspecialchars($livro["categoria"]) ?>

</td>

<td>

<?= $livro["quantidade"] ?>

</td>

<td>

<?php if($livro["disponivel"] > 0): ?>

<span class="tag tag-success">

<?= $livro["disponivel"] ?>

</span>

<?php else: ?>

<span class="tag tag-danger">

Indisponível

</span>

<?php endif; ?>

</td>

<td>

<div style="display:flex;gap:8px;">

<a
class="btn btn-warning"
href="admin.php?editar=<?= $livro["id"] ?>">

Editar

</a>

<form
method="POST"
onsubmit="return confirm('Deseja realmente excluir este livro?');">

<input
type="hidden"
name="id"
value="<?= $livro["id"] ?>">

<button
type="submit"
name="excluir"
class="btn btn-danger">

Excluir

</button>

</form>

</div>

</td>

</tr>

<?php endforeach; ?>

</table>

<br>

<div class="card">

<h3>Resumo do Acervo</h3>

<p>

<strong>Total de livros cadastrados:</strong>

<?= count($livros) ?>

</p>

<p>

<strong>Total de exemplares:</strong>

<?php

$total = 0;

foreach($livros as $l){

    $total += $l["quantidade"];

}

echo $total;

?>

</p>

<p>

<strong>Exemplares disponíveis:</strong>

<?php

$disponiveis = 0;

foreach($livros as $l){

    $disponiveis += $l["disponivel"];

}

echo $disponiveis;

?>

</p>

</div>

</div>

</body>

</html>

