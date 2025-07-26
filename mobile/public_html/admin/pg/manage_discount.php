<?php
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../conn.php');
// Fetch existing discount values
$query = "SELECT * FROM discount WHERE id = 1"; // Assuming ID is 1
$result = $conn->query($query);
$discount = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $column = $_POST['column'];
    $value = $_POST['value'];

    if (!empty($id) && !empty($column) && !empty($value)) {
        $query = "UPDATE discount SET $column = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $value, $id);

        if ($stmt->execute()) {
            echo "<p>Discount updated successfully</p>";
        } else {
            echo "<p>Failed to update discount</p>";
        }

        $stmt->close();
    } else {
        echo "<p>Invalid input</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Discounts</title>
</head>
<body>
    <form action="manage_discount_form.php" method="POST">
        <input type="hidden" name="id" value="1"> <!-- Assuming ID is 1 for updates -->
        
        <label for="mtn">MTN:</label>
        <input type="text" id="mtn" name="column" value="mtn">
        <input type="text" name="value" placeholder="Enter MTN discount" value="<?php echo $discount['mtn']; ?>">
        <br>

        <label for="airtel">Airtel:</label>
        <input type="text" id="airtel" name="column" value="airtel">
        <input type="text" name="value" placeholder="Enter Airtel discount" value="<?php echo $discount['airtel']; ?>">
        <br>

        <label for="glo">Glo:</label>
        <input type="text" id="glo" name="column" value="glo">
        <input type="text" name="value" placeholder="Enter Glo discount" value="<?php echo $discount['glo']; ?>">
        <br>

        <label for="9mobile">9Mobile:</label>
        <input type="text" id="9mobile" name="column" value="9mobile">
        <input type="text" name="value" placeholder="Enter 9Mobile discount" value="<?php echo $discount['9mobile']; ?>">
        <br>

        <label for="smile">Smile:</label>
        <input type="text" id="smile" name="column" value="smile">
        <input type="text" name="value" placeholder="Enter Smile discount" value="<?php echo $discount['smile']; ?>">
        <br>

        <label for="swift">Swift:</label>
        <input type="text" id="swift" name="column" value="swift">
        <input type="text" name="value" placeholder="Enter Swift discount" value="<?php echo $discount['swift']; ?>">
        <br>

        <label for="spectranet">Spectranet:</label>
        <input type="text" id="spectranet" name="column" value="spectranet">
        <input type="text" name="value" placeholder="Enter Spectranet discount" value="<?php echo $discount['spectranet']; ?>">
        <br>

        <label for="dstv">DSTV:</label>
        <input type="text" id="dstv" name="column" value="dstv">
        <input type="text" name="value" placeholder="Enter DSTV discount" value="<?php echo $discount['dstv']; ?>">
        <br>

        <label for="gotv">GOTV:</label>
        <input type="text" id="gotv" name="column" value="gotv">
        <input type="text" name="value" placeholder="Enter GOTV discount" value="<?php echo $discount['gotv']; ?>">
        <br>

        <label for="startimes">Startimes:</label>
        <input type="text" id="startimes" name="column" value="startimes">
        <input type="text" name="value" placeholder="Enter Startimes discount" value="<?php echo $discount['startimes']; ?>">
        <br>

        <label for="eko">Eko:</label>
        <input type="text" id="eko" name="column" value="eko">
        <input type="text" name="value" placeholder="Enter Eko discount" value="<?php echo $discount['eko']; ?>">
        <br>

        <label for="ikeja">Ikeja:</label>
        <input type="text" id="ikeja" name="column" value="ikeja">
        <input type="text" name="value" placeholder="Enter Ikeja discount" value="<?php echo $discount['ikeja']; ?>">
        <br>

        <label for="aba">Aba:</label>
        <input type="text" id="aba" name="column" value="aba">
        <input type="text" name="value" placeholder="Enter Aba discount" value="<?php echo $discount['aba']; ?>">
        <br>

        <label for="abuja">Abuja:</label>
        <input type="text" id="abuja" name="column" value="abuja">
        <input type="text" name="value" placeholder="Enter Abuja discount" value="<?php echo $discount['abuja']; ?>">
        <br>

        <label for="benin">Benin:</label>
        <input type="text" id="benin" name="column" value="benin">
        <input type="text" name="value" placeholder="Enter Benin discount" value="<?php echo $discount['benin']; ?>">
        <br>

        <label for="enugu">Enugu:</label>
        <input type="text" id="enugu" name="column" value="enugu">
        <input type="text" name="value" placeholder="Enter Enugu discount" value="<?php echo $discount['enugu']; ?>">
        <br>

        <label for="ibadan">Ibadan:</label>
        <input type="text" id="ibadan" name="column" value="ibadan">
        <input type="text" name="value" placeholder="Enter Ibadan discount" value="<?php echo $discount['ibadan']; ?>">
        <br>

        <label for="jos">Jos:</label>
        <input type="text" id="jos" name="column" value="jos">
        <input type="text" name="value" placeholder="Enter Jos discount" value="<?php echo $discount['jos']; ?>">
        <br>

        <label for="kaduna">Kaduna:</label>
        <input type="text" id="kaduna" name="column" value="kaduna">
        <input type="text" name="value" placeholder="Enter Kaduna discount" value="<?php echo $discount['kaduna']; ?>">
        <br>

        <label for="kano">Kano:</label>
        <input type="text" id="kano" name="column" value="kano">
        <input type="text" name="value" placeholder="Enter Kano discount" value="<?php echo $discount['kano']; ?>">
        <br>

        <label for="port_harcourt">Port Harcourt:</label>
        <input type="text" id="port_harcourt" name="column" value="port_harcourt">
        <input type="text" name="value" placeholder="Enter Port Harcourt discount" value="<?php echo $discount['port_harcourt']; ?>">
        <br>

        <label for="yola">Yola:</label>
        <input type="text" id="yola" name="column" value="yola">
        <input type="text" name="value" placeholder="Enter Yola discount" value="<?php echo $discount['yola']; ?>">
        <br>

        <button type="submit">Update Discount</button>
    </form>
</body>
</html>
