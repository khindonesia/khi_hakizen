<?php
    use function Laravel\Folio\{name};
    name('opini');

    $opinions = [
    (object)[
        'id' => 1,
        'title' => 'How to Improve Your PHP Skills',
        'image' => 'https://example.com/image1.jpg',
        'created_at' => '2025-04-10 10:00:00',
        'updated_at' => '2025-04-12 14:00:00',
        'user' => (object)[
            'name' => 'John Doe'
        ],
        'body' => 'This is a detailed article on how to improve your PHP skills, including practical tips, best practices, and recommended resources. It is meant for beginners as well as intermediate developers who want to improve their skills.'
    ],
    (object)[
        'id' => 2,
        'title' => 'The Importance of Security in Web Development',
        'image' => 'https://example.com/image2.jpg',
        'created_at' => '2025-04-09 09:00:00',
        'updated_at' => '2025-04-11 16:00:00',
        'user' => (object)[
            'name' => 'Jane Smith'
        ],
        'body' => 'Security is a major concern for web developers. In this article, we’ll explore why security is critical, the most common vulnerabilities, and how to protect your website from potential attacks.'
    ],
    (object)[
        'id' => 3,
        'title' => 'Understanding Laravel Eloquent Relationships',
        'image' => 'https://example.com/image3.jpg',
        'created_at' => '2025-04-08 08:30:00',
        'updated_at' => '2025-04-12 12:00:00',
        'user' => (object)[
            'name' => 'Emily Johnson'
        ],
        'body' => 'Laravel Eloquent provides a beautiful, simple ActiveRecord implementation for working with your database. In this post, we will go over the basics of defining relationships between models.'
    ],
    (object)[
        'id' => 4,
        'title' => 'Why JavaScript is the Future of Web Development',
        'image' => 'https://example.com/image4.jpg',
        'created_at' => '2025-04-07 15:00:00',
        'updated_at' => '2025-04-12 11:30:00',
        'user' => (object)[
            'name' => 'Michael Brown'
        ],
        'body' => 'JavaScript has become the cornerstone of web development. This post covers why JavaScript is so crucial in modern development and how to use it effectively for both frontend and backend development.'
    ]
];

$query = \App\Models\Post::whereHas('category', function ($query) {
    $query->where('name', 'Opini');
})->paginate(5);

?>

<x-layouts.marketing>
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading
                title="Opini"
                description="Our latest opini posts below."
                align="left"
            />
            
            {{-- @include('theme::partials.blog.categories') --}}

            <div class="grid gap-5 mx-auto mt-7 sm:grid-cols-2 lg:grid-cols-3">
                @include('theme::partials.opini.opini-loop', ['opinions' => $query])
            </div>
        </div>

        <div class="flex justify-center my-10">
            {{-- {{ $posts->links('theme::partials.pagination') }} --}}
        </div>

    </x-container>
</x-layouts.marketing>
