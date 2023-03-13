<?php

namespace FOPG\Component\UtilsBundle\Serializer\Response;

use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse as SfJsonResponse;

class JsonResponse extends SfJsonResponse implements ResponseInterface
{

}
