<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $presence = $this->isMethod('put') ? 'required' : 'sometimes';

        return [
            'user_id' => [$presence, 'integer', 'exists:users,id'],
            'title' => [$presence, 'string', 'max:255'],
            'body' => [$presence, 'string'],
        ];
    }

    protected function passedValidation(): void
    {
        if ($this->isMethod('patch') && array_intersect(
            ['user_id', 'title', 'body'],
            array_keys($this->validated()),
        ) === []) {
            throw ValidationException::withMessages([
                'post' => ['At least one of user_id, title, or body is required.'],
            ]);
        }
    }
}
