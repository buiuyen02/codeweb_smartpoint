<?php

include 'components/connect.php';

session_start();

class User {
   private $id;
   private $name;
   private $email;
   private $password;
   private $conn;

   public function __construct($conn) {
      $this->conn = $conn;
   }

   public function register($name, $email, $password, $cpass) {
      $this->name = filter_var($name, FILTER_SANITIZE_STRING);
      $this->email = filter_var($email, FILTER_SANITIZE_STRING);
      $this->password = filter_var(sha1($password), FILTER_SANITIZE_STRING);
      $this->cpass = filter_var(sha1($cpass), FILTER_SANITIZE_STRING);

      $select_user = $this->conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select_user->execute([$this->email]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if($select_user->rowCount() > 0){
         return "<span style= 'color: red';>Email Already Exists!</span>";
      }else{
         if($this->password != $this->cpass){
            return "<span style= 'color: red';>Confirm Password Not Matched!</span>";
         }else{
            $insert_user = $this->conn->prepare("INSERT INTO `users`(name, email, password) VALUES(?,?,?)");
            $insert_user->execute([$this->name, $this->email, $this->cpass]);
            return "<span style= 'color: green';>Registered Successfully, Login Now Please!</span>";
         }
      }
   }
}

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){
   $user = new User($conn);
   $message[] = $user->register($_POST['name'], $_POST['email'], $_POST['pass'], $_POST['cpass']);
}

?>


<html>
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Register Now</h3>
      <input type="text" name="name" required placeholder="Enter your username" maxlength="20"  class="box">
      <input type="email" name="email" required placeholder="Enter your email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="Enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="Confirm your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="register now" class="btn" name="submit">
      <p>Already have an account?</p>
      <a href="user_login.php" class="option-btn">Login Now</a>
   </form>

</section>


<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>