<?php
define('APP_INIT', true);

require_once '../../includes/config.php';
require_once '../auth.php';

$message = '';

if(isset($_POST['import'])){

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if(!empty($_FILES['file']['name'])){

        $file = $_FILES['file'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $allowed = ['txt', 'pdf', 'docx'];

        if(in_array($ext, $allowed)){

            if($ext == 'txt'){

    $content = file_get_contents($file['tmp_name']);

}

elseif($ext == 'pdf'){

    require '../../vendor/autoload.php';

    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($file['tmp_name']);

    $content = $pdf->getText();

}

elseif($ext == 'docx'){

    require '../../vendor/autoload.php';

    $phpWord = \PhpOffice\PhpWord\IOFactory::load($file['tmp_name']);

    $content = '';

    foreach($phpWord->getSections() as $section){

        $elements = $section->getElements();

        foreach($elements as $element){

            if(method_exists($element, 'getText')){

                $content .= $element->getText() . "\n";

            }

        }
    }
}

        }
    }

    if($title && $content){

        $stmt = $conn->prepare("
            INSERT INTO blogs(title, content)
            VALUES(?, ?)
        ");

        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();

        $message = "Blog imported successfully.";

    } else {

        $message = "Title and content required.";
    }
}

include '../layout/header.php';
?>

<section class="admin-page">

    <h1>Import Blog</h1>

    <?php if($message): ?>
        <div class="alert">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card">

        <label>Blog Title</label>

        <input type="text"
               name="title"
               required
               class="input">

        <br><br>

        <label>Paste Content</label>

        <textarea name="content"
                  rows="12"
                  class="input"></textarea>

        <br><br>

        <label>OR Upload TXT File</label>

        <input type="file"
               name="file"
               class="input">

        <br><br>

        <button type="submit"
                name="import"
                class="btn">
            Import Blog
        </button>

    </form>

</section>

<?php include '../layout/footer.php'; ?>