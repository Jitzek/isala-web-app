<?php
?>

<!DOCTYPE html>
<html>
<head>
    <title>
        <?= htmlentities($data['title']) ?>
    </title>
    <link rel="stylesheet" href="public/css/linkuser.css">
</head>
<body>
<div id="knopkes">
    <form method='post'>
        <div id="userlist">
            <h4>SELECTEER PATIËNTEN</h4>
            <?php
            echo "<select name='patient[]' multiple size = 9>";
            $array = $data['showpatienten'];
            if ($array == NULL) {
                echo "Deze dokter heeft geen patienten";
            } else {
                $value = array_map(function ($e) {
                    return htmlentities($e, ENT_NOQUOTES, 'UTF-8');
                }, $array);

                if (isset($_POST["submitUser"]) && $_POST['patient'] != NULL) {
                    foreach ($value as $arrayItem) {
                        foreach ($_POST['patient'] as $patient) {
                            if ($patient == $arrayItem)
                                echo "<option value='$arrayItem' selected='selected'>$arrayItem</option>";
                            else
                                echo "<option value='$arrayItem'>$arrayItem</option>";
                        }
                    }
                } else {
                    foreach ($value as $arrayItem) {
                        echo "<option value='$arrayItem'>$arrayItem</option>";
                    }
                }
            }
            echo "</select>";
            ?>
            <button type="submitUser" name="submitUser" value=SubmitUser>
                Selecteer gebruiker
            </button>
            <h4>Huidige gecontracteerden voor gebruiker</h4>
            <table>
                <tr>
                    <th>Diëtist</th>
                    <th>Fysiotherapeut</th>
                    <th>Psycholoog</th>
                </tr>
                <tr>
                    <?php
                    if ($data['dietist'] === NULL) {
                        echo "<td>Geen</td>";
                    } else {
                        $dietist = $data['dietist'];
                        echo "<td>";
                        echo $dietist;
                        echo "</td>";
                    }
                    if ($data['fysio'] === NULL) {
                        echo "<td>Geen</td>";
                    } else {
                        $fisio = $data['fisio'];
                        echo "<td>$fisio</td>";
                    }
                    if ($data['psych'] === NULL) {
                        echo "<td>Geen</td>";
                    } else {
                        $psych = $data['psych'];
                        echo "<td>$psych</td>";
                    }
                    ?>
                </tr>
            </table>

            <h4>SELECTEER GECONTRACTEERDEN</h4>
            <?php
            echo "<select name='contracted[]' multiple size = 9>";
            $array = $data['showcontracted'];
            if ($array == NULL) {
                echo "Er zijn geen gecontracteerden";
            } else {
                $value = array_map(function ($e) {
                    return htmlentities($e, ENT_NOQUOTES, 'UTF-8');
                }, $array);
                foreach ($value as $arrayItem) {
                    echo "<option value='$arrayItem'>$arrayItem</option>";
                }
            }
            echo "</select>";
            ?>
            <button type="submit" name="submit" value=Submit>
                Link
            </button>
        </div>
    </form>
</div>
</body>
<footer>
</footer>
</html>