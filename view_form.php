<?php
include 'config/database.php'; // Menghubungkan ke database


if (empty($_GET['slug'])) {
    die("Slug tidak valid.");
}


$slug = $_GET['slug'];

$sql_form = "SELECT * FROM forms WHERE slug = '$slug'";
$result_form = $conn->query($sql_form);
$form = $result_form->fetch_assoc();

// Ambil pertanyaan terkait form
$sql_questions = "SELECT * FROM questions WHERE form_id = '$form[id]'";
$result_questions = $conn->query($sql_questions);
$questions = $result_questions->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Formulir: <?php echo $form['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1><?php echo $form['title']; ?></h1>
        <form method="POST" action="save_answer.php" enctype="multipart/form-data">
            <input type="hidden" name="form_id" value="<?php echo $form['id']; ?>">
            <?php foreach ($questions as $question): ?>
            <div class="mb-3">
                <label class="form-label"><?php echo $question['text']; ?> <?php echo $question['is_required'] ? '*' : ''; ?></label>
                <?php
                switch ($question['type']) {
                case 'text':
                    echo '<input type="text" class="form-control" name="answers[' . $question['id'] . ']"' . ($question['is_required'] ? ' required' : '') . '>';
                    break;
                case 'textarea':
                    echo '<textarea class="form-control" name="answers[' . $question['id'] . ']"' . ($question['is_required'] ? ' required' : '') . '></textarea>';
                    break;
                case 'radio':
                    $options = explode(',', $question['options']);
                    foreach ($options as $index => $option) {
                    echo '<div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[' . $question['id'] . ']" value="' . $option . '" id="radio-' . $question['id'] . '-' . $index . '"' . ($question['is_required'] ? ' required' : '') . '>
                        <label class="form-check-label" for="radio-' . $question['id'] . '-' . $index . '">' . $option . '</label>
                        </div>';
                    }
                    break;
                case 'checkbox':
                    $options = explode(',', $question['options']);
                    foreach ($options as $index => $option) {
                    echo '<div class="form-check">
                        <input class="form-check-input" type="checkbox" name="answers[' . $question['id'] . '][]" value="' . $option . '" id="checkbox-' . $question['id'] . '-' . $index . '"' . ($question['is_required'] ? ' required' : '') . '>
                        <label class="form-check-label" for="checkbox-' . $question['id'] . '-' . $index . '">' . $option . '</label>
                        </div>';
                    }
                    break;
                case 'select':
                    $options = explode(',', $question['options']);
                    echo '<select class="form-select" name="answers[' . $question['id'] . ']"' . ($question['is_required'] ? ' required' : '') . '>';
                    foreach ($options as $option) {
                    echo '<option value="' . $option . '">' . $option . '</option>';
                    }
                    echo '</select>';
                    break;
                case 'file':
                    echo '<input type="file" class="form-control" name="answers[' . $question['id'] . ']"' . ($question['is_required'] ? ' required' : '') . '>';
                    break;
                }
                ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Kirim</button>
        </form>
    </div>
</body>

</html>