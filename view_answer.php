<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'dynamicform';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (empty($_GET['slug'])) {
    die("Slug tidak valid.");
}


$slug = $_GET['slug'];

// Ambil semua pertanyaan
$questions = [];
$res = $conn->query("SELECT questions.id, text FROM questions JOIN forms ON forms.id = questions.form_id WHERE slug = '$slug' ORDER BY id");
while ($row = $res->fetch_assoc()) {
    $questions[$row['id']] = $row['text'];
}

// Ambil semua jawaban
$res = $conn->query("SELECT form_id, question_id, answer FROM answers JOIN forms ON forms.id = answers.form_id WHERE slug = '$slug' ORDER BY form_id, question_id, answers.id");

// Siapkan array rekap
$data = [];
while ($row = $res->fetch_assoc()) {
    $form_id = $row['form_id'];
    $question_text = $questions[$row['question_id']];
    $data[$form_id][$question_text] = $row['answer'];
}

// Tampilkan dalam HTML table
echo "<table border='1'><thead><tr><th>Form ID</th>";
foreach ($questions as $q) {
    echo "<th>{$q}</th>";
}
echo "</tr></thead><tbody>";

foreach ($data as $form_id => $answers) {
    echo "<tr><td>{$form_id}</td>";
    foreach ($questions as $qtext) {
        $val = isset($answers[$qtext]) ? htmlspecialchars($answers[$qtext]) : '';
        echo "<td>{$val}</td>";
    }
    echo "</tr>";
}

echo "</tbody></table>";
