<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>{{ __("You're logged in!") }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Report Summary</h3>
                    <ul>
                        <li>Total Posts: {{ $postCount }}</li>
                        <li>Total Categories: {{ $categoryCount }}</li>
                        <li>Total Tags: {{ $tagCount }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
