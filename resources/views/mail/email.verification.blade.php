<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Pet Diaries</title>
</head>

<body>
<h4>Hello {{ $username }},</h4>
<p>Welcome to Pet Diaries, where every moment with your furry friends is a cherished memory!</p>
<p>We're thrilled to have you on board. As a member of Pet Diaries, you can store and share precious memories with your pets, creating a lasting digital diary of your experiences together.</p>
<p>Please take a moment to <a style="color: #116eac;" href="{{ $verificationUrl }}">verify your email by clicking me!</a>.</p>
<p>If you have any questions or need assistance, feel free to reach out. Our team is here to help!</p>
<p>Wishing you and your pets wonderful moments on Pet Diaries!</p>
<p>Kindest regards,<br>
    <span style="font-style: italic;">The Pet Diaries Team</span><br>
</p>
</body>

</html>
