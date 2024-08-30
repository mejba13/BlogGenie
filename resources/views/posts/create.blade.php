<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="font-sans antialiased bg-gray-100">

<div class="min-h-screen">
    <!-- Page Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Post
            </h2>
        </div>
    </header>

    <!-- Page Content -->
    <main class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="mb-4 text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('posts.store') }}" method="POST">
                        @csrf

                        <!-- Title Field -->
                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700">
                                Title
                            </label>
                            <input id="title" class="block mt-1 w-full" type="text" name="title" required />
                        </div>

                        <!-- Slug Field -->
                        <div class="mb-4">
                            <label for="slug" class="block font-medium text-sm text-gray-700">
                                Slug
                            </label>
                            <input id="slug" class="block mt-1 w-full" type="text" name="slug" required />
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Generate and Save Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
