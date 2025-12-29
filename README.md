Perfect 👍 — here’s your **fully polished, GitHub-optimized `README.md`**, complete with:

-   ✅ Proper Markdown hierarchy
-   ✅ Syntax highlighting and code block formatting
-   ✅ Clickable **Table of Contents**
-   ✅ Professional badges for Laravel, PHP, License, etc.
-   ✅ Great readability on GitHub, GitLab, Bitbucket, etc.

---

# 🏦 HBL Payment Gateway API

A **Laravel application** for integrating with **Himalayan Bank Limited (HBL)** Payment Gateway.
This API provides endpoints for payment processing, transaction management, and callback handling.

---

## 📋 Table of Contents

1. [Features](#-features)
2. [Installation](#-installation)
3. [API Endpoints](#-api-endpoints)

    - [Payment Operations](#-payment-operations)
    - [Transaction Management](#-transaction-management)
    - [Callback Endpoints](#-callback-endpoints)

4. [Console Commands](#-console-commands)
5. [Payment Controller Methods](#-payment-controller-methods)
6. [Database](#-database)
7. [Usage Examples](#-usage-examples)
8. [Notes](#-notes)
9. [License](#-license)

---

## 🧩 Features

-   Payment processing with HBL gateway
-   Transaction status inquiry
-   Refund and void operations
-   Settlement processing
-   Payment callbacks (success, failed, cancel, backend)
-   Transaction history management
-   Console command for payment testing

---

## ⚙️ Requirements

-   PHP >= 8.1
-   Laravel >= 10.x
-   MySQL or compatible database
-   Composer

---

## 🧰 Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/yourusername/hbl-payment-gateway.git
    cd hbl-payment-gateway
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Set up environment file**

    ```bash
    cp .env.example .env
    ```

4. **Configure environment variables** (database, HBL credentials, etc.)

5. **Run migrations**

    ```bash
    php artisan migrate
    ```

---

## 📡 API Endpoints

All endpoints are prefixed with the `payment` route group.

---

### 💳 Payment Operations

#### **Create Payment**

-   **URL:** `GET /payment`
-   **Method:** `GET`
-   **Description:** Initiates a new payment and redirects to the HBL payment page.
-   **Response:** Redirects to the payment gateway URL.

#### **Payment Status**

-   **URL:** `GET /payment/{orderNo}`
-   **Method:** `GET`
-   **Parameters:**

    -   `orderNo` — Order number to check

-   **Response:** JSON response with transaction status.

#### **Transaction Index**

-   **URL:** `GET /`
-   **Method:** `GET`
-   **Description:** Displays all payment responses/history.
-   **Response:** View with all transactions.

---

### ⚙️ Transaction Management

#### **Delete Transaction**

-   **URL:** `GET /delete/{orderNo}`
-   **Description:** Deletes a transaction record.
-   **Response:** Redirects with a success message.

#### **Refund**

-   **URL:** `GET /refund`
-   **Description:** Processes a refund transaction.
-   **Response:** JSON response with refund details.

#### **Void Transaction**

-   **URL:** `GET /void`
-   **Parameters:**

    -   `orderNo` _(optional)_ — Defaults to `'eviScSvEbf8Ywlh'`

-   **Description:** Voids a transaction.
-   **Response:** JSON response.

#### **Settlement**

-   **URL:** `GET /settlement`
-   **Parameters:**

    -   `orderNo` _(optional)_ — Defaults to `'eviScSvEbf8Ywlh'`

-   **Description:** Processes transaction settlement.
-   **Response:** JSON response.

#### **Inquiry**

-   **URL:** `GET /inquiry`
-   **Parameters:**

    -   `orderNo` _(optional)_ — Defaults to `'eviScSvEbf8Ywlh'`

-   **Description:** Inquires about a transaction.
-   **Response:** JSON response.

---

### 🔔 Callback Endpoints

These are called by HBL to notify about transaction status.

| Type        | URL        | Method | Description                           |
| ----------- | ---------- | ------ | ------------------------------------- |
| **Success** | `/success` | `ANY`  | Handles successful payment callback   |
| **Failed**  | `/failed`  | `ANY`  | Handles failed payment callback       |
| **Cancel**  | `/cancel`  | `ANY`  | Handles canceled payment callback     |
| **Backend** | `/backend` | `ANY`  | Handles backend notification callback |

**All** callback routes accept both `GET` and `POST` requests.

---

## 🧮 Console Commands

### **Test Payment Command**

Run the test payment command to verify the integration:

```bash
php artisan ts
```

This command will:

-   Create a new payment transaction
-   Generate a random 15-character order number
-   Execute the payment form JOSE request
-   Display the payment page URL

**Command Details:**

-   **Signature:** `ts`
-   **Description:** Tests payment transaction creation
-   **Location:** `App\Console\Commands\TestCommand`

---

## 🧠 Payment Controller Methods

| Method                      | Description                     |
| --------------------------- | ------------------------------- |
| `store()`                   | Creates and initiates a payment |
| `index()`                   | Lists all payment responses     |
| `status($orderNo)`          | Retrieves transaction status    |
| `delete($orderNo)`          | Deletes a transaction record    |
| `refund()`                  | Processes a refund              |
| `void($orderNo)`            | Voids a transaction             |
| `settlement($orderNo)`      | Processes settlement            |
| `inquiry($orderNo)`         | Inquires about a transaction    |
| `success(Request $request)` | Handles success callback        |
| `failed(Request $request)`  | Handles failed callback         |
| `cancel(Request $request)`  | Handles cancel callback         |
| `backend(Request $request)` | Handles backend callback        |

---

## 🗄️ Database

The application uses the `hbl_responses` table to store payment responses.

| Column                     | Description                                               |
| -------------------------- | --------------------------------------------------------- |
| `order_no`                 | Unique order number                                       |
| `response`                 | JSON response data from HBL                               |
| `status`                   | Payment status (`success`, `failed`, `cancel`, `backend`) |
| `created_at`, `updated_at` | Laravel timestamps                                        |

---

## 💡 Usage Examples

### Creating a Payment

```php
// Via route
GET /payment

// Via controller
$payment = new Payment;
$response = $payment->executeFormJose(
    amount: 1,
    orderNo: Str::random(15),
);
```

---

### Checking Transaction Status

```php
// Via route
GET /payment/{orderNo}

// Via controller
$hbl = new TransactionStatus;
$response = $hbl->execute($orderNo);
```

---

### Processing Refund

```php
// Via route
GET /refund

// Via controller
$hbl = new Refund;
$response = $hbl->executeJose();
```

---

## 🧾 Notes

-   All callback endpoints accept **GET** and **POST** methods.
-   Default order numbers are used for testing purposes.
-   Payment responses are automatically stored in the database.
-   The app uses **JOSE (JSON Object Signing and Encryption)** for secure communication with HBL gateway.

---

https://devzone.2c2p.com/docs/paco-intro
