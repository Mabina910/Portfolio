<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'dp.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ["success" => false, "errors" => []];

    $firstName = isset($_POST["FirstName"]) ? trim($_POST["FirstName"]) : "";
    $lastName = isset($_POST["LastName"]) ? trim($_POST["LastName"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $message = isset($_POST["message"]) ? trim($_POST["message"]) : "";
    $country = isset($_POST["country"]) ? $_POST["country"] : "";

    if (empty($firstName) || !preg_match("/^[a-zA-Z ]+$/", $firstName)) {
        $response["errors"]["firstNameErr"] = "Invalid first name.";
    }

    if (empty($lastName) || !preg_match("/^[a-zA-Z ]+$/", $lastName)) {
        $response["errors"]["lastNameErr"] = "Invalid last name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
        $response["errors"]["emailErr"] = "Invalid email (must be @gmail.com).";
    }

    if (!empty($message) && strlen($message) < 10) {
        $response["errors"]["messageErr"] = "Message must be at least 10 characters.";
    }


    if (empty($country)) {
        $response["errors"]["countryErr"] = "Please select a country.";
    }

    if (empty($response["errors"])) {
        $stmt = $conn->prepare("INSERT INTO contacts (first_name, last_name, email, message, country) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $message, $country);

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Form submitted successfully!";
        } else {
            $response["errors"]["dbErr"] = "Error saving data to database.";
        }

        $stmt->close();
    }
    echo "<pre>";
    print_r($_POST); 
    echo "</pre>";
    echo json_encode($response);
    exit();
}
?>
