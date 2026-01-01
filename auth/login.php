<?php 
require '../includes/header.php';
require '../config/config.php'; 
if (isset($_SESSION['username'])){
    header('Location: '.APPURL.'/index.php');
    exit;
}
if (isset($_POST['login'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        echo "<script>alert('All fields are required.')</script>";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $login = $conn->prepare("SELECT * FROM users WHERE email = :email"); 
        $login->execute(['email' => $email]);

        $fetch = $login->fetch(PDO::FETCH_ASSOC);

        if ($fetch) { 
            if (password_verify($password, $fetch['password'])) {
                $_SESSION['username'] = $fetch['username'];
                $_SESSION['user_id'] = $fetch['id'];

                header("Location: ".APPURL."dashboard.php");
                exit;
            } else {
                echo "<script>alert('Incorrect password.')</script>";
            }
        } else {
            echo "<script>alert('No user found with this email.')</script>";
        }
    }
}
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form class="form-control mt-5" method="POST" action="login.php">
                <h4 class="text-center mt-3"> Login </h4>
                <div class="">
                    <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
                    <div class="">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email">
                    </div>
                </div>
                <div class="">
                    <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                    <div class="">
                        <input type="password" name="password" class="form-control" placeholder="Enter your password">
                    </div>
                </div>
                <button name="login" class="w-100 btn btn-lg btn-primary mt-4" type="submit">Login</button>
            </form>
        </div>
    </div>
</div>
<?php require '../includes/footer.php' ?>
