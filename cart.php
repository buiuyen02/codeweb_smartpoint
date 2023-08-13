<?php

include 'components/connect.php';

session_start();

class Cart {
  // Khai báo các thuộc tính user_id và cart_id
  private $user_id;
  private $cart_id;

  // Khai báo constructor để khởi tạo các thuộc tính
  public function __construct($user_id, $cart_id) {
    $this->user_id = $user_id;
    $this->cart_id = $cart_id;
  }

  // Khai báo các getter và setter cho các thuộc tính
  public function getUserID() {
    return $this->user_id;
  }

  public function setUserID($user_id) {
    $this->user_id = $user_id;
  }

  public function getCartID() {
    return $this->cart_id;
  }

  public function setCartID($cart_id) {
    $this->cart_id = $cart_id;
  }

  // Khai báo phương thức delete để xóa một mặt hàng khỏi giỏ hàng
  public function delete($conn) {
    // Truy vấn cơ sở dữ liệu để xóa mặt hàng theo cart_id
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$this->cart_id]);
  }

  // Khai báo phương thức deleteAll để xóa tất cả các mặt hàng trong giỏ hàng của người dùng
  public function deleteAll($conn) {
    // Truy vấn cơ sở dữ liệu để xóa tất cả các mặt hàng theo user_id
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$this->user_id]);
    // Chuyển hướng người dùng về trang giỏ hàng
    header('location:cart.php');
  }

  // Khai báo phương thức updateQty để cập nhật số lượng của một mặt hàng trong giỏ hàng
  public function updateQty($conn, $qty) {
    // Lọc và mã hóa giá trị nhập vào
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);
    // Truy vấn cơ sở dữ liệu để cập nhật số lượng theo cart_id
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $this->cart_id]);
    // Hiển thị thông báo thành công
    echo "<span style='color:Green;'>Cart quantity updated!</span>";
  }

}

// Nếu có biến session user_id, gán nó cho biến user_id, ngược lại gán user_id là rỗng và chuyển hướng người dùng về trang đăng nhập người dùng
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

// Nếu có biến POST delete, khởi tạo một đối tượng Cart và gọi phương thức delete để xóa một mặt hàng khỏi giỏ hàng
if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $cart = new Cart($user_id, $cart_id);
   $cart->delete($conn);
}

// Nếu có biến GET delete_all, khởi tạo một đối tượng Cart và gọi phương thức deleteAll để xóa tất cả các mặt hàng trong giỏ hàng của người dùng
if(isset($_GET['delete_all'])){
   $cart = new Cart($user_id, null);
   $cart->deleteAll($conn);
}

// Nếu có biến POST update_qty, khởi tạo một đối tượng Cart và gọi phương thức updateQty để cập nhật số lượng của một mặt hàng trong giỏ hàng
if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $cart = new Cart($user_id, $cart_id);
   $cart->updateQty($conn, $qty);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <style>
:root{
   --main-color:#2980b9;
   --orange:#f39c12;
   --red:#e74c3c;
   --black:#333;
   --white:#fff;
   --light-color:#666;
   --light-bg:#eee;
   --border:.2rem solid #696969;
   --box-shadow:0 .5rem 1rem rgba(0,0,0,.1);
   --yellow:#FFFF33;
   --pink:#FFCC66;
}
body{
   background-color: var(--white);
}
.btn{
   background-color: #FF8C00;
}

.header .flex .logo{
   font-size: 2.5rem;
   color: #000099;
}

.header .flex .logo span{
   color:var(--main-color);
}

.footer{
   background-color: var(--light-bg);
   /* padding-bottom: 7rem; */
}
.option-btn{
   background-color: var(--main-color);
}
</style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">

   <h3 class="heading">Shopping Cart</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="flex">
         <div class="price"><?= $fetch_cart['price']; ?>VNĐ</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="<?= $fetch_cart['quantity']; ?>">
         <button type="submit" class="fas fa-edit" name="update_qty"></button>
      </div>
      <div class="sub-total"> Sub total : <span><?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>VNĐ</span> </div>
      <input type="submit" value="delete item" onclick="return confirm('delete this from cart?');" class="delete-btn" name="delete">
   </form>
   <?php
   $grand_total += $sub_total;
      }
   }else{
      echo '<p class="empty">Your cart is empty</p>';
   }
   ?>
   </div>

   <div class="cart-total">
      <p>Grand Total : <span><?= $grand_total; ?>VNĐ</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('delete all from cart?');">Delete All Items</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>">Proceed To Checkout</a>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>