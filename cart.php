<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
$page_title = "Cart - Medico";

// Initialize cart as associative array: id => qty
if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];

// Add to cart from ?add=ID
if (isset($_GET["add"])) {
    $id = (int)$_GET["add"];
    if (isset($_SESSION["cart"][$id])) {
        $_SESSION["cart"][$id]++;
    } else {
        $_SESSION["cart"][$id] = 1;
    }
    header("Location: cart.php");
    exit;
}

// Remove from cart
if (isset($_GET["remove"])) {
    $id = (int)$_GET["remove"];
    unset($_SESSION["cart"][$id]);
    header("Location: cart.php");
    exit;
}

// Update quantity
if (isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = max(1, (int)$qty);
        $_SESSION["cart"][$id] = $qty;
    }
    header("Location: cart.php");
    exit;
}

include 'header.php';
$total = 0;
?>
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}

.cart-container {
    max-width: 700px;
    margin: 40px auto;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 30px 40px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
h2 {
    text-align: center;
    color: #1e90ff;
    margin-bottom: 30px;
}
table.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}
.cart-table th, .cart-table td {
    padding: 12px 8px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
.cart-table th {
    background: #f7f9fb;
    color: #333;
}
.cart-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.cart-btn, .cart-remove-btn {
    background: #1e90ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 16px;
    cursor: pointer;
    transition: background 0.2s;
}
.cart-remove-btn {
    background: #ff4d4d;
}
.cart-btn:hover { background: #005cbf; }
.cart-remove-btn:hover { background: #c0392b; }
.cart-total {
    text-align: right;
    font-size: 1.2em;
    margin-bottom: 20px;
}
.checkout-btn {
    display: block;
    margin: 0 auto;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 32px;
    font-size: 1.1em;
    cursor: pointer;
    transition: background 0.2s;
    text-decoration: none;
    text-align: center;
}
.checkout-btn:hover { background: #218838; }
.empty-cart {
    text-align: center;
    color: #888;
    font-size: 1.1em;
    margin: 40px 0;
}
</style>
<div class="cart-container">
    <h2>Your Cart</h2>
    <?php if (empty($_SESSION["cart"])): ?>
        <div class="empty-cart">Your cart is empty.</div>
    <?php else: ?>
    <form method="post">
    <table class="cart-table">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
        <?php
        foreach ($_SESSION["cart"] as $id => $qty) {
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
                $subtotal = $row['price'] * $qty;
                $total += $subtotal;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>₹" . number_format($row['price'], 2) . "</td>";
                echo "<td><input type='number' name='qty[$id]' value='$qty' min='1' style='width:60px;text-align:center;'></td>";
                echo "<td>₹" . number_format($subtotal, 2) . "</td>";
                echo "<td><a href='cart.php?remove=$id' class='cart-remove-btn'>Remove</a></td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
    <div class="cart-actions">
        <button type="submit" name="update_qty" class="cart-btn">Update Cart</button>
    </div>
    </form>
    <div class="cart-total"><strong>Total: ₹<?php echo number_format($total, 2); ?></strong></div>
    <a href="checkout.php?from_cart=true" class="checkout-btn">Proceed to Checkout</a>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>