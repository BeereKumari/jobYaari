<?php
$pageTitle = 'Edit Blog';
$breadcrumb = '<a href="/admin/index.php">Home</a> &bull; <a href="/admin/blogs/index.php">Blog</a> &bull; Edit';
require_once dirname(__DIR__, 2) . '/includes/admin-header.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /admin/blogs/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    setFlash('error', 'Blog not found.');
    header('Location: /admin/blogs/index.php');
    exit;
}

$errors = [];
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $categoryId = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $shortDesc = sanitize($_POST['short_description'] ?? '');
        $content = $_POST['content'] ?? '';
        $metaKeywords = sanitize($_POST['meta_keywords'] ?? '');
        $status = in_array($_POST['status'], ['published', 'draft']) ? $_POST['status'] : 'published';

        if (empty($title)) $errors[] = 'Title is required.';
        if (strlen($title) > 255) $errors[] = 'Title must be under 255 characters.';
        if (empty($shortDesc)) $errors[] = 'Short description is required.';
        if (empty($content)) $errors[] = 'Content is required.';
        if (!$categoryId) $errors[] = 'Please select a category.';

        $slugCheck = $pdo->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ?");
        $slugCheck->execute([$slug, $id]);
        if ($slugCheck->fetch()) $errors[] = 'This slug already exists.';

        $imageFilename = $blog['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($blog['image'] && file_exists(__DIR__ . '/../../assets/uploads/blogs/' . $blog['image'])) {
                unlink(__DIR__ . '/../../assets/uploads/blogs/' . $blog['image']);
            }
            $uploadResult = uploadImage($_FILES['image'], __DIR__ . '/../../assets/uploads/blogs/');
            if (isset($uploadResult['error'])) {
                $errors[] = $uploadResult['error'];
                $imageFilename = $blog['image'];
            } else {
                $imageFilename = $uploadResult['filename'];
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE blogs SET title = ?, slug = ?, short_description = ?, content = ?, image = ?, category_id = ?, meta_keywords = ?, status = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $shortDesc, $content, $imageFilename, $categoryId, $metaKeywords, $status, $id]);
            setFlash('success', 'Blog updated successfully!');
            header('Location: /admin/blogs/index.php');
            exit;
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <ul style="margin:0; padding-left: 20px;">
        <?php foreach ($errors as $err): ?><li><?= escape($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Edit Blog</h2></div>
    <form method="POST" action="/admin/blogs/edit.php?id=<?= $id ?>" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

        <div class="form-group">
            <label for="image">Blog Image</label>
            <?php if ($blog['image']): ?>
            <div class="current-image">
                <img src="/assets/uploads/blogs/<?= escape($blog['image']) ?>" alt="Current image" style="max-width: 300px; border-radius: 8px;">
                <p>Current image. Upload a new one to replace it.</p>
            </div>
            <?php endif; ?>
            <div class="upload-area">
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <div class="upload-placeholder">
                    <span class="upload-icon">&#9729;</span>
                    <p>Choose a file</p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required maxlength="255" value="<?= escape($blog['title']) ?>">
        </div>

        <div class="form-group">
            <label for="slug">Slug *</label>
            <input type="text" id="slug" name="slug" required maxlength="280" value="<?= escape($blog['slug']) ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $blog['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="short_description">Short Description *</label>
            <textarea id="short_description" name="short_description" required maxlength="300" rows="3"><?= escape($blog['short_description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Content *</label>
            <div class="rich-editor-wrapper" id="content-editor-wrapper">
                <div class="rich-editor-toolbar">
                    <div class="toolbar-group">
                        <button type="button" data-command="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                        <button type="button" data-command="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                        <button type="button" data-command="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                        <button type="button" data-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="superscript" title="Superscript">X<sup style="font-size:0.6em;">2</sup></button>
                        <button type="button" data-command="subscript" title="Subscript">X<sub style="font-size:0.6em;">2</sub></button>
                    </div>
                    <div class="toolbar-group">
                        <select data-command="formatBlock" title="Heading">
                            <option value="p">Normal</option>
                            <option value="h1">H1 - Main Heading</option>
                            <option value="h2">H2 - Sub Heading</option>
                            <option value="h3">H3 - Section</option>
                            <option value="h4">H4 - Sub Section</option>
                        </select>
                    </div>
                    <div class="toolbar-group">
                        <select data-command="fontSize" title="Font Size">
                            <option value="1">Small</option>
                            <option value="3" selected>Normal</option>
                            <option value="5">Large</option>
                            <option value="7">Very Large</option>
                        </select>
                    </div>
                    <div class="toolbar-group">
                        <div class="color-picker-wrapper">
                            <button type="button" class="color-picker-btn" data-command="foreColor" title="Text Color">
                                A<span class="color-indicator" style="background:#000"></span>
                                <input type="color" value="#000000">
                            </button>
                        </div>
                        <div class="color-picker-wrapper">
                            <button type="button" class="color-picker-btn" data-command="hiliteColor" title="Highlight">
                                <span style="background:#ffff00;padding:0 3px;font-size:0.7em;">AB</span>
                                <input type="color" value="#ffff00">
                            </button>
                        </div>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="justifyLeft" title="Align Left">&#9776;</button>
                        <button type="button" data-command="justifyCenter" title="Align Center">&#9776;</button>
                        <button type="button" data-command="justifyRight" title="Align Right">&#9776;</button>
                        <button type="button" data-command="justifyFull" title="Justify">&#9776;</button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="insertOrderedList" title="Numbered List">&#9312;</button>
                        <button type="button" data-command="insertUnorderedList" title="Bullet List">&#8226;</button>
                        <button type="button" data-command="indent" title="Increase Indent">&#8681;</button>
                        <button type="button" data-command="outdent" title="Decrease Indent">&#8679;</button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="createLink" title="Insert Link (Ctrl+K)">&#128279;</button>
                        <button type="button" data-command="unlink" title="Remove Link">&#128279;</button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="insertTable" title="Insert Custom Table">&#9638;</button>
                        <button type="button" data-command="insertTemplateTable" title="Exam Table Templates">&#128203;</button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="formatBlock" data-value="blockquote" title="Blockquote">&#10077;</button>
                        <button type="button" data-command="insertHorizontalRule" title="Divider">&#8213;</button>
                    </div>
                    <div class="toolbar-group">
                        <button type="button" data-command="removeFormat" title="Clear Formatting">&#10060;</button>
                        <button type="button" data-command="undo" title="Undo (Ctrl+Z)">&#8630;</button>
                        <button type="button" data-command="redo" title="Redo (Ctrl+Y)">&#8631;</button>
                    </div>
                </div>
                <div class="rich-editor-content" contenteditable="true" data-placeholder="Start writing your blog content here. Use the toolbar above to format text, insert tables, and more..."><?= $blog['content'] ?></div>
                <textarea id="content" name="content" style="display:none;"><?= escape($blog['content']) ?></textarea>
            </div>
        </div>
        <script>initEditor('content-editor');</script>

        <div class="form-group">
            <label for="meta_keywords">Meta Keywords</label>
            <input type="text" id="meta_keywords" name="meta_keywords" placeholder="business, education, technology" value="<?= escape($blog['meta_keywords'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="status" value="published" <?= $blog['status'] === 'published' ? 'checked' : '' ?>> Published
                </label>
                <label class="radio-label">
                    <input type="radio" name="status" value="draft" <?= $blog['status'] === 'draft' ? 'checked' : '' ?>> Draft
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="/admin/blogs/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/admin-footer.php'; ?>
