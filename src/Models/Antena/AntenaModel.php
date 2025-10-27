<?php declare(strict_types=1);

require_once __DIR__ . '/../../db.php';

/**
 * Lista antenas com paginação e filtros opcionais.
 * $options = [
 *   'page' => 1,
 *   'per_page' => 10,
 *   'search' => 'ANT-0001',   // procura em descricao
 *   'uf' => 'MS'              // filtra por UF
 * ]
 */
function antena_list(array $options = []): array
{
    $page     = isset($options['page']) && (int)$options['page'] > 0 ? (int)$options['page'] : 1;
    $perPage  = isset($options['per_page']) && (int)$options['per_page'] > 0 ? (int)$options['per_page'] : 10;
    $search   = isset($options['search']) ? trim((string)$options['search']) : '';
    $uf       = isset($options['uf']) ? strtoupper(trim((string)$options['uf'])) : '';

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

    $sql = 'SELECT a.id_antena, a.descricao, a.latitude, a.longitude, a.uf, a.data_implantacao 
            FROM antenas a';

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY a.id_antena DESC
              LIMIT :limit OFFSET :offset';

    $pdo = db();
    $stmt = $pdo->prepare($sql);

    // bind dos filtros
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    // paginação
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Total de antenas (para paginação) respeitando os mesmos filtros.
 */
function antena_count(array $options = []): int
{
    $search = isset($options['search']) ? trim((string)$options['search']) : '';
    $uf     = isset($options['uf']) ? strtoupper(trim((string)$options['uf'])) : '';

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

    $sql = 'SELECT COUNT(*) AS total FROM antenas';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $pdo = db();
    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    $stmt->execute();
    $row = $stmt->fetch();

    return $row ? (int)$row['total'] : 0;
}

/**
 * Busca uma antena pelo ID.
 */
function antena_find(int $id): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id_antena, descricao, latitude, longitude, uf, data_implantacao
                           FROM antenas WHERE id_antena = :id LIMIT 1');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();
    return $row ?: null;
}
