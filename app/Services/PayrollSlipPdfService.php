<?php

namespace App\Services;

use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlipSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PayrollSlipPdfService
{
    public function __construct(
        private readonly PayrollSlipService $slipService,
        private readonly PayrollSlipConfig $slipConfig,
        private readonly AppBrandingService $branding,
        private readonly PayslipQrCodeService $qrCodeService,
    ) {}

    public function generateAndStore(PayrollItem $item, PayrollPeriod $period): string
    {
        $slip = $this->slipService->build($item, $period);

        if (! ($slip['signature_approved'] ?? false)) {
            throw ValidationException::withMessages([
                'pdf' => __('pages.payroll_slip.pdf_not_ready'),
            ]);
        }

        $pdfBinary = $this->render($slip);
        $path = $this->storagePath($item, $slip['verification_code']);

        Storage::disk('local')->put($path, $pdfBinary);

        return $path;
    }

    public function ensureStored(PayrollItem $item, PayrollPeriod $period): string
    {
        $path = $this->generateAndStore($item, $period);

        PayrollSlipSignature::query()
            ->where('payroll_item_id', $item->id)
            ->update(['pdf_path' => $path]);

        return $path;
    }

  /** @param array<string, mixed> $slip */
    public function downloadFilename(array $slip): string
    {
        $employeeNumber = $slip['employee']->employee_number ?? 'EMP';
        $code = $slip['verification_code'];

        return sprintf('slip-%s-%s.pdf', $employeeNumber, $code);
    }

    /** @param array<string, mixed> $slip */
    private function render(array $slip): string
    {
        $logoSrc = $this->imageDataUri($this->branding->all()['logo_path'] ?? '');
        $signatureSrc = $this->imageDataUri($this->slipConfig->all()['signature_path'] ?? '');

        $html = view('payrolls.slip-pdf', [
            ...$slip,
            'app_name' => $this->branding->name(),
            'qr_src' => $this->qrCodeService->dataUri($slip['scan_text']),
            'logo_src' => $logoSrc,
            'signature_src' => $signatureSrc,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    private function storagePath(PayrollItem $item, string $verificationCode): string
    {
        $safeCode = preg_replace('/[^A-Za-z0-9\-]/', '_', $verificationCode) ?: 'slip';

        return sprintf('payroll/slips/%d/%s.pdf', $item->id, $safeCode);
    }

    private function imageDataUri(string $path): ?string
    {
        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
