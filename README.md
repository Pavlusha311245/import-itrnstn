# OO Program

## About the project

This project is console application. Mechanism which reads a file from command line. The file contains a list of
products. The program will read the file and
import the products into a database.

### Libraries used

- [Laravel zero](https://laravel-zero.com/)
- [Laravel Excel](https://laravel-excel.com/)

## Run the project locally using development server

1. Clone the repository
2. Run `composer install`
3. Setup database in `.env` file
4. Run `php artisan migrate`
5. Run `php artisan import:products <path_to_file>` where `<path_to_file>` is the path to the file you want to import

## Run the project using docker

1. Clone the repository
2. Run `docker-compose up -d`
3. Run `docker-compose exec <app> php artisan import:products <path_to_file>` where `<app>` is the name of the app
   container and `<path_to_file>` is the path to the file you want to import

## Development

### Linting

```bash
composer lint
```

### Testing

```bash
composer test
```

## How to import products

#### Supported format

Run the following command:

```bash
php artisan import:products <path_to_file>
```

_change `<path_to_file>` to the path of the file you want to import_

### Rule of importing products

- The file must be a CSV file
- The file must have the following columns:
    - Product Code
    - Product Name
    - Product Description
    - Stock
    - Cost in GBP
    - Discontinued
- Rows with cost in GBP less than 5 will be ignored and stock less than 10 will be ignored
- Any item with cost more than 1000 will be ignored
- Items marked as discontinued will be imported, but will have the discontinued date set as the current date.
- Failed imports will be logged.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
