    <!-- Include Bootstrap CSS (only if not already included) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('sweetalert::alert')
    @include('sweetalert::alert', ['cdn' => "https://cdn.jsdelivr.net/npm/sweetalert2@9"])

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

        pre {
            margin: 0;
            font-family: inherit;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>

    <!-- payment credentials -->
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Credentials</h5>
                <p class="card-text">
                    <strong>Card Number:</strong> 5399 3300 0001 2640
                    <br>
                    <strong>Expiry Date:</strong> 04/27
                    <br>
                    <strong>CVV:</strong> 734
                </p>
            </div>
        </div>
    </div>

    <form id="payment_form" action="{{ route('payment.store') }}" method="get">
        @csrf

        <a href="{{ route('payment.store') }}" class="btn btn-primary">Payment</a>

        <h4 class="mb-4 text-center">Payment Form</h4>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount to Pay (NPR):</label>
            <input type="number" value="1" required name="amount" class="form-control" id="amount" placeholder="e.g. 1">
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


        <div class="d-grid">
            <button id="payment_submit_button" type="submit" class="btn btn-primary btn-lg">
                Proceed to Payment
            </button>
        </div>
    </form>
    @if ($responses->count())
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment Responses</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Response</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($responses as $response)
                            <tr>
                                <td>{{ $response->id }}</td>
                                <td>
                                    <pre class="mb-0">{{ json_encode($response->response, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td>{{ $response->status }}</td>
                                <td>
                                    <a href="{{ route('payment.status', $response->order_no) }}" target="_blank" class="btn {{ $response->status == 'success' ? 'btn-success' : 'btn-danger' }}">
                                        See Details
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('payment.delete', $response->order_no) }}" class="btn btn-secondary">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
