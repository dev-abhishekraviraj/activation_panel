<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidM3U implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_file($value->getRealPath())) {
            $fail("The :attribute must be a valid file.");
            return;
        }
    
        $content = file_get_contents($value->getRealPath());
    
        if (!str_starts_with(trim($content), '#EXTM3U')) {
            $fail("The :attribute is not a valid M3U file (missing #EXTM3U).");
            return;
        }
    
        if (!preg_match('/^#EXTINF:/m', $content)) {
            $fail("The :attribute contains no playlist entries.");
        }
    }
}
