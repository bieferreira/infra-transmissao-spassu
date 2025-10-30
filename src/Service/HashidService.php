<?php

declare(strict_types=1);

use Hashids\Hashids;

function hashids():Hashids {
    static $h = null;
    if ($h === null) $h = new Hashids('6869317ffad58a329b4245f504724f49a5d004defbaff8f4d158ccc2e73042e8', 6);

    return $h;
}

function setHashidEncode(int $id): string {
    return hashids()->encode($id);
}

function getHashidDecode(string $code): ?int {
    $arr = hashids()->decode($code);
    return $arr[0] ?? null;
}
