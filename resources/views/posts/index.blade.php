<!-- resources/views/posts/index.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Posts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($posts->isEmpty())
                        <p>No posts available.</p>
                    @else
                        @foreach($posts as $post)
                            <div class="mb-8 border-b pb-4">
                                <h3 class="text-2xl font-bold">{{ $post->title }}</h3>
                                <p class="text-gray-500">{{ $post->slug }}</p>
                                <p class="mt-4">{{ $post->content }}</p>
                                <div class="mt-4">
                                    <strong>Categories:</strong>
                                    @foreach($post->categories as $category)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                                <div class="mt-2">
                                    <strong>Tags:</strong>
                                    @foreach($post->tags as $tag)
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-green-200 dark:text-green-800">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
