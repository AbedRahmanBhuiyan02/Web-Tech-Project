<?php
include("../../config/database.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $address = htmlspecialchars(trim($_POST['address']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $role = $_POST['role'];

    // Validation
    if (
        empty($name) || empty($email) || empty($password)
        || empty($address) || empty($phone) || empty($role)
    ) {
        $message = "All fields are required!";
    }

    elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters!";
    }

    else {

        // Check email already exists
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email already exists!";
        }

        else {

            // Hash Password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert User
            $insertQuery = "INSERT INTO users 
            (name, email, password_hash, role, address, phone)
            VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($insertQuery);

            $stmt->bind_param(
                "ssssss",
                $name,
                $email,
                $hashedPassword,
                $role,
                $address,
                $phone
            );

            if ($stmt->execute()) {
                $message = "Registration Successful!";
            } else {
                $message = "Registration Failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>

    <style>

        body{
            font-family: Arial;
            background:#f2f2f2;
        }

        .container{
            width:400px;
            margin:50px auto;
            background:white;
            padding:25px;
            border-radius:10px;
        }

        input, select, textarea{
            width:100%;
            padding:10px;
            margin-top:10px;
        }

        button{
            width:100%;
            padding:10px;
            background:green;
            color:white;
            border:none;
            margin-top:15px;
            cursor:pointer;
        }

        h2{
            text-align:center;
        }

        .message{
            text-align:center;
            color:red;
            margin-bottom:10px;
        }

    </style>
</head>

<body>

<div class="container">

    <h2>Registration Form</h2>

    <div class="message">
        <?php echo $message; ?>
    </div>

    <form method="POST" action="">

        <input type="text" name="name" placeholder="Enter Name">

        <input type="email" name="email" placeholder="Enter Email">

        <input type="password" name="password" placeholder="Enter Password">

        <textarea name="address" placeholder="Enter Address"></textarea>

        <input type="text" name="phone" placeholder="Enter Phone Number">

        <select name="role">

            <option value="">Select Role</option>

            <option value="admin">Admin</option>

            <option value="customer">Customer</option>

        </select>

        <button type="submit">Register</button>

    </form>

</div>

</body>
</html>