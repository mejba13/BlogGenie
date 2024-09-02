<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Post') }}
        </h2>
    </x-slot>

    <div class="flex justify-center self-start py-12">
        <div class="w-full max-w-2xl bg-white shadow-md rounded-lg p-6">
            @if (session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 text-red-600">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('posts.store') }}" method="POST">
                @csrf

                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                    <input id="title" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="text" name="title" value="{{ old('title') }}" required autofocus />
                </div>

                <!-- Green Full-Width Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="w-full bg-green-900 modern-button hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md transition-all duration-200">
                        {{ __('Generate and Save Post') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
