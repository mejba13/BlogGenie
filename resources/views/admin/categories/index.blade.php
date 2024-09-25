<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Categories') }}
            </h2>
            <a href="{{ route('categories.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
                Add New Category
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

                    @if ($categories->isEmpty())
                        <div class="text-center text-gray-600">
                            No categories found. <a href="{{ route('categories.create') }}" class="text-blue-500">Add a new category</a>.
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
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 text-left">{{ $category->name }}</td>
                                    <td class="px-6 py-4 text-left">{{ $category->slug }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('categories.edit', $category->id) }}" class="text-blue-500">Edit</a>
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 ml-4">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
{{--                            {{ $categories->links() }} --}}{{-- Pagination links --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
