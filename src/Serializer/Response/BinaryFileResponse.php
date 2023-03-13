<?php

namespace FOPG\Component\UtilsBundle\Serializer\Response;

use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SfBinaryFileResponse;

class BinaryFileResponse extends SfBinaryFileResponse implements ResponseInterface
{

}
