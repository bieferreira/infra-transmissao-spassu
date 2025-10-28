<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$uri = (require __DIR__ . '/../../Core/request.php')();
$uri = explode('/', $uri);
$uri_rota = $uri[2] ?? '';
$uri_rota = $uri_rota !== '' ? $uri_rota : 'listar';
$id_antena = $uri[3] ?? '';

require_once __DIR__ . '/../../Models/Antena/AntenaModel.php';

// parâmetros consulta
$page    = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$search  = isset($_GET['q']) ? (string)$_GET['q'] : '';
$uf      = isset($_GET['uf']) ? (string)$_GET['uf'] : '';

$options = [
    'page'     => $page,
    'per_page' => 10,
    'search'   => $search,
    'uf'       => $uf,
];

$allowed = '/^(atualizar|cadastrar|editar|excluir|listar|mapa|salvar|ver)$/';

try {
    $action = preg_match($allowed, $uri_rota, $match) ? $match[0] : '';

    echo match ($action) {
        'atualizar' => (function () use ($id_antena) {

            $id = (int)$id_antena;

            if(!$id) {
                http_response_code(400);
                echo 'ID inválido.';
                exit;
            }

            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Método inválido.';
                exit;
            }

            $antena = antena_find($id);

            if (!$antena) {
                http_response_code(404);
                echo 'Antena não encontrada.';
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
                            'titulo'        => 'Editar Antena',
                            'principal_url' => 'home',
                            'antena'        => $antena,
                            'errors'        => $errors,
                        ]);
            }

            // Remover foto atual?
            $remove = isset($_POST['remover_foto']) && $_POST['remover_foto'] === '1';

            // Upload/Remoção mantendo coerência
            $retorno_upoad = handle_upload($_FILES['foto'] ?? null, $antena['foto_path'] ?? null, $remove);

            if (stripos($retorno_upoad, 'not-allowed') !== false) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => $antena,
                    'flash_error'        => 'Tipo de arquivo não permitido, aceito apenas [JPG, PNG]',
                ]);
            }

            $in['foto_path'] = $retorno_upoad;

            $ok = antena_update($id, $in);

            $antena = antena_find($id);

            if ($ok) {
                flash('success', 'Antena atualizada com sucesso!');
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => $antena,
                    'flash_success' => take_flash('success'),
                ]);
            }

            flash('error', 'Falha ao atualizar antena.');
            $_SESSION['old'] = $in;
//            header('Location: ' . BASE_URL . '/antena/editar/' . $id);
//            exit;

            //$antena = antena_find($id);

            return APP_TWIG->render('/Antena/antena_form.twig', [
                'titulo'        => 'Editar Antena',
                'principal_url' => 'home',
                'antena'        => $antena,
                'flash_error'   => take_flash('error'),
            ]);
        })(),
        'cadastrar' => (function () {
            return APP_TWIG->render('/Antena/antena_form.twig', [
                'titulo'        => 'Cadastrar Antena',
                'principal_url' => 'home',
            ]);
        })(),
        'editar' => (function () use ($id_antena) {
            $id_antena  = (int)$id_antena;
            $antena = antena_find($id_antena);

                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'       => $antena,
                ]);

        })(),
        'excluir' => APP_TWIG->render('/Antena/antena_form.twig', [
            'titulo'        => 'Nova Antena',
            'principal_url' => 'home',
        ]),
        'listar' => (function () use ($options, $page, $search, $uf) {

            $total   = antena_count($options);
            $pages   = (int)ceil($total / 10);

            $ranking_ufs  = antena_top_ufs(5);
            $antenas = antena_list($options);

            return APP_TWIG->render('/Antena/antena_list.twig', [
                'titulo'        => 'Lista de Antenas',
                'principal_url' => 'home',
                'ranking_ufs'  => $ranking_ufs,
                'antenas'  => $antenas,
                'page'     => $page,
                'total'    => $total,
                'pages'    => $pages,
                'q'        => $search,
                'uf'       => $uf,
            ]);
        })(),
        'mapa' => (function () use ($id_antena) {
            $id_antena  = (int)$id_antena;
            $antena = antena_find($id_antena);

            return APP_TWIG->render('/Antena/mapa.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'antena'       => $antena,
            ]);
        })(),
        'salvar' => (function () {

            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Método inválido.';
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
                    'errors'        => $errors,
                ]);
            }

            // Upload/Remoção mantendo coerência
            $retorno_upoad = handle_upload($_FILES['foto'] ?? null, null, false);

            if (stripos($retorno_upoad, 'not-allowed') !== false) {
                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Cadastrar Antena',
                    'principal_url' => 'home',
                    'flash_error'        => 'Tipo de arquivo não permitido, aceito apenas [JPG, PNG]',
                ]);
            }

            $in['foto_path'] = $retorno_upoad;

            $newId = antena_create($in);

            if ($newId) {
                flash('success', 'Antena cadastrada com sucesso!');

                $antena = antena_find($newId);

                return APP_TWIG->render('/Antena/antena_form.twig', [
                    'titulo'        => 'Editar Antena',
                    'principal_url' => 'home',
                    'antena'        => $antena,
                    'flash_success' => take_flash('success'),
                ]);
            }

            flash('error', 'Falha ao Cadastrar antena.');

            return APP_TWIG->render('/Antena/antena_form.twig', [
                'titulo'        => 'Cadastrar Antena',
                'principal_url' => 'home',
                'flash_error'   => take_flash('error'),
            ]);
        })(),
        'ver' => (function () use ($id_antena) {
            $id_antena  = (int)$id_antena;
            $antena = antena_find($id_antena);

            return APP_TWIG->render('/Antena/antena_view.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'antena'       => $antena,
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
        $errors[] = 'Latitude inválida (entre -90 e 90).';
    }

    $lon = $in['longitude'] ?? null;
    if ($lon === null || $lon === '' || !is_numeric($lon) || (float)$lon < -180 || (float)$lon > 180) {
        $errors[] = 'Longitude inválida (entre -180 e 180).';
    }

    $uf = trim((string)($in['uf'] ?? ''));
    if ($uf === '') {
        $errors[] = 'UF é obrigatória.';
    } elseif (strlen($uf) !== 2) {
        $errors[] = 'UF inválida.';
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
function handle_upload(?array $file, ?string $existingPath = null, bool $removeFlag = false): ?string
{
    // Remover arquivo atual
    if ($removeFlag && $existingPath) {
        $abs = $_SERVER['DOCUMENT_ROOT'] . $existingPath;
        if (is_file($abs)) @unlink($abs);
        return '';
    }

    if (!$file || empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        // Sem novo upload → mantém o existente
        return $existingPath;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $existingPath; // ou lance exceção/log
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','png'];
    if (!in_array($ext, $allowed, true)) {
        //return $existingPath; // opcional: flash erro de tipo
        return 'not-allowed';
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
        if (is_file($oldAbs)) @unlink($oldAbs);
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
