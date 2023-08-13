<?php

include 'components/connect.php';

session_start();

class Order {
  // Khai báo các thuộc tính user_id, name, number, email, method, address, total_products và total_price
  private $user_id;
  private $name;
  private $number;
  private $email;
  private $method;
  private $address;
  private $total_products;
  private $total_price;

  // Khai báo constructor để khởi tạo các thuộc tính
  public function __construct($user_id, $name, $number, $email, $method, $address, $total_products, $total_price) {
    $this->user_id = $user_id;
    $this->name = $name;
    $this->number = $number;
    $this->email = $email;
    $this->method = $method;
    $this->address = $address;
    $this->total_products = $total_products;
    $this->total_price = $total_price;
  }

  // Khai báo các getter và setter cho các thuộc tính
  public function getUserID() {
    return $this->user_id;
  }

  public function setUserID($user_id) {
    $this->user_id = $user_id;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getNumber() {
    return $this->number;
  }

  public function setNumber($number) {
    $this->number = $number;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
  }

  public function getMethod() {
    return $this->method;
  }

  public function setMethod($method) {
    $this->method = $method;
  }

  public function getAddress() {
    return $this->address;
  }

  public function setAddress($address) {
    $this->address = $address;
  }

  public function getTotalProducts() {
    return$this->total_products;
   }
 
   public function setTotalProducts($total_products) {
     $this->total_products = $total_products;
   }
 
   public function getTotalPrice() {
     return $this->total_price;
   }
 
   public function setTotalPrice($total_price) {
     $this->total_price = $total_price;
   }
 
   // Khai báo phương thức place để đặt một đơn hàng
   public function place($conn) {
     // Truy vấn cơ sở dữ liệu để kiểm tra giỏ hàng của người dùng có mặt hàng nào không
     $check_cart = $conn->prepare("SELECT * FROM cart inner join users on cart.user_id = users.id WHERE user_id = ?");
     $check_cart->execute([$this->user_id]);
 
     // Nếu có mặt hàng trong giỏ hàng, thực hiện các bước sau
     if($check_cart->rowCount() > 0){
       // Truy vấn cơ sở dữ liệu để chèn đơn hàng vào bảng orders với các thông tin của người dùng và đơn hàng
       $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
       $insert_order->execute([$this->user_id, $this->name, $this->number, $this->email, $this->method, $this->address, $this->total_products, $this->total_price]);
 
       // Truy vấn cơ sở dữ liệu để xóa tất cả các mặt hàng trong giỏ hàng của người dùng
       $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
       $delete_cart->execute([$this->user_id]);
 
       // Hiển thị thông báo thành công
       echo "<span style = 'color: green';>Order placed successfully!</span>";
     }else{
       // Nếu không có mặt hàng trong giỏ hàng, hiển thị thông báo giỏ hàng trống
       echo 'Your cart is empty';
     }
   }
 }
 
 // Nếu có biến session user_id, gán nó cho biến user_id, ngược lại gán user_id là rỗng và chuyển hướng người dùng về trang đăng nhập người dùng
 if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
 }else{
    $user_id = '';
    header('location:user_login.php');
 };
 
 // Nếu có biến POST order, lọc và mã hóa các giá trị nhập vào và khởi tạo một đối tượng Order với các thông tin đó. Sau đó gọi phương thức place để đặt đơn hàng.
 if(isset($_POST['order'])){
 
    $name = $_POST['name'];
    $name = filter_var($name);
    $number = $_POST['number'];
    $number = filter_var($number);
    $email = $_POST['email'];
    $email = filter_var($email);
    $method = $_POST['method'];
    $method = filter_var($method);
    $address = $_POST['address'] ;
    $address = filter_var($address);
    $total_products = $_POST['total_products'];
    $total_price = $_POST['total_price'];
 
    // Tạo một đối tượng Order với các thông tin đã lọc và mã hóa
    $order = new Order($user_id, $name, $number, $email, $method, $address, $total_products, $total_price);
 
    // Gọi phương thức place để đặt đơn hàng
    $order->place($conn);
 
 }
 
 ?>
 

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Your orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price'].'VNĐ x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <div class="grand-total">Grand Total : <span><?= $grand_total; ?>VNĐ</span></div>
      </div>

      <h3>Place your orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash on delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paypal">Paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address:</span>
            <input type="text" name="address" placeholder="e.g. HaNoi" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Pin Code :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="place order">

   </form>

</section>


<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>