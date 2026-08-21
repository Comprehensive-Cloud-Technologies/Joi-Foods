<?php
$app_name = config_item('application_name') ?: 'Joy Foods';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($app_name); ?> || Account Deletion Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f9;
        }

        .form-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-container h1 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #333;
        }

        .form-container p {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #555;
        }

        .form-container input[type="text"],
        .form-container input[type="email"] {
            width: calc(100% - 24px);
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            display: block;
            text-align: center;
        }

        .form-container button {
            background-color: #BD3839;
            color: #fff;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            width: 100%;
            margin-top: 10px;
        }

        .form-container button:hover {
            background-color: #9c2c2d;
        }

        .form-container button:disabled {
            background-color: #d99a9a;
            cursor: not-allowed;
        }

        .note {
            font-size: 0.85rem;
            color: #888;
            margin-top: 18px;
        }

        .toast {
            visibility: hidden;
            max-width: 320px;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 12px;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 1rem;
            z-index: 1000;
        }

        .toast.error {
            background-color: #dc3545;
        }

        .toast.show {
            visibility: visible;
            animation: fadeInOut 3.5s;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0;
            }

            20%,
            80% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1><?php echo htmlspecialchars($app_name); ?></h1>
        <p>Submit a request to delete your account.</p>
        <form id="deleteAccountForm">
            <input type="text" id="company_code" placeholder="Enter your company code" required>
            <input type="email" id="email" placeholder="Enter your email" required>
            <button type="submit" id="submitBtn">Submit Request</button>
        </form>
        <p class="note">We will verify your details and process your account deletion request.</p>
    </div>

    <div id="toast" class="toast">Your delete request has been submitted successfully.</div>

    <script src="https://taxfilr.com/assets/js/vendors/jquery-3.6.0.min.js"></script>
    <script>
        function showToast(message, isError) {
            var toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.toggle('error', !!isError);
            toast.classList.add('show');
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3500);
        }

        document.getElementById('deleteAccountForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var companyCodeInput = document.getElementById('company_code');
            var emailInput = document.getElementById('email');
            var submitBtn = document.getElementById('submitBtn');

            if (companyCodeInput.value.trim() === '' || emailInput.value.trim() === '') {
                showToast('Please enter your company code and email.', true);
                return;
            }

            submitBtn.disabled = true;

            $.ajax({
                url: '<?php echo base_url('page/submit_delete_account'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    company_code: companyCodeInput.value.trim(),
                    email: emailInput.value.trim()
                },
                success: function(response) {
                    if (response.status === 200) {
                        showToast(response.message || 'Your delete request has been submitted successfully.', false);
                        companyCodeInput.value = '';
                        emailInput.value = '';
                    } else {
                        showToast(response.message || 'Failed to send delete request. Please try again.', true);
                    }
                },
                error: function() {
                    showToast('An error occurred. Please try again.', true);
                },
                complete: function() {
                    submitBtn.disabled = false;
                }
            });
        });
    </script>
</body>

</html>
