{{-- resources/views/admin/post_titles/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Post Title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('post_titles.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input id="title" name="title" type="text" required class="mt-1 block w-full">
                        </div>

                        <div class="mb-4">
                            <label for="publish_date" class="block text-sm font-medium text-gray-700">Publish Date</label>
                            <input id="publish_date" name="publish_date" type="date" required class="mt-1 block w-full">
                        </div>

                        <div>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
