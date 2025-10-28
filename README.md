# infra-transmissao-spassu

################################# Etapas execução projeto #################################

1 Mapeamento

2 Definição utilizar PHP Procedural, Twig e bootstrap

3 Definindo layout, buscando identidade visual site Spassu / Anatel

4 Ajuste logo e imports font

5 Configuração ambiente

    => Criar repositorio github
    => Iniciar git
        >> git init
        >> git config --global init.defaultBranch main
        >> git remote add origin https://github.com/bieferreira/infra-transmissao-spassu.git
    => Criar docker-compose.yml
    => Criar DockerFile
    => Criar .dockerignore
    => Criar .gitignore
    => Criar estrutura projeto
        >> src
        >> public / index.php
    => Permitir rotas vazia
        >> criar public / .htaccess
        >> criar docker / apache / 000-default.conf
    => Subir ambiente
        >> docker network create infratransmissaonetwork
        >> docker compose up -d --build
            =>> teste sucesso navegador
    => Definindo token git
        >> configurando phpstorm
    => Instalar dependencias
    	>> Migrations phinx
    		=>> composer require robmorgan/phinx --dev
    	>> Testes unitários
    		=>> sudo apt-get update
				sudo apt-get install php8.3-xml
				php -m | grep -E 'dom|xml'
    		=>> composer require --dev phpunit/phpunit:^10.5 -W
    		=>> criar phpunit.xml
    		=>> criar tests / ExampleTest.php
    		=>> composer test > ./vendor/bin/phpunit
                >>> teste exemplo com sucesso
    	>> Corretor PSR-12
    		=>> criar .php-cs-fixer.dist.php
    		=>> scripts composer.json
    			>>> lint vericar padrao
    			>>> format corrigir para o padrao
    	>> Análise qualidade
    		=>> composer require nunomaduro/phpinsights --dev
    		=>> criar phpinsights.php
    		=>> scripts composer.json
    			>>> insights analisar padrao
    	>> Twig
    		=>> composer require "twig/twig:^3.0"
    		=>> criar src / config.php
    	>> Bootstrap
    		=>> composer require twbs/bootstrap
    		=>> mover arquivos vendor/twbs/bootstrap/dist/ -> assets

6 Implementação

    => Definindo estrutura
    	>> src / config.php
    	>> src / Controllers
    	>> src / Core
    	>> src / Models
    	>> src / Views
        >> public / index.php
    => Implementando 
    	>> src / config.php
    	>> public / index.php
    	>> Controllers / Home / HomeController.php
    	>> Views / Home / home.twig
    => Definir layout
    	>> prototipando layout
    => Implementando
    	>> Views / Home / home.twig
    	>> public / assets / css / principal.php
    	>> src / config.php
    	>> Views / base.twig
    	>> Views / Partials / header.twig
    	>> Views / Partials / footer.twig
    => Refatorando
    	>> Views / Home / home.twig
    => Refatorar docker compose
    	>> docker compose down
    	>> docker compose up -d --build
    => Criar migrations
    	>> Gerar o arquivo de configuração do Phinx.
    		=>> vendor/bin/phinx init
		>> Configurar a conexão com o banco de dados MySQL.
			=>> criar phinx-db-constants.php
		>> Criar a estrutura de diretórios para as migrações.
			=>> db / migrations
			=>> db / seeds
		>> Gerar migrations
			=>> composer migrate-init >> vendor/bin/phinx create vendor/bin/phinx migrateInitInfraSpassu
			=>> Estrutura do projeto
				>>> antenas
				>>> usuarios
				>>> estados
				>>> carga antenas 100k
				>>> carga estados
			=>> composer migrate >> vendor/bin/phinx migrate
		>> Criar carga dados
			=>> migrate-init-data > vendor/bin/phinx seed:create AntenasSeeder
    		=>> migrate-load-data > vendor/bin/phinx seed:run -s AntenasSeeder
    => Definir layout
    	>> criar list
    => Refatorando rotas
    => Implementando
    	>> src / Core / request.php
    	>> src / Controllers / 404 / 404Controler.php
    	>> src / Views / 404 / 404.twig
    => Implementando Model antena list
	    >> src / db.php
    	>> src / Model / Antena / AntenaModel.php
	=> Implementando Antena
		>> criar listar com model
		>> criar ranking com model
		>> criar ver antena com model
			=>> estourando mapa
			==> realizado ajustes e correções
    => Definir layout
		>> criar form
