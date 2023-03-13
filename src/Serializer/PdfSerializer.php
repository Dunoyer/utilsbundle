<?php

namespace FOPG\Component\UtilsBundle\Serializer;

use FOPG\Component\UtilsBundle\ShellCommand\ShellCommand;
use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;
use FOPG\Component\UtilsBundle\Serializer\Response\BinaryFileResponse;
use FOPG\Component\UtilsBundle\Serializer\Response\JsonResponse;

class PdfSerializer extends AbstractSerializer
{
  public static function getExtensions(): array {
    return ["pdf"];
  }

  public function render(): ResponseInterface {
    /** @var ?string $file */
    $file = $this->getFilename();
    return ($file) ? new BinaryFileResponse($file) : new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
  }
  /**
   * Extraction des pièces jointes depuis un PDF
   *
   * @param ShellCommand $command Commande courante (null si non initialisé)
   * @return array
   */
  public function getAssociatedFiles(): array
  {
    /** @var ?string $file */
    $file = $this->getFilename();
    /** @var string $tmpDir */
    $tmpDir = sys_get_temp_dir().'/'.uniqid();
    /** @var ShellCommand $command */
    $command = new ShellCommand();
    $command->execute([
      [
        "action" => "mkdir %tmp_dir%",
        "args" => [ "%tmp_dir%" => $tmpDir ]
      ],
      [
        "action" => "pdftk %source_file% unpack_files output %tmp_dir%",
        "args" => [ "%source_file%" => $file, "%tmp_dir%" => $tmpDir ]
      ],
      [
        "action" => "find %tmp_dir% -type f",
        "args" => [ "%tmp_dir%" => "$tmpDir" ]
      ]

    ]);

    if(false === $command->hasError())
    {
      return $command->getLastOutput();
    }

  }
}
