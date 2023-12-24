<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
</head>
<body>
<h4>Hello {{ ucfirst($details['admin_username']) }}, </h4>
<p>
    You have a new notification.<br>
    <strong>{{ ucfirst($details['author']) }}</strong> posted a new topic <strong>{{ $details['title'] }}</strong>.
</p>
<p>
    Kindest regards,<br>
    <span style="font-weight: bolder;">The Forum</span><br>
</p>

</body>
</html>
