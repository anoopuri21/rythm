<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class CataloguePublicationReviewService
{
    /** @return array{required: bool, reasons: list<string>} */
    public function assess(string $title, string $description): array
    {
        $content = Str::lower($title.' '.$description);
        $rules = [
            'source-retailer reference' => ['bajaao'],
            'warranty or guarantee claim' => ['warranty', 'guarantee'],
            'free item, lesson or trial claim' => ['free lesson', 'free trial', 'free ebook', 'e-book', 'ebook'],
            'shipping or return promise' => ['free shipping', 'return policy', 'day return', 'shipping policy'],
            'finance or discount promotion' => ['emi', 'cashback', 'limited time', 'lightning deal'],
            'bundle contents require verification' => ['bundle', 'includes picks', 'includes bag', 'accessory pack'],
            'open-box condition' => ['open box', 'b-stock', 'b stock'],
        ];

        $reasons = [];
        foreach ($rules as $reason => $needles) {
            if (Str::contains($content, $needles)) {
                $reasons[] = $reason;
            }
        }

        return [
            'required' => $reasons !== [],
            'reasons' => $reasons,
        ];
    }
}
