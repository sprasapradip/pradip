<?php include '../config.php'; ?>

<?php
if(isset($_POST['save'])){

    $position = $_POST['position'];
    $company = $_POST['company'];
    $description = $_POST['description'];
    $start = $_POST['start_year'];
    $end = $_POST['end_year'];
    $order = $_POST['sort_order'];

    $conn->query("INSERT INTO experience
        (position, company, description, start_year, end_year, sort_order)
        VALUES
        ('$position','$company','$description','$start','$end','$order')
    ");

    echo "Saved successfully!";
}
?>

<form method="post">

    <input type="text" name="position" placeholder="Position" required><br><br>

    <input type="text" name="company" placeholder="Company" required><br><br>

    <textarea name="description" placeholder="Description"></textarea><br><br>

    <input type="text" name="start_year" placeholder="Start Year"><br><br>

    <input type="text" name="end_year" placeholder="End Year"><br><br>

    <input type="number" name="sort_order" placeholder="Order"><br><br>

    <button type="submit" name="save">Save</button>

</form>