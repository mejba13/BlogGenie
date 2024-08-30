<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>{{ $post->content }}</p>
{{--                    <p><strong>Categories:</strong> {{ $post->categories->pluck('name')->join(', ') }}</p>--}}
{{--                    <p><strong>Tags:</strong> {{ $post->tags->pluck('name')->join(', ') }}</p>--}}
{{--                    <p><strong>Meta Title:</strong> {{ $post->meta->where('meta_key', 'meta_title')->first()->meta_value }}</p>--}}
{{--                    <p><strong>Meta Description:</strong> {{ $post->meta->where('meta_key', 'meta_description')->first()->meta_value }}</p>--}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
