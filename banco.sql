CREATE DATABASE IF NOT EXISTS biblioteca;
USE biblioteca;

-- ==========================
-- Tabela de usuários
-- ==========================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    matricula VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('usuario','admin') DEFAULT 'usuario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Tabela de livros
-- ==========================
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    categoria VARCHAR(80),
    quantidade INT DEFAULT 1,
    disponivel INT DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Tabela de empréstimos
-- ==========================
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_devolucao DATETIME NULL,
    status ENUM('emprestado','devolvido') DEFAULT 'emprestado',

    FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,

    FOREIGN KEY(livro_id) REFERENCES livros(id)
        ON DELETE CASCADE
);

-- ==========================
-- Administrador padrão
-- senha: admin123
-- ==========================

INSERT INTO usuarios
(nome, matricula, email, senha, tipo)
VALUES
(
'Administrador',
'admin',
'admin@biblioteca.com',
'$2y$10$8qI9N1nZzS8Oc7Dk7N5w7uY8N6F4rxJQ9h9UQK2I3uY2Q9vW9Hk1K',
'admin'
);

-- ==========================
-- Livros de exemplo
-- ==========================

INSERT INTO livros
(titulo,autor,categoria,quantidade,disponivel)
VALUES
('Dom Casmurro','Machado de Assis','Romance',5,5),
('O Pequeno Príncipe','Antoine de Saint-Exupéry','Infantil',3,3),
('1984','George Orwell','Ficção',4,4),
('Clean Code','Robert C. Martin','Tecnologia',2,2);