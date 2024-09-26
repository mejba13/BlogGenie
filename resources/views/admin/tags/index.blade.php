{{-- resources/views/admin/tags/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tags') }}
            </h2>
            <a href="{{ route('tags.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
                Add New Tag
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="mb-4 text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($tags->isEmpty())
                        <div class="text-center text-gray-600">
                            No tags found. <a href="{{ route('tags.create') }}" class="text-blue-500">Add a new tag</a>.
                        </div>
                    @else
                        <table class="min-w-full table-auto">
                            <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-gray-800 font-medium">Name</th>
                                <th class="px-6 py-3 text-left text-gray-800 font-medium">Slug</th>
                                <th class="px-6 py-3 text-center text-gray-800 font-medium">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($tags as $tag)
                                <tr>
                                    <td class="px-6 py-4 text-left">{{ $tag->name }}</td>
                                    <td class="px-6 py-4 text-left">{{ $tag->slug }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('tags.edit', $tag->id) }}" class="text-blue-500">Edit</a>
                                        <form action="{{ route('tags.destroy', $tag->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
