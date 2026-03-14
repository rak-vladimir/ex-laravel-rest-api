<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasStoreUpdateLogic;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    use HasStoreUpdateLogic;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isStore()) {
            return $this->user()->can('create', Post::class);
        }

        /** @var \App\Models\Post|null $post */
        $post = $this->route('post');

        return $post && $this->user()->can('update', $post);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'   => [
                $this->isStore() ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique('posts', 'title')->ignore($this->route('post')?->id)
            ],
            'content' => [
                $this->isStore() ? 'required' : 'sometimes',
                'string'
            ],
        ];
    }
}
