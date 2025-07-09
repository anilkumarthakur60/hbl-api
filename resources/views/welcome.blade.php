<!-- Include Bootstrap CSS (only if not already included) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    #payment_form {
        max-width: 600px;
        margin: 40px auto;
        padding: 30px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    #payment_form .form-label {
        font-weight: 500;
        color: #333;
    }

    #payment_form .form-control {
        border-radius: 6px;
        padding: 10px 14px;
        border: 1px solid #ced4da;
        transition: border-color 0.2s;
    }

    #payment_form .form-control:focus {
        border-color: #ff4c02;
        box-shadow: 0 0 0 0.2rem rgba(255, 76, 2, 0.25);
    }

    #payment_form .btn-primary {
        background-color: #ff4c02;
        border: none;
        transition: background-color 0.2s ease;
    }

    #payment_form .btn-primary:hover {
        background-color: #e04300;
    }

    #payment_form .form-check-label a {
        color: #ff4c02;
        text-decoration: none;
    }

    #payment_form .form-check-label a:hover {
        text-decoration: underline;
    }
</style>

<form id="payment_form" action="{{ route('payment.store') }}" method="get">
    @csrf

    <a href="{{ route('payment.store') }}" class="btn btn-primary">Payment</a>

    <h4 class="mb-4 text-center">Payment Form</h4>

    <div class="mb-3">
        <label for="amount" class="form-label">Amount to Pay (NPR):</label>
        <input type="number" value="100" required name="amount" class="form-control" id="amount" placeholder="e.g. 100">
    </div>

    <div class="mb-3">
        <label for="price" class="form-label">Total Amount:</label>
        <input type="number" value="104" required name="price" class="form-control" id="price" readonly>
    </div>

    <div class="mb-3">
        <label for="trip" class="form-label">Trip Name:</label>
        <input type="text" required name="trip_name" value="hello" class="form-control" id="trip" placeholder="Name of the trip you're paying for">
    </div>

    <div class="mb-3">
        <label for="fullname" class="form-label">Full Name:</label>
        <input name="fullname" value="Test Name" required type="text" id="fullname" class="form-control" placeholder="Your full name">
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address:</label>
        <input type="email" name="email" value="test@gmail.com" class="form-control" id="email" placeholder="you@example.com">
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Contact Number:</label>
        <input type="tel" required name="contact_number" value="9843262634" class="form-control" id="phone" placeholder="e.g. 9800000000">
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" name="terms_conditions" required type="checkbox" id="inlineCheckbox1">
        <label class="form-check-label" for="inlineCheckbox1">
            I accept <a href="#" target="_blank">Terms & Conditions</a>
        </label>
    </div>

    <div class="d-grid">
        <button id="payment_submit_button" type="submit" class="btn btn-primary btn-lg">
            Proceed to Payment
        </button>
    </div>
</form>