# Demo Loan Application
### Prerequisites

Basic knowledge of building applications with Laravel will be of help in this tutorial. Also, you need to ensure that you have installed Composer globally to manage dependencies.

What things you need to install the software. 

1. We need [composer](https://getcomposer.org/download/) installed in local system.
2. We are using Laravel 9.x and it requires a minimum PHP version of 8.0.I am using macOS and I am use=ing [MAMP](https://www.mamp.info/en/downloads/) for PHP, APACHE and MYSQL related operations.
3. Git

### Getting Started Installation

Our Laravel API will be used to create a loan, approve it and to pay installments of loan. 

1. To begin, run the following command to clone the aspire test project using Git:

    ```$ git clone https://github.com/kevinmakwana/loan-application.git```

2. Next, move into the new project’s folder and install all its dependencies:

    ``` 
    $ cd loan-application
    $ composer install
    ```

3. Next, create a `.env` file at the root of the project and populate it with the content found in the `.env.example` file. You can do this manually or by running the command below:

    ```$ cp .env.example .env```

4. Now generate the Laravel application key for this project with:

    ```$ php artisan key:generate```

5. Setting Up the .env file

    * Created a `demo_loan_application` in MySQL and `test.sqlite` file in the database folder. This file will be used to interact with our testing database and maintain a separate configuration from the main database. Next, replace the few environment variables from `.env.testing` in your `.env` file.

6. Migrate and Seed application by following command

    ```$ php artisan migrate --seed```

7. You can now run the application with

    ```$ php artisan serve```
    
    * There is not much to see here as this is just a default page for a newly installed Laravel project.

8. Lastly, use the following command to run PHPUnit from the terminal.

    ```$ vendor/bin/phpunit```

    or

    ```$ php artisan test```

9. Postman documentation URL

    * You can visit [Postman Documentation](https://documenter.getpostman.com/view/2318961/2s84Dvrziq) over here.

10. Postman collection

    * You can find postman collection inside `postman_collection/DEMO-LOAN-APPLICATION.postman_collection.json` and you can import it in your postman. Postman environment variables

    ```
    url : http://127.0.0.1:8080/api
    token : put generated token on register and login api
    ```
