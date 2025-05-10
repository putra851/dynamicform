<?php

include 'config/database.php';

// Ambil nama form dari input
$form_name = $_POST['form_name'];
$form_name = $conn->real_escape_string($form_name);
$form_slug = strtolower(str_replace(' ', '-', $form_name)); // Mengubah nama form menjadi slug
$form_slug = preg_replace('/[^a-z0-9-]+/', '-', $form_slug); // Menghapus karakter yang tidak valid
$form_slug = trim($form_slug, '-'); // Menghapus karakter '-' di awal dan akhir

// Simpan form_name ke tabel forms
$sql = "INSERT INTO forms (title, slug) VALUES ('$form_name', '$form_slug')";
if ($conn->query($sql) === TRUE) {
    // Ambil ID form yang baru saja disimpan
    $form_id = $conn->insert_id;

    // Simpan setiap pertanyaan ke tabel questions
    if (!empty($_POST['questions'])) {
        foreach ($_POST['questions'] as $question) {
            $text = $conn->real_escape_string($question['text']);
            $type = $conn->real_escape_string($question['type']);
            $question_options = isset($question['options']) ? $conn->real_escape_string($question['options']) : '';
            $question_required = isset($question['required']) ? 1 : 0;

            $sql = "INSERT INTO questions (`form_id`, `text`, `type`, `options`, `is_required`)
                    VALUES ('$form_id', '$text', '$type', '$question_options', '$question_required')";

            $conn->query($sql);
        }
    }
    echo "Form berhasil disimpan!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
