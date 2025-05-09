<?php
include 'config/database.php'; // Menghubungkan ke database

// Ambil form_id dan data jawaban dari form
$form_id = $_POST['form_id']; // ID Formulir yang dikirimkan
$answers = $_POST['answers']; // Array jawaban yang dikirimkan dari form

// Proses penyimpanan data jawaban ke database
foreach ($answers as $question_id => $answer) {
    if (is_array($answer)) {
        // Jika jawaban adalah array (checkbox), gabungkan menjadi string
        $answer = implode(',', $answer);
    }

    // Simpan jawaban per pertanyaan ke database
    $stmt = $conn->prepare("INSERT INTO answers (form_id, question_id, answer) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $form_id, $question_id, $answer);
    $stmt->execute();
}

echo "Formulir telah berhasil dikirim!";
