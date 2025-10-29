<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitInfraSpassu extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<SQL
            CREATE DATABASE IF NOT EXISTS infratransmissao
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci;
        SQL);

        $this->execute('USE infratransmissao;');

        //Tabela antenas
        $antenas = $this->table('antenas', [
            'id'           => false,
            'primary_key'  => ['id_antena'],
            'engine'       => 'InnoDB',
            'encoding'     => 'utf8mb4',
            'collation'    => 'utf8mb4_unicode_ci',
        ]);

        $antenas
            ->addColumn('id_antena', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'null'     => false,
            ])
            ->addColumn('descricao', 'string', [
                'limit' => 100,
                'null'  => false,
            ])
            ->addColumn('latitude', 'decimal', [
                'precision' => 10,
                'scale'     => 7,
                'null'      => true,
                'signed'   => true,
            ])
            ->addColumn('longitude', 'decimal', [
                'precision' => 10,
                'scale'     => 7,
                'null'      => true,
                'signed'   => true,
            ])
            ->addColumn('uf', 'string', [
                'limit' => 2,
                'null'  => true,
            ])
            ->addColumn('altura', 'decimal', [
                'precision' => 5,
                'scale'     => 2,
                'null'      => false,
                'signed'   => false,
            ])
            ->addColumn('foto_path', 'string', [
                'limit' => 512,
                'null' => true
            ])
            ->addColumn('data_implantacao', 'date', [
                'null' => true,
            ])
            ->addColumn('excluido', 'string', [
                'limit' => 11,
                'default' => '0',
                'null'    => false,
            ])
            ->addColumn('id_usuario_inclusao', 'biginteger', [
                'signed'   => false,
                'null'  => true,
            ])
            ->addColumn('criado_em', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('id_usuario_alteracao', 'biginteger', [
                'signed'   => false,
                'null'  => true,
            ])
            ->addColumn('alterado_em', 'datetime', [
                'null' => true,
            ])
            ->addIndex(['descricao', 'excluido'], [
                'unique' => true,
                'name'   => 'UQ_Antenas_Descricao',
            ])
            ->addIndex(['descricao'], [
                'name' => 'IDX_Antenas_Descricao',
            ])
            ->create();

        $this->execute("
            ALTER TABLE `antenas`
                ADD CONSTRAINT `CHK_Descricao_Min_Length`
                    CHECK (CHAR_LENGTH(TRIM(descricao)) >= 10),
                ADD CONSTRAINT `CHK_Latitude_Range`
                    CHECK (latitude BETWEEN -90.0000000 AND 90.0000000),
                ADD CONSTRAINT `CHK_Longitude_Range`
                    CHECK (longitude BETWEEN -180.0000000 AND 180.0000000),
                ADD CONSTRAINT `CHK_Altura_Maior_Zero`
                    CHECK (altura > 0)
        ");

        //Tabela usuarios
        $usuarios = $this->table('usuarios', [
            'id'           => false,
            'primary_key'  => ['id_usuario'],
            'engine'       => 'InnoDB',
            'encoding'     => 'utf8mb4',
            'collation'    => 'utf8mb4_unicode_ci',
        ]);

        $usuarios
            ->addColumn('id_usuario', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'null'     => false,
            ])
            ->addColumn('nome', 'string', [
                'limit' => 150,
                'null'  => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 200,
                'null'  => false,
            ])
            ->addColumn('senha', 'string', [
                'limit' => 255,
                'null'  => false,
                'comment' => 'password_hash()',
            ])
            ->addColumn('excluido', 'string', [
                'limit' => 11,
                'default' => '0',
                'null'    => false,
            ])
            ->addColumn('id_usuario_inclusao', 'biginteger', [
                'signed'   => false,
                'null'  => true,
            ])
            ->addColumn('criado_em', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('id_usuario_alteracao', 'biginteger', [
                'signed'   => false,
                'null'  => true,
            ])
            ->addColumn('alterado_em', 'datetime', [
                'null' => true,
            ])
            ->addIndex(['email', 'excluido'], [
                'unique' => true,
                'name'   => 'UQ_Usuarios_Email',
            ])
            ->addIndex(['nome'], [
                'name' => 'IDX_Usuarios_Nome',
            ])
            ->create();

        $this->execute("
            ALTER TABLE `usuarios`
                ADD CONSTRAINT `CHK_Usuarios_Email_Format`
                    CHECK (`email` REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$')
        ");

        //Tabela estados
        $table = $this->table('estados', [
            'id' => false,
            'primary_key' => 'uf',
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('uf', 'string', ['limit' => 2, 'null' => false])
            ->addColumn('uf_descricao', 'string', ['limit' => 100, 'null' => false])
            ->addIndex(['uf_descricao'], ['unique' => true, 'name' => 'ux_uf_descricao'])
            ->create();

        //Procedure sp_delete_antena
        $this->execute(<<<'SQL'
            CREATE PROCEDURE `sp_delete_antena`(IN id_antena_entrada BIGINT, IN id_usuario_entrada BIGINT)
            BEGIN
                DECLARE valida_acao INT DEFAULT 0;

                    UPDATE antenas SET 
                        Excluido        = UNIX_TIMESTAMP(NOW()),
                        id_usuario_alteracao = id_usuario_entrada, 
                        alterado_em = NOW()
                    WHERE id_antena = id_antena_entrada AND Excluido = '0';

                    SET valida_acao = ROW_COUNT();

                    IF valida_acao > 0 THEN
                        SELECT 'Antena excluída com sucesso !' AS mensagemRetorno;
                    ELSE
                        SELECT 'Não foi possível realizar a exclusão da Antena' AS mensagemRetorno;
                    END IF;
            END
        SQL);

    }

    public function down(): void
    {
        $this->execute('USE infratransmissao');

        $this->execute('DROP PROCEDURE IF EXISTS sp_delete_antena');
        $this->execute('DROP TABLE IF EXISTS infratransmissao.antenas');
        $this->execute('DROP TABLE IF EXISTS infratransmissao.usuarios');
    }
}
