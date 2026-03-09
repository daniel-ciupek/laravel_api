<?php

namespace App\Http\Requests;

use App\Models\Priority;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\TaskInputParser;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'filled', 'max:255'],
            'priority_id' => ['nullable', Rule::exists(Priority::class, 'id')],
            'due_date' => ['nullable', 'date']

        ];
    }
     public function after(): array
    {
        return [
            function ($validator) {
                $name = $this->input('name');
                if ($name) {
                    $parser = app(TaskInputParser::class);
                    $parsed = $parser->parse($name);
                    if ($parsed === null) {
                        $validator->errors()->add('name', 'The name field is required.');
                    }
                }
            }
        ];
    }
}
