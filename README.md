# Flash-Sale Checkout API  
A high-concurrency flash-sale backend built with **Laravel 12** and **MySQL (InnoDB)**.  
The system ensures **no overselling**, supports **temporary holds**, **orders**, and an **idempotent payment webhook**.

---

## 🚀 Features

### 1. Product Endpoint
- `GET /api/products/{id}`
- Returns product details and accurate available stock.
- Uses caching for fast reads.

### 2. Create Hold
- `POST /api/holds`
- `{ "product_id": 1, "qty": 1 }`
- Creates a temporary 2-minute hold and reduces available stock.

### 3. Create Order
- `POST /api/orders`
- `{ "hold_id": 5 }`
- Creates an order using a valid, unexpired hold.

### 4. Payment Webhook (Idempotent)
- `POST /api/payments/webhook`
- Handles duplicate webhook calls safely.
- Finalizes stock only once.

---

## 📦 Installation & Setup

### **How to Run the Project**

```bash
# Install dependencies
composer install

# Copy .env and generate application key
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

### **How to Run the Project**# Start the Laravel development server
php artisan serve
---

## 📦 Testing 
```bash
# Install migrations
php artisan migrate --env=testing

# run the test
php artisan test


