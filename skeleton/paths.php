<?php
if (!isset($baseUrl)) {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $safeRoot = str_replace('\\', '/', dirname(__DIR__));

    if ($docRoot !== '' && strncmp($safeRoot, $docRoot, strlen($docRoot)) === 0) {
        $baseUrl = substr($safeRoot, strlen($docRoot));
        $baseUrl = '/' . trim($baseUrl, '/') . '/';
        if ($baseUrl === '//') {
            $baseUrl = '/';
        }
    } else {
        $baseUrl = '/';
    }
}
