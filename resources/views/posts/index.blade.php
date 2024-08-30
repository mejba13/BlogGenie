<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Posts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @foreach ($posts as $post)
                        <div class="mt-4">
                            <h3 class="text-lg font-bold">
                                <a href="{{ route('posts.show', $post->id) }}">{{ $post->title }}</a>
                            </h3>
                            <p>{{ $post->content }}</p>
{{--                            <p><strong>Categories:</strong> {{ $post->categories->pluck('name')->join(', ') }}</p>--}}
{{--                            <p><strong>Tags:</strong> {{ $post->tags->pluck('name')->join(', ') }}</p>--}}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
