<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../Models/Antena/AntenaModel.php';
require __DIR__ . '/../../Service/IBGEService.php';
require __DIR__ . '/../../Service/HashidService.php';
require_once __DIR__ . '/../../Core/csrf.php';

$uri = (require __DIR__ . '/../../Core/request.php')();
$uri = explode('/', $uri);
$uri_rota = $uri[2] ?? '';
$uri_rota = $uri_rota !== '' ? $uri_rota : 'listar';
$id_antena = $uri[3] ?? '';

$pdo = db();
//upload permitidos
$ext_allowed = ['jpg','png'];
//total ufs maior incidencia
$rank = 5;
//registros por página
$per_page = 10;

try {
    $action = preg_match(allowed(), $uri_rota, $match) ? $match[0] : '';

    echo match ($action) {
        'atualizar' => (function () use ($pdo, $id_antena, $per_page, $rank, $ext_allowed) {

            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Método inválido.';
                exit;
            }

            if(!verifyCsrfToken($_POST['csrf_token_form'])) {
                http_response_code(403);
                echo 'Por motivos de segurança, sua sessão expirou. Recarregue a página e tente novamente.';
                exit;
            }

            $old = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $old = $_POST;
            }

            if ($id_antena === '' && ($old['id_antena'] ?? '') === '') {
                $params = getParametroConsulta($pdo, $per_page);

                flash('error', 'Não foi possível localizar a antena.');
                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo'        => 'Lista de Antenas',
                    'principal_url' => 'home',
                    'csrf'          => getCsrfToken(),
                    'ranking_ufs'   => getAntenaRankingUf($pdo, $rank),
                    'antenas'       => getAntenaList($pdo, $params['options']),
                    'page'          => $params['page'],
                    'total'         => $params['total'],
                    'pages'         => $params['pages'],
                    'q'             => $params['search'],
                    'uf'            => $params['uf'],
                    'flash_error'   => take_flash('error'),
                    'redir_msg'     => 'listar',
                ]);
            }

            if($id_antena === '') {
                $id_antena = $old['id_antena'];
            }

            $id = $id_antena;

            if (!getAntenaFindId($pdo, $id)) {
                http_response_code(404);
                echo 'Antena não encontrada.';
                exit;
            }

            $in = [
                'id_antena'         => $id,
                'descricao'        => trim((string)($_POST['descricao'] ?? '')),
                'latitude'         => $_POST['latitude'] ?? null,
                'longitude'        => $_POST['longitude'] ?? null,
                'uf'               => strtoupper(trim((string)($_POST['uf'] ?? ''))),
                'altura'           => $_POST['altura'] ?? null,
                'data_implantacao' => normalize_data($_POST['data_implantacao'] ?? null),
            ];

            $errors = validate_antena($in);

            if ($errors) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                            'titulo'        => 'Editar Antena',
                            'principal_url' => 'home',
                            'antena'        => $in,
                            'ufs'           => getUfOrdenado(),
                            'errors'        => $errors,
                            'redir_msg'     => 'editar',
                        ]);
            }

            // Remover foto atual?
            $remove = isset($_POST['remover_foto']) && $_POST['remover_foto'] === '1';

            // Upload/Remoção
            $retorno_upload = handle_upload($_FILES['foto'] ?? null, $antena['foto_path'] ?? '', $remove, $ext_allowed);

            if (stripos($retorno_upload, 'ext-not-allowed') !== false) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => $in,
                    'ufs'           => getUfOrdenado(),
                    'flash_error'   => 'Tipo de arquivo não permitido, aceito apenas <br>[ '.implode(' | ', $ext_allowed).' ]',
                    'redir_msg'     => 'editar',
                ]);
            }

            $in['foto_path'] = $retorno_upload;

            if (antena_update($pdo, $id, $in)) {
                flash('success', 'Antena atualizada com sucesso!');
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => getAntenaFindId($pdo, $id),
                    'ufs'           => getUfOrdenado(),
                    'flash_success' => take_flash('success'),
                    'redir_msg'     => 'editar',
                ]);
            }

            flash('error', 'Falha ao atualizar antena, verifique Descrição já existente.');
            return APP_TWIG->render('/Antena/antena_form.twig', [
                'titulo'        => 'Editar Antena',
                'principal_url' => 'home',
                'antena'        => getAntenaFindId($pdo, $id),
                'ufs'           => getUfOrdenado(),
                'flash_error'   => take_flash('error'),
                'redir_msg'     => 'editar',
            ]);
        })(),
        'cadastrar' => (function () {
            $old = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $old = $_POST;
            }

                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Cadastrar Antena',
                    'principal_url' => 'home',
                    'csrf'          => getCsrfToken(),
                    'old'           => $old,
                    'ufs'           => getUfOrdenado(),
                ]);
        })(),
        'editar' => (function () use ($pdo, $id_antena, $per_page, $rank) {
            $old = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $old = $_POST;
            }

            if ($id_antena === '' && ($old['id_antena'] ?? '') === '') {
                $params = getParametroConsulta($pdo, $per_page);

                flash('error', 'Não foi possível localizar a antena.');
                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo'        => 'Lista de Antenas',
                    'principal_url' => 'home',
                    'csrf'          => getCsrfToken(),
                    'ranking_ufs'   => getAntenaRankingUf($pdo, $rank),
                    'antenas'       => getAntenaList($pdo, $params['options']),
                    'page'          => $params['page'],
                    'total'         => $params['total'],
                    'pages'         => $params['pages'],
                    'q'             => $params['search'],
                    'uf'            => $params['uf'],
                    'flash_error'   => take_flash('error'),
                    'redir_msg'     => 'listar',
                ]);
            }

            if($id_antena === '') {
                $id_antena = $old['id_antena'];
            }

            if (($old['id_antena'] ?? '') !== '' && ($old['form_name'] ?? '') !== 'frm_hidden') {
                $antena = array_intersect_key(getAntenaFindId($pdo, $id_antena), array_flip(['id_antena']));
            } else {
                $antena = getAntenaFindId($pdo, $id_antena);
            }

            return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'csrf'          => getCsrfToken(),
                    'old'           => $old,
                    'antena'        => $antena,
                    'ufs'           => getUfOrdenado(),
                    'redir_msg'     => '../listar',
            ]);

        })(),
        'excluir' => (function () use ($pdo, $id_antena, $rank, $per_page) {

            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Método inválido.';
                exit;
            }

            if(!verifyCsrfToken($_POST['csrf_token_form'])) {
                http_response_code(403);
                echo 'Por motivos de segurança, sua sessão expirou. Recarregue a página e tente novamente.';
                exit;
            }

            $old = [];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $old = $_POST;
            }

            $id = $old['id_antena'];

            if (trim($id) === '') {
                http_response_code(400);
                echo 'ID inválido.';
                exit;
            }

            $antena = getAntenaFindId($pdo, $id);

            if (!$antena) {
                http_response_code(404);
                echo 'Antena não encontrada.';
                exit;
            }

            if (!empty($antena['foto_path'])) {
                if( $antena['foto_path'] !== '/uploads/fotos_antenas/antena.png') {
                    $abs = $_SERVER['DOCUMENT_ROOT'] . $antena['foto_path'];
                    if (is_file($abs)) @unlink($abs);
                }
            }

            $params = getParametroConsulta($pdo, $per_page);

            if (antena_delete($pdo, $id)) {
                flash('success', 'Antena excluída com sucesso!');

                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo'        => 'Lista de Antenas',
                    'principal_url' => 'home',
                    'csrf'          => getCsrfToken(),
                    'ranking_ufs'   => getAntenaRankingUf($pdo, $rank),
                    'antenas'       => getAntenaList($pdo, $params['options']),
                    'page'          => $params['page'],
                    'total'         => $params['total'],
                    'pages'         => $params['pages'],
                    'q'             => $params['search'],
                    'uf'            => $params['uf'],
                    'flash_success' => take_flash('success'),
                    'flash_error'   => take_flash('error'),
                    'redir_msg'     => 'listar',
                ]);
            } else {

                flash('error', 'Falha ao excluir antena.');
                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo'        => 'Lista de Antenas',
                    'csrf'          => getCsrfToken(),
                    'principal_url' => 'home',
                    'ranking_ufs'   => getAntenaRankingUf($pdo, $rank),
                    'antenas'       => getAntenaList($pdo, $params['options']),
                    'page'          => $params['page'],
                    'total'         => $params['total'],
                    'pages'         => $params['pages'],
                    'q'             => $params['search'],
                    'uf'            => $params['uf'],
                    'flash_success' => take_flash('success'),
                    'flash_error'   => take_flash('error'),
                    'redir_msg'     => '../listar',
                ]);
            }

        })(),
        'listar' => (function () use ($pdo, $rank, $per_page) {

            $params = getParametroConsulta($pdo, $per_page);

            return APP_TWIG->render('/Antena/antena_list.twig', [
                'titulo'        => 'Lista de Antenas',
                'principal_url' => 'home',
                'csrf'          => getCsrfToken(),
                'ranking_ufs'  => getAntenaRankingUf($pdo, $rank),
                'antenas'  => getAntenaList($pdo, $params['options']),
                'page'     => $params['page'],
                'total'    => $params['total'],
                'pages'    => $params['pages'],
                'q'        => $params['search'],
                'uf'       => $params['uf'],
            ]);
        })(),
        'mapa' => (function () use ($pdo, $id_antena, $per_page, $rank) {
            if($id_antena === '') {
                $id_antena = $_POST['id_antena'] ?? '';
            }

            if ($id_antena === '' && ($_POST['id_antena'] ?? '') === '') {
                $params = getParametroConsulta($pdo, $per_page);

                flash('error', 'Não foi possível localizar a antena.');
                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo' => 'Lista de Antenas',
                    'principal_url' => 'home',
                    'csrf' => getCsrfToken(),
                    'ranking_ufs' => getAntenaRankingUf($pdo, $rank),
                    'antenas' => getAntenaList($pdo, $params['options']),
                    'page' => $params['page'],
                    'total' => $params['total'],
                    'pages' => $params['pages'],
                    'q' => $params['search'],
                    'uf' => $params['uf'],
                    'flash_error' => take_flash('error'),
                    'redir_msg' => 'listar',
                ]);
            }

            return APP_TWIG->render('/Antena/mapa.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'csrf'          => getCsrfToken(),
                'antena'        => getAntenaFindId($pdo, $id_antena),
            ]);
        })(),
        'salvar' => (function () use($pdo, $ext_allowed) {

            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Método inválido.';
                exit;
            }

            if(!verifyCsrfToken($_POST['csrf_token_form'])) {
                http_response_code(403);
                echo 'Por motivos de segurança, sua sessão expirou. Recarregue a página e tente novamente.';
                exit;
            }

            $in = [
                'descricao'        => trim((string)($_POST['descricao'] ?? '')),
                'latitude'         => $_POST['latitude'] ?? null,
                'longitude'        => $_POST['longitude'] ?? null,
                'uf'               => strtoupper(trim((string)($_POST['uf'] ?? ''))),
                'altura'           => $_POST['altura'] ?? null,
                'data_implantacao' => normalize_data($_POST['data_implantacao'] ?? null),
            ];

            $errors = validate_antena($in);

            if ($errors) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Cadastrar Antena',
                    'principal_url' => 'home',
                    'antena'        => $in,
                    'errors'        => $errors,
                    'redir_msg'     => 'cadastrar',
                ]);
            }

            // Upload/Remoção mantendo coerência
            $retorno_upload = handle_upload($_FILES['foto'] ?? null, '', false, $ext_allowed);

            if (stripos($retorno_upload, 'ext-not-allowed') !== false) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Cadastrar Antena',
                    'principal_url' => 'home',
                    'antena'        => $in,
                    'flash_error'   => 'Tipo de arquivo não permitido, aceito apenas <br>[ '.implode(' | ', $ext_allowed).' ]',
                    'redir_msg'     => 'cadastrar',
                ]);
            }

            $in['foto_path'] = $retorno_upload;

            $newId = antena_create($pdo, $in);

            if (trim($newId) !== '') {
                $in['id_antena'] = $newId;
                flash('success', 'Antena cadastrada com sucesso!');
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => getAntenaFindId($pdo, $newId),
                    'flash_success' => take_flash('success'),
                    'redir_msg'     => 'editar',
                ]);
            }

            flash('error', 'Falha ao Cadastrar antena, verifique Descrição já existente.');
            return APP_TWIG->render('/Antena/antena_form.twig', [
                'titulo'        => 'Cadastrar Antena',
                'principal_url' => 'home',
                'antena'        => $in,
                'flash_error'   => take_flash('error'),
                'redir_msg'     => 'cadastrar',
            ]);
        })(),
        'ver' => (function () use ($pdo, $id_antena, $per_page, $rank) {
            if($id_antena === '') {
                $id_antena = $_POST['id_antena'] ?? '';
            }

            if ($id_antena === '' && ($_POST['id_antena'] ?? '') === '') {
                $params = getParametroConsulta($pdo, $per_page);

                flash('error', 'Não foi possível localizar a antena.');
                return APP_TWIG->render('/Antena/antena_list.twig', [
                    'titulo' => 'Lista de Antenas',
                    'principal_url' => 'home',
                    'csrf' => getCsrfToken(),
                    'ranking_ufs' => getAntenaRankingUf($pdo, $rank),
                    'antenas' => getAntenaList($pdo, $params['options']),
                    'page' => $params['page'],
                    'total' => $params['total'],
                    'pages' => $params['pages'],
                    'q' => $params['search'],
                    'uf' => $params['uf'],
                    'flash_error' => take_flash('error'),
                    'redir_msg' => 'listar',
                ]);
            }

            return APP_TWIG->render('/Antena/antena_view.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'csrf'          => getCsrfToken(),
                'antena'       => getAntenaFindId($pdo, $id_antena),
            ]);
        })(),
        default => APP_TWIG->render('/404/404.twig'),
    };
} catch (\Twig\Error\LoaderError $e) {
    echo APP_TWIG->render('/404/404.twig');
} catch (\Twig\Error\SyntaxError $e) {
    http_response_code(500);
    echo 'Não foi possível apresentar está página';
} catch (\Twig\Error\RuntimeError $e) {
    http_response_code(500);
    echo 'Não foi possível gerar está página';
}

function validate_antena(array $in): array
{
    $errors = [];

    $descricao = trim((string)($in['descricao'] ?? ''));
    if ($descricao === '') {
        $errors[] = 'Descrição é obrigatória.';
    } elseif (strlen($descricao) < 10) {
        $errors[] = 'Descrição deve ter no mínimo 10 caracteres.';
    }


    $lat = $in['latitude'] ?? null;
    if ($lat === null || $lat === '' || !is_numeric($lat) || (float)$lat < -90 || (float)$lat > 90) {
        $errors[] = 'Latitude inválida (entre -90 e 90) e no máximo 7 casas decimais.';
    }

    $lon = $in['longitude'] ?? null;
    if ($lon === null || $lon === '' || !is_numeric($lon) || (float)$lon < -180 || (float)$lon > 180) {
        $errors[] = 'Longitude inválida (entre -180 e 180) e no máximo 7 casas decimais.';
    }

    $uf = trim((string)($in['uf'] ?? ''));
    if ($uf === '') {
        $errors[] = 'UF é obrigatória.';
    } elseif (strlen($uf) !== 2) {
        $errors[] = 'UF inválida.';
    }

    $alt = $in['altura'] ?? null;
    if ($alt === null || $alt === '' || !is_numeric($alt) || (float)$alt <= 0) {
        $errors[] = 'Altura inválida (exemplo 3,50).';
    }

    $data_implantacao = trim((string)($in['data_implantacao'] ?? ''));
    if ($data_implantacao !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $data_implantacao);
        $d_errors = DateTime::getLastErrors();

        if (!$d || !empty($d_errors['warning_count']) || !empty($d_errors['error_count'])) {
            $errors[] = 'Data de implantação inválida.';
        } elseif ($d > new DateTime()) {
            $errors[] = 'Data de implantação não pode ser futura.';
        }
    }

    return $errors;
}

function flash(string $type, string $msg): void {
    $_SESSION["flash_{$type}"] = $msg;
}
function take_flash(string $type): ?string {
    $key = "flash_{$type}";
    if (!empty($_SESSION[$key])) {
        $v = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $v;
    }
    return null;
}

/**
 * Trata upload opcional; retorna caminho relativo (ex.: /uploads/fotos_antenas/arquivo.jpg) ou null
 */
function handle_upload(?array $file, ?string $existingPath = '', bool $removeFlag = false, $allowed = ['jpg']): ?string
{
    // Remover arquivo atual
    if ($removeFlag && $existingPath) {
        $abs = $_SERVER['DOCUMENT_ROOT'] . $existingPath;
        if( $existingPath !== '/uploads/fotos_antenas/antena.png') {
            if (is_file($abs)) @unlink($abs);
            return '';
        }
    }

    if (!$file || empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        // Sem novo upload → mantém o existente
        return $existingPath;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $existingPath; // ou lance exceção/log
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
//    $allowed = ['jpg','png'];
    if (!in_array($ext, $allowed, true)) {
        return 'ext-not-allowed';
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fotos_antenas/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $filename = 'antena_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = rtrim($uploadDir, '/').'/'.$filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return $existingPath; // opcional: flash erro de upload
    }

    // (Opcional) Remove o antigo após sucesso
    if ($existingPath) {
        $oldAbs = $_SERVER['DOCUMENT_ROOT'] . $existingPath;
        if( $existingPath !== '/uploads/fotos_antenas/antena.png') {
            if (is_file($oldAbs)) @unlink($oldAbs);
        }
    }

    return '/uploads/fotos_antenas/' . $filename;
}

function normalize_data(?string $v): ?string
{
    if (!$v) return null;
    // Espera formato Y-m-d (ex: 2025-10-27)
    $v = trim($v);

    // Valida se é uma data válida
    $d = DateTime::createFromFormat('Y-m-d', $v);
    $d_errors = DateTime::getLastErrors();

    if (!$d || !empty($d_errors['warning_count']) || !empty($d_errors['error_count'])) {
        return null; // ou pode lançar erro, conforme regra do seu sistema
    }

    // Retorna no formato padronizado
    return $d->format('Y-m-d');
}

function getUfOrdenado() {
    $estados = getEstadosIBGE();
    usort($estados, fn($a, $b) => strcmp($a['sigla'], $b['sigla']));

    return $estados;
}

function allowed(): string {
    return $allowed = '/^(atualizar|cadastrar|editar|excluir|listar|mapa|salvar|ver)$/';
}

function getParametroConsulta($pdo, $per_page){
    // parâmetros consulta
    $page    = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $search  = isset($_GET['q']) ? (string)$_GET['q'] : '';
    $uf      = isset($_GET['uf']) ? (string)$_GET['uf'] : '';
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $per_page;

    $options = [
        'page'     => $page,
        'per_page' => $per_page,
        'search'   => $search,
        'uf'       => $uf,
    ];

    $total   = getAntenaCount($pdo, $options);
    $pages   = (int)ceil($total / $per_page);

    return compact('page', 'search', 'uf', 'options', 'total', 'pages');
}
