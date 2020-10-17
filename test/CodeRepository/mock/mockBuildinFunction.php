<?php declare(strict_types=1);

    // mock php buildin function by namespace
    namespace RewriteDagger\CodeRepository;

    global $fileGetContentsReturn;
    $fileGetContentsReturn = '';
    function file_get_contents(): string
    {
        global $fileGetContentsReturn;
        return $fileGetContentsReturn;
    }

    function umask() {}

    global $tempnamReturn;
    $tempnamReturn = '';
    function tempnam(): string
    {
        global $tempnamReturn;
        return $tempnamReturn;
    }

    global $chmodReturn;
    $chmodReturn = false;
    function chmod(): bool
    {
        global $chmodReturn;
        return $chmodReturn;
    }

    global $filePutContentsReturn;
    $filePutContentsReturn = false;
    function file_put_contents(): bool
    {
        global $filePutContentsReturn;
        return $filePutContentsReturn;
    }

    global $unlinkReturn;
    $unlinkReturn = false;
    function unlink(): bool
    {
        global $unlinkReturn;
        return $unlinkReturn;
    }
