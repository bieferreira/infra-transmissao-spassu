<?php declare(strict_types=1);

require_once __DIR__ . '/../../db.php';


function getAntenaRankingUf(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
        SELECT 
            a.uf,
            e.uf_descricao, 
            COUNT(*) AS total
        FROM antenas AS a
        INNER JOIN estados AS e ON e.uf = a.uf
        WHERE a.excluido = '0'
        GROUP BY a.uf, e.uf_descricao
        ORDER BY total DESC
        LIMIT :limit
    SQL;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    try {
        $stmt->execute();
    } catch (PDOException $e) {
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            die('Erro ao conectar as tabelas antenas ou estados: ' . htmlspecialchars($e->getMessage()));
        } else {
            die('Falha de conexão com as tabelas.');
        }
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lista antenas com paginação e filtros opcionais.
 * $options = [
 *   'page' => 1,
 *   'per_page' => 10,
 *   'search' => 'ANT-0001',   // procura em descricao
 *   'uf' => 'MS'              // filtra por UF
 * ]
 */
function getAntenaList(PDO $pdo, $options): array
{
    $page     = $options['page'];
    $perPage  = $options['per_page'];
    $search   = $options['search'];
    $uf       = $options['uf'];

    $offset = ($page - 1) * $perPage;
    $where  = [];
    $params = [];


    if ($search !== '') {
        $where[] = 'a.descricao LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }

    if ($uf !== '') {
        $where[] = 'a.uf = :uf';
        $params[':uf'] = $uf;
    }

    $sql = <<<SQL
    SELECT 
        a.id_antena, 
        a.descricao, 
        a.latitude, 
        a.longitude, 
        a.uf, 
        a.data_implantacao, 
        a.altura 
    FROM antenas a  
    WHERE excluido = '0'
    SQL;

    if ($where) {
        $sql .= ' AND ' . implode(' AND ', $where);
    }

    $sql .= <<<SQL
        ORDER BY a.id_antena DESC
        LIMIT :limit OFFSET :offset
    SQL;

    $stmt = $pdo->prepare($sql);

    // bind dos filtros
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    // paginação
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    try {
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as &$row) {
            if (!empty($row['id_antena'])) {
                $row['id_antena'] = setHashidEncode((int) $row['id_antena']);
            }
        }
        unset($row);
    } catch (PDOException $e) {
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            die('Erro ao conectar a tabela antena: ' . htmlspecialchars($e->getMessage()));
        } else {
            die('Falha de conexão com a tabela.');
        }
    }

    return $result;

}

/**
 * Total de antenas (para paginação) respeitando os mesmos filtros.
 */
function getAntenaCount(PDO $pdo, array $options): int
{
    $search = $options['search'];
    $uf     = $options['uf'];

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[] = 'descricao LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }

    if ($uf !== '') {
        $where[] = 'uf = :uf';
        $params[':uf'] = $uf;
    }

    $sql = <<<SQL
        SELECT 
            COUNT(*) AS total 
        FROM antenas 
        WHERE excluido = '0'
    SQL;

    if ($where) {
        $sql .= ' AND ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare($sql);

    // bind dos filtros
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    try {
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            die('Erro ao conectar a tabela antena count: ' . htmlspecialchars($e->getMessage()));
        } else {
            die('Falha de conexão com a tabela.');
        }
    }

    return (int) $row['total'];

}

/**
 * Busca uma antena pelo ID.
 */
function getAntenaFindId(PDO $pdo, string $id): ?array
{
    $id = (int)getHashidDecode($id);

    $sql = <<<SQL
        SELECT 
            a.id_antena, 
            a.descricao, 
            a.latitude, 
            a.longitude, 
            a.uf, 
            a.altura, 
            a.data_implantacao, 
            a.foto_path, 
            e.uf_descricao
        FROM antenas AS a
        INNER JOIN estados AS e ON e.uf = a.uf 
        WHERE 
            id_antena = :id 
            AND excluido = '0' 
        LIMIT 1
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['id_antena'])) {
            $row['id_antena'] = setHashidEncode((int) $row['id_antena']);
        }


    return $row ?: null;
}

/**
 * Atualizar antena
 */
function antena_update(PDO $pdo, string $id, array $dados): bool
{
    $mensagemerro = "";
    $id = (int)getHashidDecode($id);

    try {
    $sql = <<<SQL
        UPDATE antenas SET 
            descricao = :descricao,
            latitude = :latitude,
            longitude = :longitude,
            uf = :uf,
            altura = :altura,
            foto_path = COALESCE(:foto_path, foto_path),
            data_implantacao = :data_implantacao,
            id_usuario_alteracao = :id_usuario_alteracao
        WHERE id_antena = :id
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':descricao', $dados['descricao']);
    $stmt->bindValue(':latitude', $dados['latitude']);
    $stmt->bindValue(':longitude', $dados['longitude']);
    $stmt->bindValue(':uf', $dados['uf']);
    $stmt->bindValue(':altura', $dados['altura']);
    $stmt->bindValue(':foto_path', $dados['foto_path'] ?? null);
    $stmt->bindValue(':data_implantacao', $dados['data_implantacao']);
    $stmt->bindValue(':id_usuario_alteracao', ID_USUARIO);

        if ($stmt->execute()) {
            return true;
        }

    } catch (PDOException $e) {

        $mensagemerro = $e->getMessage();

    } catch (InvalidArgumentException $e) {

        $mensagemerro = $e->getMessage();

    } catch (Exception $e) {

        $mensagemerro = $e->getMessage();

    }

    return false;
}

function antena_create(PDO $pdo, array $dados): string
{
    $mensagemerro = "";
    try {
    $sql = <<<SQL
            INSERT INTO antenas (
                descricao,
                latitude,
                longitude,
                uf,
                altura,
                foto_path,
                data_implantacao,
                id_usuario_inclusao
            ) VALUES (
                :descricao,
                :latitude,
                :longitude,
                :uf,
                :altura,
                :foto_path,
                :data_implantacao,
                :id_usuario_inclusao
            )
        SQL;

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':descricao', $dados['descricao']);
    $stmt->bindValue(':latitude', $dados['latitude']);
    $stmt->bindValue(':longitude', $dados['longitude']);
    $stmt->bindValue(':uf', $dados['uf']);
    $stmt->bindValue(':altura', $dados['altura'] ?? null);
    $stmt->bindValue(':foto_path', $dados['foto_path'] ?? null);
    $stmt->bindValue(':data_implantacao', $dados['data_implantacao'] ?? null);
    $stmt->bindValue(':id_usuario_inclusao', ID_USUARIO);

    if ($stmt->execute()) {
        return setHashidEncode((int) $pdo->lastInsertId());
    }

    } catch (PDOException $e) {
        $mensagemerro = $e->getMessage();
    } catch (InvalidArgumentException $e) {
        $mensagemerro = $e->getMessage();
    } catch (Exception $e) {
        $mensagemerro = $e->getMessage();
    }

    return '';
}

function antena_delete(PDO $pdo, string $id): bool
{
    $id = (int)getHashidDecode($id);

    try {
        $id_usuario_inclusao = ID_USUARIO;

        $sql = <<<SQL
            CALL sp_delete_antena(:id_antena, :id_usuario_inclusao)
        SQL;
        $sqlPdo = $pdo->prepare($sql);
        $sqlPdo->bindParam(':id_antena', $id);
        $sqlPdo->bindParam(':id_usuario_inclusao', $id_usuario_inclusao);


        if ($sqlPdo->execute()) {
            $retorno = true;
        }

    } catch (PDOException $e) {
        $retorno = false;
    }

    return $retorno;
}
