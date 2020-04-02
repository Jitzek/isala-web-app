<?php
?>
<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="../../public/css/fileupload.css">
</head>

<body>
    <div id="center">
        <?php if ($_SESSION['role'] != "patienten") : ?>
            <form action="/public/upload" method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" id="fileToUpload">
                <label for="fileToUpload" id="butt">Choose a file</label>
                <label>title</label>
                <input type="text" name="title" id="title">
                <label>patiënt(should be removed with uid of patiënt trough post</label>
                <input type="text" name="patiënt" id="patiënt">
                <input type="submit" value="Submit" name="submit">
            </form>
        <?php endif; ?>
        <?php if ($data['table']->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Titel</th>
                    <th>Verzender</th>
                    <th>Datum</th>
                </tr>
                <?php while ($row = $data['table']->fetch_assoc()) : ?>
                    <tr>
                        <td>
                            <form action="/public/download" method="post">
                                <input type="hidden" name="ID" value="<?= htmlentities($row["ID"]) ?>">
                                <input type="submit" value="Submit" name="submit">
                            </form>
                            <?= htmlentities($row["Titel"]) ?>
                        </td>
                        <td>
                            <?= htmlentities($row["Eigenaar"]) ?>
                        </td>
                        <td>
                            <?= htmlentities($row["Datum"]) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
<footer>
</footer>

</html>