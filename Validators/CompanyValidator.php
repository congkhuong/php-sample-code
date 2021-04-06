<?php
namespace App\Validators;

class CompanyValidator extends BaseValidator
{
    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [
        self::RULE_CREATE => [
            'name' => 'required|string|max:255',
            'kana_name' => 'string|nullable|max:255',
            'logo' => 'nullable|url',
        ],
        self::RULE_UPDATE => [
            'name' => 'sometimes|required|string|max:255',
            'kana_name' => 'string|nullable|max:255',
            'logo' => 'nullable|url',
        ],
    ];
}
