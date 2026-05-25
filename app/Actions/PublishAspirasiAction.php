<?php

namespace App\Actions;

use App\Models\Aspirasi;
use App\Models\User;
use Illuminate\Support\Arr;

class PublishAspirasiAction
{
    /**
     * @param array<string, mixed> $input
     */
    public function create(User $user, array $input): Aspirasi
    {
        return Aspirasi::query()->create(array_merge(
            $this->editableData($input),
            [
                'author_id' => $user->id,
                'status' => 'PUBLISHED',
                'featured' => false,
            ],
        ));
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(User $user, Aspirasi $aspirasi, array $input): Aspirasi
    {
        if ((int) $aspirasi->author_id !== (int) $user->id) {
            abort(404);
        }

        $aspirasi->update($this->editableData($input));

        return $aspirasi;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function editableData(array $input): array
    {
        return Arr::only($input, [
            'title',
            'slug',
            'body',
            'excerpt',
            'image',
            'seo_title',
            'category_id',
            'meta_description',
            'meta_keywords',
        ]);
    }
}
