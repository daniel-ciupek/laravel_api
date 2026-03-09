<?php

namespace App\Http\Requests;

class UpdateTaskRequest extends StoreTaskRequest
{
    public function rules(): array
    {
        
        return array_merge(parent::rules(), [
           
            'name' => ['sometimes', 'required', 'string', 'filled', 'max:255'],
        ]);
    }
}
