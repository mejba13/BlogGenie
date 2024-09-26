<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Display validation errors -->
                    @if ($errors->any())
                        <div class="mb-4">
                            <ul class="text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Edit Post Form -->
                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('title', $post->title) }}" required>
                        </div>

                        <!-- Trix Editor for Content -->
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <input id="content" type="hidden" name="content" value="{{ old('content', $post->content) }}">
                            <trix-editor input="content"></trix-editor>
                        </div>

                        <!-- Categories -->
                        <div class="mb-4">
                            <label for="categories" class="block text-sm font-medium text-gray-700">Categories</label>
                            <select name="categories[]" id="categories" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" multiple>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, $post->categories->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tags -->
                        <div class="mb-4">
                            <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                            <input type="text" name="tags" id="tags" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('tags', implode(',', $post->tags->pluck('name')->toArray())) }}">
                            <small class="text-gray-500">Enter comma-separated tags.</small>
                        </div>

                        <!-- Upload or Update Featured Image -->
                        <div class="mb-4">
                            <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                            <input type="file" name="featured_image" id="featured_image" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <small class="text-gray-500">Upload a new image to replace the current one.</small>

                            <!-- Display Current Featured Image -->
                            @if($post->featured_image_url)
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Current Featured Image</label>
                                    <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}" class="w-48 h-48 object-cover rounded shadow-sm">
                                </div>
                            @else
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">No Featured Image Available</label>
                                    <img src="https://via.placeholder.com/150" alt="No Image Available" class="w-48 h-48 object-cover rounded shadow-sm">
                                </div>
                            @endif
                        </div>

                        <!-- Video URL -->
                        <div class="mb-4">
                            <label for="video_url" class="block text-sm font-medium text-gray-700">Video URL</label>
                            <input type="url" name="video_url" id="video_url" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('video_url', $post->video_url) }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ $post->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <!-- Update Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition">
                                Update Post
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
