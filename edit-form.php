<?php
include 'config/database.php';

if (empty($_GET['slug'])) {
    die("Slug tidak valid.");
}


$slug = $_GET['slug'];

$form = $conn->query("SELECT * FROM forms WHERE slug = '$slug'")->fetch_assoc();
$questions = $conn->query("SELECT * FROM questions WHERE form_id = '$form[id]'")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Buat Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">

            <h2>Edit Form: <?= htmlspecialchars($form['title']) ?></h2>

            <div class="col-md-6">

                <form method="POST" action="update_form_builder.php" id="formBuilder">
                    <input type="hidden" name="form_id" value="<?= $form['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Form</label>
                        <input type="text" name="form_name" class="form-control" required placeholder="Contoh: Formulir Pendaftaran" value="<?= htmlspecialchars($form['title']) ?>">
                    </div>

                    <div id="questionList">
                        <?php foreach ($questions as $index => $q): ?>
                            <div class="question-item card p-3 mb-3 position-relative" data-index="<?= $index ?>">
                                <button type="button" class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2"
                                    onclick="removeQuestion(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $q['id'] ?>">
                                <label>Pertanyaan</label>
                                <input type="text" name="questions[<?= $index ?>][text]" class="form-control" value="<?= htmlspecialchars($q['text']) ?>" required>

                                <label>Tipe Jawaban</label>
                                <select name="questions[<?= $index ?>][type]" class="form-select" onchange="toggleOptions(this, <?= $index ?>)">
                                    <?php foreach (['text', 'textarea', 'radio', 'checkbox', 'select'] as $type): ?>
                                        <option value="<?= $type ?>" <?= $q['type'] == $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                                    <?php endforeach ?>
                                </select>

                                <div class="options" id="options-<?= $index ?>" style="<?= in_array($q['type'], ['radio', 'checkbox', 'select']) ? '' : 'display:none' ?>">
                                    <label>Opsi (pisahkan dengan koma)</label>
                                    <input type="text" name="questions[<?= $index ?>][options]" class="form-control" value="<?= htmlspecialchars($q['options']) ?>">
                                </div>

                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="questions[<?= $index ?>][is_required]" value="1" <?= $q['is_required'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Wajib Diisi</label>
                                </div>

                            </div>
                        <?php endforeach ?>
                    </div>

                    <button type="button" class="btn btn-success my-3" onclick="addQuestion()"><i class="fas fa-plus"></i> Tambah Pertanyaan</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Preview:</h3>
                <div id="formPreview" class="p-3 border bg-light"></div>
            </div>
            <script>
                let questionIndex = <?= count($questions) ?>;

                function toggleOptions(select, index) {
                    const val = select.value;
                    const show = ['radio', 'checkbox', 'select'].includes(val);
                    document.getElementById('options-' + index).style.display = show ? '' : 'none';
                    generatePreview();
                }

                function removeQuestion(btn) {
                    btn.closest('.question-item').remove();
                    generatePreview();
                }

                function addQuestion() {
                    const index = questionIndex++;
                    const html = `
    <div class="question-item card p-3 mb-3" data-index="${index}">
        <label>Pertanyaan</label>
        <input type="text" name="questions[${index}][text]" class="form-control" required oninput="generatePreview()">

        <label>Tipe Jawaban</label>
        <select name="questions[${index}][type]" class="form-select" onchange="toggleOptions(this, ${index})">
            <option value="text">Text</option>
            <option value="textarea">Textarea</option>
            <option value="radio">Radio</option>
            <option value="checkbox">Checkbox</option>
            <option value="select">Select</option>
        </select>

        <div class="options mt-2" id="options-${index}" style="display:none">
            <label>Opsi (pisahkan dengan koma)</label>
            <input type="text" name="questions[${index}][options]" class="form-control" oninput="generatePreview()">
        </div>

        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="questions[${index}][required]" value="1">
            <label class="form-check-label">Wajib Diisi</label>
        </div>

        <button type="button" class="btn btn-danger mt-2" onclick="removeQuestion(this)">Hapus Pertanyaan</button>
    </div>`;
                    document.getElementById('questionList').insertAdjacentHTML('beforeend', html);
                    generatePreview();
                }

                function generatePreview() {
                    const container = document.getElementById('formPreview');
                    container.innerHTML = '';
                    document.querySelectorAll('.question-item').forEach(item => {
                        const text = item.querySelector('input[name$="[text]"]').value;
                        const type = item.querySelector('select[name$="[type]"]').value;
                        const options = item.querySelector('input[name$="[options]"]')?.value || '';

                        let inputHtml = '';
                        if (type === 'text') {
                            inputHtml = `<input type="text" class="form-control">`;
                        } else if (type === 'textarea') {
                            inputHtml = `<textarea class="form-control"></textarea>`;
                        } else if (['radio', 'checkbox'].includes(type)) {
                            options.split(',').forEach(opt => {
                                const clean = opt.trim();
                                if (clean) inputHtml += `<div class="form-check"><input type="${type}" class="form-check-input"> <label class="form-check-label">${clean}</label></div>`;
                            });
                        } else if (type === 'select') {
                            inputHtml = `<select class="form-select">` +
                                options.split(',').map(opt => `<option>${opt.trim()}</option>`).join('') +
                                `</select>`;
                        }

                        container.innerHTML += `<div class="mb-3"><label>${text}</label>${inputHtml}</div>`;
                    });
                }

                document.querySelectorAll('input, select, textarea').forEach(e => {
                    e.addEventListener('input', generatePreview);
                });
                generatePreview();
            </script>
        </div>
    </div>
</body