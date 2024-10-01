<x-app-layout>
    @section('title', $metaTitle) <!-- Meta title for SEO -->
    @section('meta_description', $metaDescription) <!-- Meta description for SEO -->

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>

    <div class="py-12 px-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 px-12 border-b border-gray-200">

                    <!-- Display the post's featured image (fixed square ratio) -->
                    @if($post->featured_image_url)
                        <div class="mb-6">
                            <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}"
                                 class="w-full max-w-full h-[800px] object-cover rounded-lg shadow-sm">
                        </div>
                    @endif

                    <!-- Author Info and Date -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.656 0 3-1.344 3-3s-1.344-3-3-3-3 1.344-3 3 1.344 3 3 3zm0 2c-2.762 0-5 2.238-5 5h10c0-2.762-2.238-5-5-5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg text-gray-700 font-semibold">{{ $post->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $post->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>

                    <!-- Display the post's categories -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            <strong>Categories:</strong>
                            @foreach($post->categories as $category)
                                <a href="#" class="text-indigo-500">{{ $category->name }}</a>@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    </div>

                    <!-- Display the post's tags -->
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <strong>Tags:</strong>
                            @foreach($post->tags as $tag)
                                <a href="#" class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold py-1 px-3 rounded-full shadow-md hover:shadow-lg transition-shadow duration-200">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Display the Table of Contents -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-2">Table of Contents</h3>
                        <ul class="list-disc pl-5">
                            <!-- Assuming the table of contents is part of the post content -->
                            {!! nl2br(e($post->toc)) !!}
                        </ul>
                    </div>

                    <!-- Display the post's content -->
                    <div class="post-content leading-relaxed text-gray-800">
                        {!! $post->content !!}
                    </div>

                    <div class="mt-6 text-right">
                        <a href="{{ route('posts.index') }}" class="inline-block bg-indigo-500 text-white py-2 px-4 rounded-full hover:bg-indigo-600">
                            Back to Posts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
