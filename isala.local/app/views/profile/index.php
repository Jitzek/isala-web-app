<?php
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="/public/css/profile.css">
</head>

<body>
    <div class="container">
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Profiel Pagina</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3 col-lg-3 " allign="center">
                            <img alt="User Pic" src="public/imgs/user_blue.png" class="img-circle img-usr" />
                        </div>
                        <div class=" col-md-9 col-lg-9 ">
                            <table class="table table-user-information">
                                <tbody>
                                    <?php foreach ($data['algemeen'] as $key => $value) : ?>
                                        <tr>
                                            <td><?= $key ?>:</td>
                                            <td><?= $value ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($data['medical_data']) : ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title mb-4">
                                <div class="d-flex justify-content-start">
                                    <h2 class="d-block">Medische Gegevens</h2>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                                        <?php foreach ($data['medical_data'] as $category => $content) : ?>
                                            <li class="nav-item category">
                                                <a class="nav-link" id="<?= $category ?>-tab" data-toggle="tab" href="#<?= $category ?>" role="tab" aria-selected="false"><?= $category ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="tab-content ml-1" id="myTabContent">
                                        <?php foreach ($data['medical_data'] as $category => $row) : ?>
                                            <div class="tab-pane fade" id="<?= $category ?>" role="tabpanel" aria-labelledby="<?= $category ?>-tab">
                                                <?php foreach ($row as $column) : ?>
                                                    <div class="row">
                                                        <div class="col-sm-3 col-md-2 col-5">
                                                            <label style="font-weight:bold;"><?= $column['Onderwerp'] ?></label>
                                                        </div>
                                                        <div class="col-md-8 col-6">
                                                            <?= $column['Afbeeld_Waarde'] ?>
                                                        </div>
                                                    </div>
                                                    <hr />
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
<footer>
</footer>

</html>