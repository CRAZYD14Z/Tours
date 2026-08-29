<?php
declare(strict_types=1);

// Normalizar parámetro de ID (Id o id o company_id)
$companyId = filter_input(INPUT_GET, 'Id', FILTER_VALIDATE_INT) 
    ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_GET, 'company_id', FILTER_VALIDATE_INT);

if ($companyId) {
    $_GET['id'] = $companyId;
}

require_once __DIR__ . '/pages/compania/index.php';
