<?php
header('Content-Type: application/json');

// Include your database connection file
// Adjust the path if necessary
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/database.php';


$searchTerm = $_GET['q'] ?? ''; // Get search term if provided by Select2

$results = [];

try {
    // Assuming $conn is your PDO or mysqli connection object from connection.php
    // Modify the table and column names if they are different
    $sql = "SELECT id, name as text FROM employees"; // Select 'id' and 'name' (aliased as 'text' for Select2)
    
    // Add a WHERE clause if you want to filter based on the search term
    if (!empty($searchTerm)) {
         // Use prepared statements to prevent SQL injection
        $sql .= " WHERE name LIKE :searchTerm";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();

    // Fetch results in a format suitable for Select2
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    // If using mysqli, the fetching logic would be different:
    /*
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $row['text'] = $row['name']; // Ensure 'text' key exists
            $results[] = $row;
        }
        $result->free();
    }
    $conn->close();
    */

} catch (Exception $e) {
    // Handle exceptions or errors (e.g., log the error)
    // Return an empty array or an error structure if needed
    error_log("Error fetching data for Select2: " . $e->getMessage());
}

// Return the results as JSON
// Select2 typically expects results in a 'results' key
echo json_encode(['results' => $results]);

?>
