## Sales - API

Esta é uma API de vendas escrita utilizando o framework [Laravel](https://laravel.com/docs) em sua versão mais
enxuta para APIs, o [Lumen](https://lumen.laravel.com/docs).

### Requisitos

Para rodar o projeto é necessário ter as seguintes dependências instaladas em sua máquina:

- [Docker](https://docs.docker.com)
- [docker-compopse](https://docs.docker.com/compose)
- [make](https://command-not-found.com/make)

### Instalação

1. Copie o arquivo `.env.example` e mude seu nome para `.env`:

```bash
cp .env.example .env
```

2. Configure a porta do projeto e a conexão com o banco. Seu arquivo deve ficar parecido com isso:

```bash
APP_PORT=9090

DB_CONNECTION=mysql
DB_HOST=sales_api_db
DB_PORT=3306
DB_DATABASE=sales_db
DB_USERNAME=root
DB_PASSWORD=123456
```

Isso fará com que a API esteja disponível na porta `9090` da sua máquina.

Observe que a variável `DB_PASSWORD` deve conter o valor definido na chave `MYSQL_ROOT_PASSWORD` contida no arquivo
`docker-compose.yml`. Assim como a variável `DB_HOST` deve conter o mesmo valor especificado na chave `hostname` do serviço `mysql` do arquivo `docker-compose.yml`.

3. Suba os containers:

```bash
make up
```

4. Instale as dependências:

```bash
make install
```

5. Crie um banco de dados:

```bash
make db
```

O comando entrará no container do MySQL. Crie um banco de dados com o seguinte comando:

```
create database sales_db;
```

Ex.:

```
mysql> create database sales_db;
Query OK, 1 row affected (0.00 sec)
```

6. Crie as tabelas do banco:

```bash
make migrate
```

Obs.: Pressione CTRL+D para sair do container antes de executar o código acima.

### Testes

Para rodar a suite de testes, é necessário executar os mesmos passos da instalação, porém utilizando o arquivo
`.env.testing`. Esse é o arquivo utilizado nos testes.

**Obs.:** Recomenda-se a criação de um banco de dados apenas para a execução dos testes.

**Obs.:** Caso o arquivo `.env.testing` não exista, o projeto utilizará as configurações existentes no `.env`. Isso poderá causar erros inesperados e perda total de dados de seu banco de produção durante a execução dos testes.

Depois de tudo configurado, execute o seguinte comando:

```bash
make test
```

A saída deve ser algo parecido com:

```bash
docker exec sales_api vendor/bin/phpunit
PHPUnit 9.5.13 by Sebastian Bergmann and contributors.

...............................................................  63 / 104 ( 60%)
.........................................                       104 / 104 (100%)

Time: 00:19.168, Memory: 32.00 MB

OK (104 tests, 300 assertions)
```
