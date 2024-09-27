<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Post Titles') }}
            </h2>
            <a href="{{ route('admin.posts.create') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition">
                Create Post
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Display Session Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="min-w-full bg-white">
                        <thead>
                        <tr>
                            <th class="py-2 text-left">Featured</th>
                            <th class="py-2 text-left">Title</th>
                            <th class="py-2 text-left">Publish Date</th>
                            <th class="py-2 text-left">Content Preview</th>
                            <th class="py-2 text-left">Status</th>
                            <th class="py-2 text-left">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($posts as $post)
                            <tr class="border-b">
                                <!-- Featured Image Thumbnail -->
                                <td class="py-4 px-6">
                                    @if($post->featured_image_url)
                                        <img src="{{ asset($post->featured_image_url) }}" alt="{{ $post->title }}" class="w-16 h-16 object-cover rounded">
                                    @else
                                        <img src="https://via.placeholder.com/64" alt="No Image" class="w-16 h-16 object-cover rounded">
                                    @endif
                                </td>

                                <!-- Post Title -->
                                <td class="py-4 px-6 text-gray-800">
                                    {{ $post->title }}
                                </td>

                                <!-- Publish Date with Safe Null Handling -->
                                <td class="py-4 px-6 text-gray-500">
                                    {{ optional($post->published_at)->format('Y-m-d') ?? 'Not Published' }}
                                </td>

                                <!-- Content Preview (shortened content) -->
                                <td class="py-4 px-6 text-gray-600">
                                    {{ Str::limit(strip_tags($post->content), 100, '...') }}
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 text-gray-600">
                                    {{ ucfirst($post->status) }}
                                </td>

                                <!-- Actions (View / Edit / Delete) -->
                                <td class="py-4 px-6">
                                    <a href="{{ route('admin.posts.show', $post->id) }}" class="text-blue-500 hover:text-blue-700 mr-4">View</a>
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="text-blue-500 hover:text-blue-700 mr-4">Edit</a>
                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-gray-500">No posts found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="mt-6">
                        {{ $posts->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
