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


## Usage

### Creating a New Route

To create a new route, add the following to your `routes/base.php` file:

```php
use App\Providers\RouterProvider;

RouterProvider::get('/hello', function() {
    return 'Hello, World!';
});
```

### Connecting to docker

WIP