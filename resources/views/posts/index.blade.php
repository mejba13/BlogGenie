<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Posts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @foreach($posts as $post)
                        <a href="{{ route('posts.show', $post->id) }}" class="block mb-6 p-6 bg-gray-50 rounded-lg shadow-md hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center gap-2 mb-4">
                                @foreach($post->categories as $category)
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded shadow-sm">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>

                            <h3 class="text-xl font-semibold mb-2">{{ $post->title }}</h3>
                            <p class="text-gray-700">
                                {{ Str::limit(strip_tags($post->content), 250, '...') }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($post->tags as $tag)
                                    <span class="inline-block bg-blue-500 text-blue-800 text-xs font-semibold py-1 px-3 rounded-full shadow-md hover:shadow-lg transition-shadow duration-200">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="mt-4 text-right">
                                <span class="inline-block bg-indigo-600 text-black text-sm font-semibold py-2 px-4 rounded">
                                    Read More
                                </span>
                            </div>
                        </a>
                    @endforeach

                    @if($posts->isEmpty())
                        <div class="text-center text-gray-500">
                            No posts found.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
