{{-- resources/views/admin/tags/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Tag') }}
            </h2>
            <a href="{{ route('tags.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                {{ __('Back to Tags') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Form to create a new tag -->
                    <form method="POST" action="{{ route('tags.store') }}">
                        @csrf

                        <!-- Tag Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Tag Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name') }}" required autofocus />
                            @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Create Button -->
                        <div class="mt-4">
                            <x-primary-button>
                                {{ __('Create Tag') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
