<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Setup

### Requirements

-   PHP >= 8.2
-   Composer
-   Node.js and npm

### Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/your-repository.git
    cd your-repository
    ```

2. **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

3. **Copy the example environment file and modify the environment variables:**

    ```bash
    cp .env.example .env
    ```

4. **Set up the database connection in the `.env` file:**

    ```properties
    APP_URL=http://localhost:8000

    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=halo_db
    DB_USERNAME=root
    DB_PASSWORD=

    ```

5. **Generate an application key:**

    ```bash
    php artisan key:generate
    php artisan jwt:secret
    ```

6. **Run the database migrations:**

    ```bash
    php artisan migrate

    ```

7. **Create a Symbolic Link for the public folder**

    ```bash
    php artisan storage:link
    ```

8. **Start the local development server:**

    ```bash
    php artisan serve
    ```

Your Laravel application should now be up and running on `http://localhost:8000`.

---



---

## How to Test the APIs

### Prerequisites
- Ensure the application is running (`php artisan serve`).
- Use an API client like [Postman](https://www.postman.com/) or [Insomnia](https://insomnia.rest/) for testing.
- Authenticate and obtain a JWT token if endpoints are protected.

### Example API Endpoints

#### 1. Create Reservation
- **POST** `/api/reservations`
- **Body (JSON):**
  ```json
  {
    "hotel_id": 1,
    "room_type": "Deluxe Suite",
    "check_in_date": "2025-08-01",
    "check_out_date": "2025-08-05",
    "number_of_guests": 2
  }
  ```
- **Headers:**
  - `Authorization: Bearer <your_token>`

#### 2. List My Reservations
- **GET** `/api/reservations`
- **Headers:**
  - `Authorization: Bearer <your_token>`

#### 3. List All Reservations (Admin)
- **GET** `/api/admin/reservations`
- **Headers:**
  - `Authorization: Bearer <admin_token>`

#### 4. Payment Initialization
- Payment is automatically initialized after reservation creation. The response will include payment details.

#### 5. Webhook/Callback
- The payment provider will call your webhook endpoint to update payment and booking status.

### Notes
- If you try to reserve a room with more guests than its max capacity, you will receive an error message.
- Overlapping reservations for the same room and dates are not allowed.
- Use the seeder data or create your own hotels/rooms for testing.

---

### Example API Endpoints (continued)

#### 6. Create Booking (for an existing reservation)
- **POST** `/api/bookings`
- **Body (JSON):**
  ```json
  {
    "reservation_id": 1,
    "payment_method": "credit_card"
  }
  ```
- **Headers:**
  - `Authorization: Bearer <your_token>`
- **Note:** The reservation must exist and belong to the authenticated user. Booking is usually created automatically after payment, but this endpoint can be used for manual creation if needed.

#### 7. List My Bookings
- **GET** `/api/bookings`
- **Headers:**
  - `Authorization: Bearer <your_token>`

#### 8. List All Bookings (Admin)
- **GET** `/api/admin/bookings`
- **Headers:**
  - `Authorization: Bearer <admin_token>`

#### 9. Payment Status/Details
- **GET** `/api/payments/{payment_id}`
- **Headers:**
  - `Authorization: Bearer <your_token>`
- **Note:** Replace `{payment_id}` with the actual payment record ID. This endpoint returns payment status and details for a reservation/booking.

#### 10. Simulate Payment Callback (for testing)
- You can use tools like [Webhook.site](https://webhook.site/) or Postman to simulate a payment provider callback to your webhook endpoint (e.g., `/api/payment/webhook`).
- **POST** `/api/payment/webhook`
- **Body (JSON):**
  ```json
  {
    "tx_ref": "demo-...",
    "status": "success"
  }
  ```
- **Note:** This will update the payment and booking status in your system.

---
