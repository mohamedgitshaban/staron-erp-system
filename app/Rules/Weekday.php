<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Weekday implements ValidationRule
{
    protected $weekdays = ['Sunday','Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday','Starday'];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }
}
