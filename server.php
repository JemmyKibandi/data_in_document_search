<?php session_start();
require '../../vendor/autoload.php';

use Smalot\PdfParser\Parser;
$id = $_GET['id'] ?? '222';

// Prepare the query to fetch all documents
$query = "SELECT document_id, document_name, document_file, document_time, document_process FROM uploaded_documents WHERE document_status = 'PUBLIC'";

// Prepare and execute the query
$stmt = $dbBPR->prepare($query);
if ($stmt) {
    $stmt->execute();

    $result = $stmt->get_result();

    // Initialize an array to store search results
    $searchResults = [];

    // Loop through each document
    while ($row = $result->fetch_assoc()) {
        // Fetch document content
        $content = extractTextFromPDF($row['document_file']); // Extract text from PDF

        // Check if the search term exists in the document content
        $searchTermFound = stripos($content, $_GET['searchTerm'] ?? '') !== false;

        // If search term is found, add document details to search results
        if ($searchTermFound) {
            // Generate excerpt containing the search term
            $excerpt = generateExcerpt($content, $_GET['searchTerm'] ?? '');

            // Add document content to the search results
            $searchResults[] = [
                'document_id' => $row['document_id'],
                'document_process' => $row['document_process'],
                'document_name' => htmlspecialchars($row['document_name']),
                'document_time' => date('Y-m-d', strtotime($row['document_time'])),
                'excerpt' => htmlspecialchars($excerpt), // Return the excerpt containing the search term
                'searchTermFound' => $searchTermFound, // Indicate whether search term is found in document
            ];
        }
    }

    // Output search results as JSON
    if (!empty($searchResults)) {
        echo json_encode($searchResults);
    } else {
        echo json_encode(['error' => 'No matching documents found.']);
    }

    // Close the statement
    $stmt->close();
} else {
    // Error preparing statement
    echo json_encode(['error' => 'Error preparing statement.']);
}
function extractTextFromPDF($pdfUrl)
{
    // Create an instance of the PDF parser
    $parser = new Parser();

    try {
        // Parse the PDF and extract text content
        $pdf = $parser->parseFile($pdfUrl);
        $text = $pdf->getText();

        // Return the extracted text content
        return $text;
    } catch (\Exception $e) {
        // Handle any exceptions (e.g., file not found, parsing errors)
        return 'Error: ' . $e->getMessage();
    }
}
function generateExcerpt($text, $searchTerm, $excerptLength = 100)
{
    // Find position of the search term in the text
    $pos = stripos($text, $searchTerm);

    // If search term not found, return empty excerpt
    if ($pos === false) {
        return '';
    }

    // Calculate start and end positions of the excerpt
    $startPos = max(0, $pos - ($excerptLength / 2));
    $endPos = min(strlen($text), $pos + ($excerptLength / 2));

    // Extract excerpt from the text
    $excerpt = substr($text, $startPos, $endPos - $startPos);

    // Add ellipsis if excerpt does not start at the beginning of the text
    if ($startPos > 0) {
        $excerpt = '...' . $excerpt;
    }

    // Add ellipsis if excerpt does not end at the end of the text
    if ($endPos < strlen($text)) {
        $excerpt .= '...';
    }
    // Highlight search term in the excerpt
    $excerpt = preg_replace('/' . preg_quote($searchTerm, '/') . '/i', '$0', $excerpt);

    // Return generated excerpt
    return $excerpt;
}
