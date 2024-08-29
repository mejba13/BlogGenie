
# BlogGenie

BlogGenie is a Laravel-based project that automates the generation and publishing of blog posts using the ChatGPT API. This project aims to streamline the content creation process, making it easier to keep your website up-to-date with fresh content.

## Features

- Automatically generate blog posts using the ChatGPT API
- Seamlessly publish content to your blog
- Customize and manage blog post templates
- Simple and intuitive interface for managing content

## Requirements

- PHP >= 8.0
- Composer
- Laravel >= 9.x
- ChatGPT API Key
- MySQL or MariaDB

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/bloggenie.git
   cd bloggenie
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Create a `.env` file:**
   ```bash
   cp .env.example .env
   ```

4. **Set up your environment variables:**

   - Update your `.env` file with your database credentials:
     ```env
     DB_DATABASE=bloggenie_db
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```
   - Add your ChatGPT API key:
     ```env
     CHATGPT_API_KEY=your_api_key
     ```

5. **Generate the application key:**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

7. **Run the development server:**
   ```bash
   php artisan serve
   ```

## Usage

1. **Create a new blog post:**

   You can generate a new blog post by accessing the `/generate-post` route. This will use the ChatGPT API to create content based on your predefined templates.

2. **Customize templates:**

   Modify the templates in the `resources/views/posts/` directory to control the structure and content of your generated blog posts.

3. **Publishing posts:**

   Generated posts can be reviewed and published directly from the admin interface.

## Contributing

If you'd like to contribute to this project, feel free to fork the repository and submit a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
