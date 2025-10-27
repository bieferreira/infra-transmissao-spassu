# infra-transmissao-spassu

################################# Etapas execução projeto #################################

1 Mapeamento

2 Definição utilizar PHP Procedural, Twig e bootstrap

3 Definindo layout, buscando identidade visual site Spassu / Anatel

4 Ajuste logo e imports font

6 Configuração ambiente

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
    