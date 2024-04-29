<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!doctype html>
<html lang="en">
<body>
    <div class="wrapper">
    </div>
    <div class="page-wrapper">
        <?php if (isset($_SESSION['succ'])) { ?>
        <!-- Success alert -->
        <?php } ?>
        <?php if (isset($_SESSION['err'])) { ?>
        <!-- Error alert -->
        <?php } ?>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card mt-4">
                            <div class="card-body">
                                <div class="contact-header">
                                    <div class="d-sm-flex d-block align-items-center justify-content-between">
                                        <div class="h5 fw-semibold mb-0">All Documents</div>
                                        <div class="d-flex mt-sm-0 mt-2 align-items-center">
                                            <form id="searchForm">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="searchInput"
                                                        name="searchTerm" placeholder="Search documents...">
                                                    <button class="btn btn-outline-secondary" type="submit"
                                                        id="searchButton">Search</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="searchResults">
                        <?php
                        $id = $_GET['id'] ?? '111';
                        $dept_id = $_GET['dept_id'] ?? '';
                        if ($dept_id !== '') {
                            $query = "SELECT * FROM uploaded_documents WHERE document_process = ? AND document_status = ?";
                            $stmt = $dbBPR->prepare($query);

                            if ($stmt) {
                                $documentStatus = 'PUBLIC';
                                $stmt->bind_param("ss", $dept_id, $documentStatus);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Output each document as before
                        ?>
                        <a href="view_document.php?doc_id=<?php echo htmlspecialchars($row['document_id']); ?>">
                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 contact-item">
                                <div class="card custom-card">
                                    <div class="card-body contact-action"
                                        style="background-color: lightgreen; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 10px;">
                                        <div class="contact-overlay"></div>
                                        <div class="d-flex align-items-top">
                                            <div class="row">
                                                <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-3 mx-2">
                                                    <div
                                                        class="widgets-icons rounded-circle mx-auto bg-light-success text-success mb-3">
                                                        <i class='bx bxs-file'></i>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-8 col-xl-9 col-lg-9 col-md-9 col-sm-6">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <?php echo htmlspecialchars($row['document_name']); ?>
                                                    </h6>
                                                    <p class="fw-semibold fs-11 mb-0 text-primary">
                                                        <?php echo date('Y-m-d', strtotime($row['document_time'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="d-flex align-items-center justify-content-center gap-2 contact-hover-buttons">
                                            <a type="button"
                                                href="view_document.php?doc_id=<?php echo htmlspecialchars($row['document_id']); ?>"
                                                class="btn btn-sm btn-light contact-hover-btn">
                                                View Document
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php

                                    }
                                } else {
                                    // Display an image and a message if no documents are found
                                    echo '<div style="text-align: center;"><img src="resources/none.png" alt="No Documents Found" style="width:350px; height: 350px;"></div>';
                                    echo "<h3 style='text-align: center;'>No documents found for this Process.</h3>";
                                }
                                $stmt->close();
                            } else {
                                echo "<p>Error preparing statement.</p>";
                            }
                        } else {
                            echo "<p>No department selected.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="node_modules/pdfjs-dist/build/pdf.js"></script>
    <script>
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form submission
        var searchTerm = document.getElementById('searchInput').value;
        console.log('Search term:', searchTerm); // Log search term to console

        if (searchTerm.trim() !== '') {
            // Perform AJAX request to fetch search results
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'server.php?id=222&searchTerm=' + encodeURIComponent(searchTerm));
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log('Response:', response); // Log response to console
                    if (response.error) {
                        // Display error message if search term not found or other errors
                        document.getElementById('searchResults').innerHTML = '<p>' + response.error +
                            '</p>';
                    } else {
                        // Display search results
                        var resultsHTML = '<h2>Search Results</h2>';
                        if (response.length > 0) {
                            response.forEach(function(result) {
                                resultsHTML += '<div class="card" style="margin-bottom: 10px;">';
                                resultsHTML +=
                                    '<div class="card-body contact-action" style="background-color: lightgreen; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 10px; display: flex; align-items: center;">';
                                if (result.searchTermFound) {
                                    resultsHTML += '<div style="flex-grow: 1;">';
                                    resultsHTML += '<h5 class="card-title">' + result
                                        .document_name + '</h5>';
                                    resultsHTML += '<h5 class="card-title">' + result
                                        .document_process + '</h5>';
                                    resultsHTML += '<p class="card-text">' + result.excerpt +
                                    '</p>'; // Use the original excerpt without replacing <b> tags
                                    resultsHTML += '</div>'; // Close div with flex-grow: 1
                                } else {
                                    resultsHTML += '<p class="card-text">' + result.document_name +
                                        ' - Search term not found in this document</p>';
                                }
                                resultsHTML += '<a href="view_document.php?doc_id=' + result
                                    .document_id +
                                    '" class="btn btn-primary ml-auto">View Document</a>';
                                resultsHTML += '</div>'; // Close card-body
                                resultsHTML += '</div>'; // Close card
                            });
                        } else {
                            resultsHTML += '<p>No matching documents found.</p>';
                        }
                        document.getElementById('searchResults').innerHTML =
                        resultsHTML; // Set innerHTML to display the HTML content
                    }
                } else {
                    // Display error message if there was a problem with the request
                    document.getElementById('searchResults').innerHTML =
                        '<p>Error fetching search results.</p>';
                }
            };
            // Error handler for the AJAX request
            xhr.onerror = function() {
                console.error('An error occurred during the AJAX request.');
                document.getElementById('searchResults').innerHTML =
                    '<p>An error occurred. Please try again later.</p>';
            };
            xhr.send();
        } else {
            // No search term provided, display all documents
            document.getElementById('searchResults').innerHTML = ''; // Clear previous search results
            // You may fetch and display all documents here using another AJAX request or any other method
        }
    });
    </script>


</html>
