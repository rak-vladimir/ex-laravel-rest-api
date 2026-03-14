<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasStoreUpdateLogic;
use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    use HasStoreUpdateLogic;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isStore()) {
            return $this->user()->can('create', Comment::class);
        }

        /** @var \App\Models\Comment|null $comment */
        $comment = $this->route('comment');

        return $comment && $this->user()->can('update', $comment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => [
                $this->isStore() ? 'required' : 'sometimes',
                'string',
            ],
        ];
    }
}
