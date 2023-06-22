<?php

namespace App\Service;

use App\Entity\Participants;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class GetQRService
{
    public function __construct(
        private BuilderInterface $qrCodeBuilder
    )
    {
    }

    public function __invoke(Participants $participant): \Endroid\QrCode\Writer\Result\ResultInterface
    {
        return $this->qrCodeBuilder
            ->size(800)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->margin(10)
            // set path to logo, logo is in public/images/rfr.png
            ->logoPath(
                __DIR__ . '/../../public/assets/logo.png'
            )
            // background color of the QR code white
            ->logoPunchoutBackground(true)

            ->logoResizeToHeight(210)
            ->logoResizeToWidth(270)

            ->data(
                $this->getContent($participant)
            )
            ->build();
    }

    public function getContent(Participants $participant): string
    {
        return $participant->getScanCode();
    }
}