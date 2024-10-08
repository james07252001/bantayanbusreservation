<?php
include('db.php');

// Check if a file is uploaded
if (isset($_FILES['upload_id']) && $_FILES['upload_id']['error'] == 0) {
    // Define the directory where files will be saved
    $target_dir = "uploads/"; // Make sure the 'uploads/' directory exists and is writable

    // Get the file extension
    $file_extension = pathinfo($_FILES['upload_id']['name'], PATHINFO_EXTENSION);

    // Generate a unique filename
    $file_name = uniqid() . '.' . $file_extension;

    // Set the full file path
    $target_file = $target_dir . $file_name;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['upload_id']['tmp_name'], $target_file)) {
        // File uploaded successfully, save the file name in the database

        // Prepare your booking insertion query
        $schedule_id = $_POST['schedule_id'];
        $passenger_id = $_POST['passenger_id'];
        $seat_num = $_POST['seat_num'];
        $total = $_POST['total'];
        $passenger_type = $_POST['passenger_type'];
        $routeName = $_POST['routeName'];
        
        // Use prepared statements to insert into the database
        $stmt = $db->prepare("INSERT INTO tblbook (schedule_id, passenger_id, seat_num, total, passenger_type, upload_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $schedule_id, $passenger_id, $seat_num, $total, $passenger_type, $file_name);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Booking created successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error creating booking."]);
        }
    } else {
        // File upload failed
        echo json_encode(["success" => false, "message" => "Error uploading file."]);
    }
} else {
    // No file uploaded, handle accordingly
    echo json_encode(["success" => false, "message" => "No ID proof uploaded."]);
}
?>
