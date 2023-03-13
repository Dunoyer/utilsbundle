<?php

namespace FOPG\Component\UtilsBundle\Contracts;

interface FilesystemInterface
{
    public const REGEXP_DIRECTORY = "/^(?<directory>.*)\/(?<filename>[^\/.]+)[.](?<extension>[^\/]+)$/i";
}
