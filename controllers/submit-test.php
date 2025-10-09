<?php
session_start();

// In a real application, you would validate and save to database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testId = $_POST['test_id'] ?? '';
    $testName = $_POST['test_name'] ?? '';
    $responses = [];
    
    // Collect all responses
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
            $questionNumber = str_replace('question_', '', $key);
            $responses[$questionNumber] = (int)$value;
        }
    }
    
    // Calculate basic score (sum of all responses)
    $totalScore = array_sum($responses);
    $maxScore = count($responses) * 4; // Max value per question is 4
    $percentage = ($totalScore / $maxScore) * 100;
    
    // Store results in session (in real app, save to database)
    $_SESSION['test_results'] = [
        'test_id' => $testId,
        'test_name' => $testName,
        'score' => $totalScore,
        'percentage' => $percentage,
        'responses' => $responses,
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to results page
    header('Location: ../index.php?role=estudiante&page=resultados&test_id=' . urlencode($testId));
    exit;
} else {
    // Invalid request method
    header('Location: ../index.php?role=estudiante&page=tests');
    exit;
}
?>
