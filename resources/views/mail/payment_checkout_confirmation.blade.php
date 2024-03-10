<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Pet Diaries | Payment Confirmation</title>
    <style>
        /* Reset some Bootstrap styles */
        body, figure, h1, h2, h3, p {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 50px auto; /* Center horizontally */
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h4 {
            color: #333333;
            margin-bottom: 10px;
        }

        p {
            color: #555555;
            margin-bottom: 15px;
        }

        a {
            color: #5ca15e;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            color: #3d7e4f;
        }

        .slogan{
            font-size: 9px;
            font-weight: bolder;
            font-style: normal;
            text-align: center;
        }

        .copyright {
            margin-top: 20px;
            text-align: center;
            color: #888888;
        }
    </style>
</head>

<body>
<div class="container">
    <h4>Hello {{ $username }},</h4>
    <p>We're delighted to inform you that your payment of $4.00 USD has been successfully processed.</p>
    <p>Your invoice is: <span style="font-weight: bolder">{{ $invoice }}</span></p>
    <p>Explore Pet Diaries to create and share delightful moments with your furry friends.
        If you have any questions or need assistance, our support team is here to help. Simply
        contact us at <a href="mailto:support@petdiaries.com">support@petdiaries.com</a>.</p>
    <p>Wishing you and your pets wonderful moments on Pet Diaries!</p>

    @include('mail.slogan')

</div>
</body>

</html>
