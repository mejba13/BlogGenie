<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Blog Post</title>
</head>
<body>
<h1>Generate a Blog Post</h1>
<form action="{{ route('blog.generate') }}" method="POST">
    @csrf
    <label for="prompt">Enter a prompt:</label>
    <textarea name="prompt" id="prompt" rows="4" required></textarea>
    <button type="submit">Generate</button>
</form>
</body>
</html>
