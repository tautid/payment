<?php

namespace TautId\Payment\Abstracts;

use Illuminate\Http\Request;
use TautId\Payment\Data\Payment\PaymentData;
use TautId\Payment\Helpers\ImageHelper;

abstract class PaymentMethodDriverAbstract
{
    public function serviceImageUrl(string $service, bool $is_base64 = false, bool $is_grayscale = false): ?string
    {
        $image_filename = $this->getServiceImageFilename($service);

        if (empty($image_filename)) {
            return null;
        }

        $image_path = public_path("vendor/taut-payment/images/methods/{$image_filename}");

        if ($is_base64 && file_exists($image_path)) {
            try {
                if ($is_grayscale) {
                    return ImageHelper::convertImageToGrayscaleBase64($image_path);
                }

                return ImageHelper::convertImageToBase64($image_path);
            } catch (\Exception $e) {
                return asset("vendor/taut-payment/images/methods/{$image_filename}");
            }
        }

        return asset("vendor/taut-payment/images/methods/{$image_filename}");
    }

    abstract public function services(): array;

    abstract public function getServiceImageFilename(string $service): string;

    abstract public function isServiceRedirectType(string $service): bool;

    abstract public function createPayment(PaymentData $data): void;

    abstract public function checkPayment(PaymentData $data): void;

    abstract public function cancelPayment(PaymentData $data): void;

    abstract public function metaValidation(array $meta): void;

    abstract public function checkSignature(Request $request): bool;
}
