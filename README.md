# VoltPHP

VoltPHP is a fast framework that is batteries included, has modular database support, is object-oriented, and has optional Docker support.

## Features

- **High Performance**: VoltPHP is designed for speed and efficiency.
- **Batteries Included**: Comes with a rich set of built-in features to get you started quickly.
- **Modular Database Support**: Easily integrate with various databases using a modular approach.
- **Object-Oriented**: Leverage the power of object-oriented programming to build robust applications.
- **Optional Docker Support**: Seamlessly run your applications in Docker containers for consistent environments, if desired.

## Getting Started

### Installation

1. **Clone the Repository**:
    ```sh
    git clone https://github.com/yourusername/voltphp.git
    cd voltphp
    ```

2. **Install Dependencies**:
    ```sh
    composer install
    ```

3. **Set Up Environment**:
    Copy the `.env.example` file to `.env` and configure your environment variables.

4. **Run the Application**:
    ```sh
    php -S localhost:8000 -t public
    ```

### Using Docker (Optional)

If you prefer to use Docker, follow these steps:

1. **Build the Docker Image**:
    ```sh
    docker build -t voltphp .
    ```

2. **Run the Docker Container**:
    ```sh
    docker run -p 8000:80 voltphp
    ```

## Usage

### Creating a New Route

To create a new route, add the following to your `routes/base.php` file:

```php
use App\Providers\RouterProvider;

RouterProvider::get('/hello', function() {
    return 'Hello, World!';
});# VoltPHP

VoltPHP is a fast framework that is batteries included, has modular database support, is object-oriented, and has optional Docker support.

## Features

- **High Performance**: VoltPHP is designed for speed and efficiency.
- **Batteries Included**: Comes with a rich set of built-in features to get you started quickly.
- **Modular Database Support**: Easily integrate with various databases using a modular approach.
- **Object-Oriented**: Leverage the power of object-oriented programming to build robust applications.
- **Optional Docker Support**: Seamlessly run your applications in Docker containers for consistent environments, if desired.

## Getting Started

### Installation

1. **Clone the Repository**:
    ```sh
    git clone https://github.com/yourusername/voltphp.git
    cd voltphp
    ```

2. **Install Dependencies**:
    ```sh
    composer install
    ```

3. **Set Up Environment**:
    Copy the `.env.example` file to `.env` and configure your environment variables.

4. **Run the Application**:
    ```sh
    php -S localhost:8000 -t public
    ```

### Using Docker (Optional)

If you prefer to use Docker, follow these steps:

1. **Build the Docker Image**:
    ```sh
    docker build -t voltphp .
    ```

2. **Run the Docker Container**:
    ```sh
    docker run -p 8000:80 voltphp
    ```

## Usage

### Creating a New Route

To create a new route, add the following to your `routes/base.php` file:

```php
use App\Providers\RouterProvider;

RouterProvider::get('/hello', function() {
    return 'Hello, World!';
});
test update