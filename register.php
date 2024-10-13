<?php 
    include('includes/layout-header.php');
    
    // Redirect to account if the user is already logged in
    if(isset($_SESSION["userId"])){
        header("location: account.php");
        exit;
    }
    
    include('controllers/db.php');
    include('controllers/passenger.php');

    // Database connection
    $database = new Database();
    $db = $database->getConnection();

    // CSRF token generation
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if(isset($_POST["sign-up-submit"])){
        $new_passenger = new Passenger($db);
        
        $first_name = htmlspecialchars(trim($_POST["first_name"]), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(trim($_POST["last_name"]), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $address = htmlspecialchars(trim($_POST["address"]), ENT_QUOTES, 'UTF-8');
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $agree_terms = isset($_POST["agree_terms"]); // Check if checkbox is checked

        // Regular expressions for validation
        $name_pattern = "/^[a-zA-Z]+$/";
        $address_pattern = "/^[a-zA-Z\s]+$/"; // Allows spaces for address

        // Validation
        if (!preg_match($name_pattern, $first_name)) {
            $error = "First name can only contain letters.";
        } elseif (!preg_match($name_pattern, $last_name)) {
            $error = "Last name can only contain letters.";
        } elseif (!preg_match($address_pattern, $address)) {
            $error = "Address can only contain letters and spaces.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif (strlen($password) < 7) {
            $error = "Password must be at least 7 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!$agree_terms) {
            $error = "You must agree to the terms and conditions.";
        } elseif ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Invalid CSRF token.";
        } else {
            // Hash the password before saving
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Create the new passenger
            if ($new_passenger->create($first_name, $last_name, $email, $address, $hashed_password)) {
                header("Location: success.php");
                exit;
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
?>

<main>
    <div class="signup-container d-flex align-items-center justify-content-center">
        <div class="w-100 m-auto bg-white shadow-sm" style="max-width: 500px;">
            <div class="bg-primary p-3" style="background: rgb(51,122,183);background: radial-gradient(circle, rgba(51,122,183,1) 0%, rgba(4,92,167,1) 50%, rgba(0,137,255,1) 100%);">
                <h1 class="text-center">Create an Account</h1>
            </div>

            <div class="p-3">
                <?php
                    if (isset($error)) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>';
                    }
                ?>

                <form method="POST" action="" id="signupForm">
                    <!-- CSRF token field -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />

                    <div class="form-group">
                        <label for="first_name" style="color: black; font-weight: bold">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required />
                        <small id="firstNameError" class="form-text text-danger" style="display:none;">First name can only contain letters.</small>
                    </div>
                    <div class="form-group">
                        <label for="last_name" style="color: black; font-weight: bold">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required />
                        <small id="lastNameError" class="form-text text-danger" style="display:none;">Last name can only contain letters.</small>
                    </div>
                    <div class="form-group">
                        <label for="address" style="color: black; font-weight: bold">Address</label>
                        <input type="text" class="form-control" id="address" name="address" required />
                    </div>
                    <div class="form-group">
                        <label for="email" style="color: black; font-weight: bold">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required />
                    </div>
                    <div class="form-group">
                        <label for="password" style="color: black; font-weight: bold">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required />
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="toggle-password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" style="color: black; font-weight: bold">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="toggle-confirm-password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required />
                        <label class="form-check-label" for="agree_terms">I agree to the <a href="#" id="termsLink" style="color: skyblue;">terms and conditions</a></label>
                    </div>
                    <button type="submit" class="btn btn-block glow-button" name="sign-up-submit">Register</button>

                    <div class="text-center" style="color: black; font-weight: bold">
                        <span>Already have an account? </span>
                        <a href="login.php" style="color: skyblue; font-weight: bold">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include('includes/scripts.php')?>
<?php include('includes/layout-footer.php')?>

<script>
    // Modal handling for terms and conditions
    var modal = document.getElementById("termsModal");
    var termsLink = document.getElementById("termsLink");
    var span = document.getElementsByClassName("close")[0];

    termsLink.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Real-time validation for first name and last name fields
    document.getElementById('first_name').addEventListener('input', function() {
        var firstName = this.value;
        var firstNameError = document.getElementById('firstNameError');
        if (/[^a-zA-Z]/.test(firstName)) {
            firstNameError.style.display = 'block';  // Show error message
        } else {
            firstNameError.style.display = 'none';  // Hide error message
        }
    });

    document.getElementById('last_name').addEventListener('input', function() {
        var lastName = this.value;
        var lastNameError = document.getElementById('lastNameError');
        if (/[^a-zA-Z]/.test(lastName)) {
            lastNameError.style.display = 'block';  // Show error message
        } else {
            lastNameError.style.display = 'none';  // Hide error message
        }
    });

    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function(e) {
            var passwordField = (icon.id === 'toggle-password') ? document.getElementById('password') : document.getElementById('confirm_password');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            icon.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
</script>
