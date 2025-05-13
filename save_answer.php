<?php
include 'config/database.php'; // Menghubungkan ke database

// Ambil form_id dan data jawaban dari form
$form_id = $_POST['form_id']; // ID Formulir yang dikirimkan
$answers = $_POST['answers']; // Array jawaban yang dikirimkan dari form

// Proses penyimpanan data jawaban ke database
foreach ($answers as $question_id => $answer) {
    // Cek apakah pertanyaan ini adalah upload file
    if (
        isset($_FILES['answers']['name'][$question_id]) &&
        $_FILES['answers']['name'][$question_id] != '' &&
        isset($_FILES['answers']['error'][$question_id]) &&
        $_FILES['answers']['error'][$question_id] == UPLOAD_ERR_OK
    ) {
        // Pastikan folder upload_berkas ada
        $uploadDir = __DIR__ . '/upload_berkas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['answers']['name'][$question_id]);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid('file_' . $question_id . '_') . '.' . $ext;
        $targetFile = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['answers']['tmp_name'][$question_id], $targetFile)) {
            // Simpan path file sebagai jawaban
            $answer = 'upload_berkas/' . $uniqueName;
        } else {
            $answer = '';
        }
    } elseif (is_array($answer)) {
        // Jika jawaban adalah array (checkbox), gabungkan menjadi string
        $answer = implode(',', $answer);
    }

    // Simpan jawaban per pertanyaan ke database
    $stmt = $conn->prepare("INSERT INTO answers (form_id, question_id, answer) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $form_id, $question_id, $answer);
    $stmt->execute();
}

echo "Formulir telah berhasil dikirim!";
