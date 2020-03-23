<?php
    //If user hasent seen cookie yet
    $accepted = $data['accepted_cookie'];
    if($accepted == 0){
    echo '<div class="cookie-container">
        <p>
            Wij maken op deze website gebruik van cookies. Een cookie is 
            een eenvoudig klein bestandje dat met pagina\'s van deze 
            website [en/of Flash-applicaties] wordt meegestuurd en door 
            uw browser op uw harde schrijf van uw computer wordt opgeslagen.
        </p>
        <p>
            U heeft het recht om te vragen om inzage in en correctie of 
            verwijdering van uw gegevens. Zie hiervoor onze contactpagina.
            Om misbruik te voorkomen kunnen wij u vragen om u adequaat te
            identificeren. Wanneer het gaat om inzage in persoonsgegevens
            gekoppeld aan een cookie, dient u een kopie van het cookie in
            kwestie mee te sturen. Cookies kunt u alleen zelf verwijderen,
            aangezien deze op uw computer opgeslagen zijn. Raadpleeg 
            hiervoor de handleiding van uw browser.    
        </p>
        <form method="post" action="/public/home/cookie">
            <button type="submit" name="cookie" value="cookie" class="cookie-knopke">Ik ga akkoord!</button>
        </form>
    </div>';
    }
?>