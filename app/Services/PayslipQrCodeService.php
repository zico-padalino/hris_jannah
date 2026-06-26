<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PayslipQrCodeService
{
    public const DISPLAY_SIZE = 120;

    public const PDF_PIXEL_SIZE = 420;

    public const LOGO_RATIO = 0.36;

    public function __construct(
        private readonly AppBrandingService $branding,
    ) {}

    public function dataUri(string $text, int $pixelSize = self::PDF_PIXEL_SIZE): string
    {
        return 'data:image/png;base64,'.base64_encode($this->generatePng($text, $pixelSize));
    }

    public function webLogoSize(int $qrSize = self::DISPLAY_SIZE): int
    {
        return (int) round($qrSize * self::LOGO_RATIO);
    }

    public function generatePng(string $text, int $pixelSize = self::PDF_PIXEL_SIZE): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required to generate payslip QR codes.');
        }

        $qrPng = $this->renderQrPng($text, $pixelSize);

        if (! $this->branding->hasLogo()) {
            return $qrPng;
        }

        return $this->mergeLogoOntoQr($qrPng, $this->branding->all()['logo_path']);
    }

    private function renderQrPng(string $text, int $pixelSize): string
    {
        $qrCode = Encoder::encode($text, ErrorCorrectionLevel::H(), 'UTF-8');
        $matrix = $qrCode->getMatrix();
        $moduleCount = $matrix->getWidth();
        $quietZone = 2;
        $totalModules = $moduleCount + ($quietZone * 2);
        $moduleSize = max(1, (int) floor($pixelSize / $totalModules));
        $imageSize = $moduleSize * $totalModules;

        $image = imagecreatetruecolor($imageSize, $imageSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $dark = imagecolorallocate($image, 30, 41, 59);
        imagefilledrectangle($image, 0, 0, $imageSize, $imageSize, $white);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if ($matrix->get($x, $y) !== 1) {
                    continue;
                }

                $px = ($x + $quietZone) * $moduleSize;
                $py = ($y + $quietZone) * $moduleSize;

                imagefilledrectangle(
                    $image,
                    $px,
                    $py,
                    $px + $moduleSize - 1,
                    $py + $moduleSize - 1,
                    $dark,
                );
            }
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    private function mergeLogoOntoQr(string $qrPng, string $logoPath): string
    {
        $qr = imagecreatefromstring($qrPng);

        if ($qr === false) {
            throw new RuntimeException('Invalid QR image.');
        }

        $size = imagesx($qr);
        $center = (int) ($size / 2);
        $logoDiameter = (int) round($size * self::LOGO_RATIO);

        imagealphablending($qr, true);
        imagesavealpha($qr, false);

        $white = imagecolorallocate($qr, 255, 255, 255);
        imagefilledellipse($qr, $center, $center, $logoDiameter, $logoDiameter, $white);

        $logoData = Storage::disk('public')->get($logoPath);
        $logo = imagecreatefromstring($logoData);

        if ($logo === false) {
            imagedestroy($qr);
            throw new RuntimeException('Invalid logo image for payslip QR code.');
        }

        $inner = (int) round($logoDiameter * 0.86);
        $logoWidth = imagesx($logo);
        $logoHeight = imagesy($logo);

        if ($logoWidth >= $logoHeight) {
            $newWidth = $inner;
            $newHeight = (int) max(1, round($logoHeight * ($inner / $logoWidth)));
        } else {
            $newHeight = $inner;
            $newWidth = (int) max(1, round($logoWidth * ($inner / $logoHeight)));
        }

        $dstX = $center - (int) ($newWidth / 2);
        $dstY = $center - (int) ($newHeight / 2);

        imagecopyresampled($qr, $logo, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $logoWidth, $logoHeight);
        imagedestroy($logo);

        ob_start();
        imagepng($qr);
        imagedestroy($qr);

        return (string) ob_get_clean();
    }
}
